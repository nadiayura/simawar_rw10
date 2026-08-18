<?php

namespace App\Http\Controllers;

use App\Models\PembayaranMidtrans;
use App\Models\PembayaranTunai;
use App\Models\PeriodeIuran;
use App\Models\Status;
use App\Models\TagihanIuranWarga;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function bayarTagihan(TagihanIuranWarga $tagihan): RedirectResponse
    {
        $currentPeriode = \Illuminate\Support\Facades\DB::table('periode_iurans')
            ->where('periode_iuran_id', $tagihan->periode_iuran_id)
            ->first();
        if ($currentPeriode) {
            $unpaidIds = array_values(array_filter([
                Status::idForFitur('keuangan', 'Belum bayar'),
            ]));
            $earliestUnpaid = TagihanIuranWarga::query()
                ->where('warga_nik', $tagihan->warga_nik)
                ->where('iuran_id', $tagihan->iuran_id)
                ->whereIn('status_id', $unpaidIds)
                ->join('periode_iurans', 'periode_iurans.periode_iuran_id', '=', 'tagihan_iuran_wargas.periode_iuran_id')
                ->where('periode_iurans.tahun', (int) $currentPeriode->tahun)
                ->orderBy('periode_iurans.bulan')
                ->select('tagihan_iuran_wargas.periode_iuran_id')
                ->first();

            if ($earliestUnpaid && (string) $tagihan->periode_iuran_id !== (string) $earliestUnpaid->periode_iuran_id) {
                abort(403, 'Silahkan lakukan pembayaran pada bulan sebelumnya');
            }
        }

        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $warga = Warga::where('warga_nik', $tagihan->warga_nik)->first();

        $amount = (int) round((float) $tagihan->nominal_tagihan);
        $baseOrderId = (string) $tagihan->getKey();
        $orderId = $baseOrderId;
        $attempts = 0;
        do {
            $attempts++;
            $candidate = $baseOrderId.'-'.random_int(100, 999);
            $exists = PembayaranMidtrans::query()->where('order_id', $candidate)->exists();
            if (! $exists) {
                $orderId = $candidate;
                break;
            }
        } while ($attempts < 25);

        $items = [[
            'id' => 'IURAN-'.$tagihan->iuran_id,
            'price' => $amount,
            'quantity' => 1,
            'name' => 'Iuran Warga',
        ]];

        $email = $warga?->email;
        $isValidEmail = is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
        $phoneRaw = $warga?->no_hp;
        $phone = is_string($phoneRaw) ? preg_replace('/\D+/', '', $phoneRaw) : null;
        $customer = [
            'first_name' => $warga?->nama ?: 'Warga',
            'phone' => $phone ?: null,
            'billing_address' => [
                'address' => $warga?->alamat,
            ],
        ];
        if ($isValidEmail) {
            $customer['email'] = $email;
        }
        $customer = array_filter($customer, fn ($v) => ! is_null($v));

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'item_details' => $items,
            'customer_details' => $customer,
            'callbacks' => [
                'finish' => route('payments.midtrans.update', [
                    'order_id' => $orderId,
                    'panel' => request()->query('panel'),
                ]),
            ],
        ];

        $transaction = \Midtrans\Snap::createTransaction($params);

        $redirectUrl = is_array($transaction)
            ? ($transaction['redirect_url'] ?? null)
            : ($transaction->redirect_url ?? null);

        $token = is_array($transaction)
            ? ($transaction['token'] ?? null)
            : ($transaction->token ?? null);

        $existing = PembayaranMidtrans::query()->where('order_id', $orderId)->latest('updated_at')->first();
        if ($existing) {
            $existing->update([
                'jumlah' => (float) $tagihan->nominal_tagihan,
                'status_id' => Status::idForFitur('keuangan', 'Menunggu pembayaran'),
                'tipe_pembayaran' => null,
                'snap_token' => $token,
                'redirect_url' => $redirectUrl,
                'transaksi_id' => null,
            ]);
            $pmId = $existing->PembayaranMidtrans_id;
        } else {
            $created = PembayaranMidtrans::create([
                'jumlah' => (float) $tagihan->nominal_tagihan,
                'status_id' => Status::idForFitur('keuangan', 'Menunggu pembayaran'),
                'tipe_pembayaran' => null,
                'snap_token' => $token,
                'redirect_url' => $redirectUrl,
                'order_id' => $orderId,
                'transaksi_id' => null,
            ]);
            $pmId = $created->PembayaranMidtrans_id;
        }

        $tagihan->update([
            'status_id' => Status::idForFitur('keuangan', 'Menunggu pembayaran'),
            'PembayaranMidtrans_id' => $pmId ?? $tagihan->PembayaranMidtrans_id,
        ]);

        return redirect()->away($redirectUrl ?? url('/'));
    }

    public function notification()
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $notif = new \Midtrans\Notification;

        $orderId = $notif->order_id ?? null;
        $transactionStatus = $notif->transaction_status ?? null;
        $paymentType = $notif->payment_type ?? null;
        $channel = $this->resolveChannel($paymentType, $notif);
        $transactionId = $notif->transaction_id ?? null;
        $jumlahBayar = isset($notif->gross_amount) ? (float) $notif->gross_amount : null;
        $fraudStatus = $notif->fraud_status ?? null;

        $tagihanId = null;
        $trx = PembayaranMidtrans::query()
            ->where('order_id', $orderId)
            ->latest('updated_at')
            ->first();
        if (! $trx && is_string($orderId) && str_starts_with($orderId, 'TAGIHAN-')) {
            $raw = substr($orderId, 8);
            $lastDash = strrpos($raw, '-');
            if ($lastDash !== false) {
                $tagihanId = substr($raw, 0, $lastDash);
            }
        }
        if (is_string($orderId) && str_starts_with($orderId, 'TGH-IURAN-')) {
            $tagihanId = $orderId;
        }
        if (is_string($orderId) && str_starts_with($orderId, 'TG-')) {
            $tagihanId = $orderId;
            if (substr_count($orderId, '-') > 2) {
                $parts = explode('-', $orderId);
                if (count($parts) >= 3) {
                    $tagihanId = implode('-', array_slice($parts, 0, 3));
                }
            }
        }

        if ($trx) {
            $trx->update([
                'status_id' => Status::idForFitur('keuangan', $transactionStatus),
                'tipe_pembayaran' => $channel,
                'transaksi_id' => $transactionId,
                'jumlah' => $jumlahBayar ?? $trx->jumlah,
            ]);
            $pmId = $trx->PembayaranMidtrans_id;
        } else {
            $created = PembayaranMidtrans::create([
                'jumlah' => $jumlahBayar ?? 0,
                'status_id' => Status::idForFitur('keuangan', $transactionStatus),
                'tipe_pembayaran' => $channel,
                'snap_token' => null,
                'redirect_url' => null,
                'order_id' => $orderId,
                'transaksi_id' => $transactionId,
            ]);
            $pmId = $created->PembayaranMidtrans_id;
        }

        if ($tagihanId) {
            $tagihan = TagihanIuranWarga::query()
                ->where('tagihan_iuran_id', $tagihanId)
                ->first();

            if ($tagihan) {
                if ($pmId ?? null) {
                    $tagihan->PembayaranMidtrans_id = $pmId;
                }

                if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                    $tagihan->status_id = Status::idForFitur('keuangan', 'Lunas');

                    if (! $tagihan->tanggal_lunas) {
                        $tagihan->tanggal_lunas = now();
                    }
                }

                $tagihan->save();
            }
        }

        return response()->json(['received' => true]);
    }

    public function updateStatus(Request $request)
    {
        $orderId = $request->query('order_id');
        $transactionStatus = $request->query('transaction_status');
        $paymentType = $request->query('payment_type');
        $transactionId = $request->query('transaction_id');
        $jumlahBayar = $request->query('gross_amount');
        $fraudStatus = $request->query('fraud_status');
        $details = null;
        $channel = null;
        if ($orderId && (! $paymentType || ! $transactionId)) {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;
            try {
                $details = \Midtrans\Transaction::status($orderId);
                $paymentType = $paymentType ?: ($details->payment_type ?? $paymentType);
                $transactionStatus = $transactionStatus ?: ($details->transaction_status ?? $transactionStatus);
                $fraudStatus = $fraudStatus ?: ($details->fraud_status ?? $fraudStatus);
                $transactionId = $transactionId ?: ($details->transaction_id ?? $transactionId);
                if (! $jumlahBayar && isset($details->gross_amount)) {
                    $jumlahBayar = (float) $details->gross_amount;
                }
            } catch (\Throwable $e) {
                // ignore fetch error, continue with provided params
            }
        }
        $channel = $this->resolveChannel($paymentType, $details, $request);

        $tagihanId = null;
        $trx = PembayaranMidtrans::query()
            ->where('order_id', $orderId)
            ->latest('updated_at')
            ->first();
        if (! $trx && is_string($orderId) && str_starts_with($orderId, 'TAGIHAN-')) {
            $raw = substr($orderId, 8);
            $lastDash = strrpos($raw, '-');
            if ($lastDash !== false) {
                $tagihanId = substr($raw, 0, $lastDash);
            }
        }
        if (is_string($orderId) && str_starts_with($orderId, 'TGH-IURAN-')) {
            $tagihanId = $orderId;
        }
        if (is_string($orderId) && str_starts_with($orderId, 'TG-')) {
            $tagihanId = $orderId;
            if (substr_count((string) $orderId, '-') > 2) {
                $parts = explode('-', (string) $orderId);
                if (count($parts) >= 3) {
                    $tagihanId = implode('-', array_slice($parts, 0, 3));
                }
            }
        }

        if ($trx) {
            $trx->update([
                'status_id' => Status::idForFitur('keuangan', $transactionStatus),
                'tipe_pembayaran' => $channel,
                'transaksi_id' => $transactionId,
                'jumlah' => $jumlahBayar ? (float) $jumlahBayar : $trx->jumlah,
            ]);
            $pmId = $trx->PembayaranMidtrans_id;
        } else {
            $created = PembayaranMidtrans::create([
                'jumlah' => $jumlahBayar ? (float) $jumlahBayar : 0,
                'status_id' => Status::idForFitur('keuangan', $transactionStatus),
                'tipe_pembayaran' => $channel,
                'snap_token' => null,
                'redirect_url' => null,
                'order_id' => $orderId,
                'transaksi_id' => $transactionId,
            ]);
            $pmId = $created->PembayaranMidtrans_id;
        }
        if ($tagihanId) {
            $tagihan = TagihanIuranWarga::query()
                ->where('tagihan_iuran_id', $tagihanId)
                ->first();

            if ($tagihan) {
                if ($pmId ?? null) {
                    $tagihan->PembayaranMidtrans_id = $pmId;
                }

                if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                    $tagihan->status_id = Status::idForFitur('keuangan', 'Lunas');

                    if (! $tagihan->tanggal_lunas) {
                        $tagihan->tanggal_lunas = now();
                    }
                }

                $tagihan->save();
            }
        }
        $updated = (bool) $tagihanId;
        if ($request->wantsJson()) {
            return response()->json(['updated' => $updated]);
        }
        $panel = $request->query('panel');
        $target = $panel === 'admin' ? url('/admin/tagihan-iuran-wargas') : url('/warga/pembayaran-iurans');

        return redirect()->to($target);
    }

    private function resolveChannel($paymentType, $notif = null, ?Request $request = null): ?string
    {
        $pt = strtolower((string) $paymentType);
        switch ($pt) {
            case 'bank_transfer':
                $bank = null;
                if ($notif && isset($notif->va_numbers)) {
                    $list = $notif->va_numbers;
                    if (is_array($list) && ! empty($list)) {
                        $bank = is_array($list[0]) ? ($list[0]['bank'] ?? null) : ($list[0]->bank ?? null);
                    }
                }
                if (! $bank && $notif && isset($notif->permata_va_number)) {
                    $bank = 'permata';
                }
                if (! $bank && $request) {
                    $bank = $request->query('bank');
                }

                return $bank ? ('VA '.strtoupper($bank)) : 'Virtual Account';
            case 'echannel':
                return 'Mandiri Bill';
            case 'qris':
                return 'QRIS';
            case 'gopay':
                return 'GoPay';
            case 'credit_card':
                return 'Credit Card';
            default:
                return $paymentType;
        }
    }

    private function bulanIndonesia(int $bulan): string
    {
        $map = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $map[$bulan] ?? (string) $bulan;
    }

    public function bayarTunai(Request $request, TagihanIuranWarga $tagihan)
    {
        $amount = (float) $request->input('nominal_dibayarkan', (float) $tagihan->nominal_tagihan);
        if ($amount <= 0) {
            abort(422, 'Nominal tidak valid');
        }
        $paths = [];
        if ($request->hasFile('bukti')) {
            $files = $request->file('bukti');
            $list = is_array($files) ? $files : [$files];
            foreach ($list as $f) {
                if ($f && $f->isValid()) {
                    $paths[] = $f->store('BuktiTunai', ['disk' => 'public']);
                }
            }
        }
        $penerima = \Illuminate\Support\Facades\Auth::user()?->name;
        $periode = PeriodeIuran::find($tagihan->periode_iuran_id);
        $bulanBayar = $periode ? (int) $periode->bulan : (int) now()->month;
        $trx = PembayaranTunai::create([
            'tagihan_iuran_id' => $tagihan->getKey(),
            'nominal_dibayarkan' => $amount,
            'status_id' => Status::idForFitur('keuangan', 'Lunas') ?? Status::idByName('Lunas'),
            'bukti' => $paths,
            'penerima' => $penerima,
            'periode_iuran_id' => $tagihan->periode_iuran_id,
            'bulan_bayar' => $bulanBayar,
        ]);

        $tagihan->update([
            'PembayaranTunai_id' => $trx->PembayaranTunai_id,
            'status_id' => Status::idForFitur('keuangan', 'Lunas') ?? Status::idByName('Lunas'),
            'tanggal_lunas' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'id' => $trx->PembayaranTunai_id]);
        }
        $panel = $request->query('panel');
        $target = $panel === 'admin' ? url('/admin/tagihan-iuran-wargas') : url('/warga/pembayaran-iurans');

        return redirect()->to($target);
    }

    public function prepareTunai(TagihanIuranWarga $tagihan, Request $request)
    {
        $today = now();
        $prefix = 'BYR-TNI-'.$today->format('dmY').'-';
        $last = \App\Models\PembayaranTunai::query()
            ->whereDate('created_at', $today->toDateString())
            ->where('PembayaranTunai_id', 'like', $prefix.'%')
            ->orderBy('PembayaranTunai_id', 'desc')
            ->first();
        $seq = 1;
        if ($last && is_string($last->PembayaranTunai_id)) {
            $parts = explode('-', $last->PembayaranTunai_id);
            $suffix = end($parts);
            $num = (int) $suffix;
            if ($num > 0) {
                $seq = $num + 1;
            }
        }
        $preview = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

        return response()->json(['next_id' => $preview]);
    }
}
