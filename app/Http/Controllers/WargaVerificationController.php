<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warga;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class WargaVerificationController extends Controller
{
    public function verify(Warga $warga)
    {
        DB::beginTransaction();

        try {
            $user = User::where('warga_nik', $warga->warga_nik)->first();

            if ($user) {
                $user->role_id = 1;
                $user->save();

                $notification = Notification::make()
                    ->title('Warga berhasil diverifikasi')
                    ->success();

                $notification->send();
            } else {
                $notification = Notification::make()
                    ->title('User tidak ditemukan')
                    ->danger();

                $notification->send();

                DB::rollBack();

                return redirect()->back();
            }

            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();

            $notification = Notification::make()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
                ->danger();

            $notification->send();

            return redirect()->back();
        }
    }

    public function reject(Warga $warga)
    {
        DB::beginTransaction();

        try {
            $user = User::where('warga_nik', $warga->warga_nik)->first();

            if ($user) {
                $notification = Notification::make()
                    ->title('Verifikasi warga ditolak')
                    ->success();

                $notification->send();
            } else {
                $notification = Notification::make()
                    ->title('User tidak ditemukan')
                    ->danger();

                $notification->send();

                DB::rollBack();

                return redirect()->back();
            }

            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();

            $notification = Notification::make()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
                ->danger();

            $notification->send();

            return redirect()->back();
        }
    }
}
