<?php

namespace App\Http\Controllers;

use App\Models\FonnteDevice;
use App\Models\User;
use App\Services\FonnteService;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class WargaPasswordResetController extends Controller
{
    public function showEmailForm()
    {
        return view('warga.auth.reset-password-email');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $broker = Password::broker(Filament::getAuthPasswordBroker());

        $user = $broker->getUser(['email' => $data['email']]);

        if (! $user instanceof CanResetPassword) {
            return back()
                ->withErrors(['email' => 'Pengguna dengan email tersebut tidak ditemukan.'])
                ->withInput();
        }

        if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getPanel('warga')))) {
            return back()
                ->withErrors(['email' => 'Pengguna tidak dapat mengakses panel warga.'])
                ->withInput();
        }

        if (! method_exists($user, 'warga') || ! $user->warga || ! $user->warga->no_hp) {
            return back()
                ->withErrors(['email' => 'Nomor HP warga belum diisi. Silakan hubungi admin.'])
                ->withInput();
        }

        $deviceToken = $this->resolveDeviceToken();

        if (! $deviceToken) {
            return back()
                ->withErrors(['email' => 'Perangkat WhatsApp tidak tersedia untuk mengirim OTP.'])
                ->withInput();
        }

        $phone = $this->normalizePhone((string) $user->warga->no_hp);

        if ($phone === '') {
            return back()
                ->withErrors(['email' => 'Format nomor HP tidak valid.'])
                ->withInput();
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ],
        );

        $message = "Kode OTP reset password SIMAWAR Anda: {$otp}\n\n".
            "Jangan berikan kode ini kepada siapa pun.\n".
            'Kode berlaku selama 15 menit.';

        $response = app(FonnteService::class)->sendWhatsAppMessage(
            $phone,
            $message,
            $deviceToken,
        );

        if (! $response['status'] || (isset($response['data']['status']) && ! $response['data']['status'])) {
            $error = $response['data']['reason'] ?? $response['error'] ?? 'Terjadi kesalahan saat mengirim OTP.';

            return back()
                ->withErrors(['email' => $error])
                ->withInput();
        }

        session()->put('warga_password_reset_email', $user->email);
        session()->forget('warga_password_reset_verified');

        return redirect()->route('warga.password.verify-otp')
            ->with('status', 'OTP berhasil dikirim. Silakan cek WhatsApp Anda.');
    }

    public function showOtpForm()
    {
        if (! session()->has('warga_password_reset_email')) {
            return redirect()->route('warga.password.request')
                ->withErrors(['email' => 'Sesi reset password tidak ditemukan. Silakan mulai ulang.']);
        }

        return view('warga.auth.reset-password-otp');
    }

    public function verifyOtp(Request $request)
    {
        if (! session()->has('warga_password_reset_email')) {
            return redirect()->route('warga.password.request')
                ->withErrors(['email' => 'Sesi reset password tidak ditemukan. Silakan mulai ulang.']);
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:4'],
        ]);

        $email = session('warga_password_reset_email');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($data['otp'], $record->token)) {
            return back()
                ->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah.'])
                ->withInput();
        }

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        if ($record->created_at && now()->diffInMinutes($record->created_at) > $expireMinutes) {
            return back()
                ->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan minta OTP baru.']);
        }

        session()->put('warga_password_reset_verified', true);

        return redirect()->route('warga.password.new');
    }

    public function showNewPasswordForm()
    {
        if (! session()->has('warga_password_reset_email') || ! session('warga_password_reset_verified')) {
            return redirect()->route('warga.password.request')
                ->withErrors(['email' => 'Sesi reset password tidak valid. Silakan mulai ulang.']);
        }

        return view('warga.auth.reset-password-new');
    }

    public function updatePassword(Request $request)
    {
        if (! session()->has('warga_password_reset_email') || ! session('warga_password_reset_verified')) {
            return redirect()->route('warga.password.request')
                ->withErrors(['email' => 'Sesi reset password tidak valid. Silakan mulai ulang.']);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $email = session('warga_password_reset_email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('warga.password.request')
                ->withErrors(['email' => 'Pengguna tidak ditemukan. Silakan mulai ulang.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        session()->forget('warga_password_reset_email');
        session()->forget('warga_password_reset_verified');

        return redirect()->to('/warga/login')
            ->with('status', 'Password berhasil diubah. Silakan login dengan password baru.');
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === null) {
            return '';
        }

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    private function resolveDeviceToken(): ?string
    {
        $active = FonnteDevice::query()
            ->where('status', 'connected')
            ->orderByDesc('last_synced_at')
            ->value('token');

        if ($active) {
            return $active;
        }

        $fallback = env('DEVICE_TOKEN');

        return $fallback ?: null;
    }
}
