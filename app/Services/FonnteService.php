<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $account_token;

    // Konstanta endpoint API Fonnte
    const ENDPOINTS = [
        'send_message' => 'https://api.fonnte.com/send',
        'add_device' => 'https://api.fonnte.com/add-device',
        'qr_activation' => 'https://api.fonnte.com/qr',
        'get_devices' => 'https://api.fonnte.com/get-devices',
        'device_profile' => 'https://api.fonnte.com/device',
        'delete_device' => 'https://api.fonnte.com/delete-device',
        'disconnect' => 'https://api.fonnte.com/disconnect',
        'check_device_status' => 'https://api.fonnte.com/get-devices',

    ];

    public function __construct()
    {
        $this->account_token = env('ACCOUNT_TOKEN');
    }

    protected function makeRequest($endpoint, $params = [], $useAccountToken = true, $deviceToken = null)
    {
        $token = $useAccountToken
            ? $this->account_token
            : ($deviceToken ?? null);

        if (! $token) {
            return ['status' => false, 'error' => 'API token or device token is required.'];
        }

        // Gunakan JSON format dan pastikan Content-Type header benar
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json', // Tambahkan header
        ])->post($endpoint, $params);

        // Log respons untuk memudahkan debugging
        Log::info('Fonnte API Response', ['endpoint' => $endpoint, 'response' => $response->json()]);

        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->json()['reason'] ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $response->json(),
        ];
    }

    public function sendWhatsAppMessage($phoneNumber, $message, $deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['send_message'], [
            'target' => $phoneNumber,
            'message' => $message,
        ], '', $deviceToken);
    }

    public function getAllDevices()
    {
        return $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);
    }

    public function addDevice($name, $phoneNumber)
    {
        $params = [
            'name' => $name,
            'device' => $phoneNumber,
            'autoread' => 'false',  // string "false", bukan boolean
            'personal' => 'true',   // string "true", bukan boolean
            'group' => 'false',  // string "false"
        ];

        // Log request untuk memastikan payload benar
        Log::info('Fonnte Add Device Request', ['params' => $params]);

        // Kirim request
        $response = $this->makeRequest(self::ENDPOINTS['add_device'], $params, true);

        // Cek dan log respons API
        if (! $response['status'] || empty($response['data']['status'])) {
            Log::error('Failed to add device', ['response' => $response]);

            return [
                'status' => false,
                'error' => $response['data']['reason'] ?? 'Invalid or empty body value',
            ];
        }

        return [
            'status' => true,
            'data' => $response['data'],
        ];
    }

    public function requestQRActivation($phoneNumber, $deviceToken)
    {
        // Kirim permintaan untuk mengaktifkan akun baru dengan QR code
        $response = Http::withHeaders([
            'Authorization' => $deviceToken, // Gunakan account_token dari properti
            'Accept' => 'application/json, text/plain;q=0.9, */*;q=0.8',
        ])->post(self::ENDPOINTS['qr_activation'], [
            'type' => 'qr',
            'whatsapp' => $phoneNumber, // Nomor WhatsApp yang diaktivasi
        ]);

        Log::info('Fonnte QR Activation response', ['status' => $response->status(), 'body' => $response->json()]);

        // Periksa jika respons gagal dan ambil pesan error dari respons API
        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->body() ?? 'Unknown error occurred',
            ];
        }

        $rawBody = $response->body();
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }

        // Jika API mengembalikan JSON, gunakan langsung
        if (is_array($json)) {
            return [
                'status' => true,
                'data' => $json,
            ];
        }

        // Jika bukan JSON, coba deteksi base64 PNG atau URL
        $data = [];
        if (is_string($rawBody)) {
            $trim = trim($rawBody);
            if (preg_match('/^https?:\\/\\//i', $trim)) {
                $data['url'] = $trim;
            } elseif (preg_match('/^[A-Za-z0-9+\\/=]+$/', $trim) && str_starts_with($trim, 'iVBOR')) {
                $data['png'] = $trim;
            }
        }

        if (! empty($data)) {
            return [
                'status' => true,
                'data' => $data,
            ];
        }

        return [
            'status' => false,
            'error' => 'Invalid QR response format',
        ];
    }

    public function getDeviceProfile($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['device_profile'], [], false, $deviceToken);
    }

    public function disconnectDevice($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['disconnect'], [], false, $deviceToken);
    }

    // Method untuk request OTP menggunakan token perangkat
    public function requestOTPForDeleteDevice($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => ''], false, $deviceToken);
    }

    public function submitOTPForDeleteDevice($otp, $deviceToken)
    {
        Log::info('Menghapus perangkat dengan OTP', ['otp' => $otp, 'device_token' => $deviceToken]);

        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => (int) $otp], false, $deviceToken);
    }

    public function getDeviceStatus($phoneNumber)
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.account_token'), // Ensure you're using the correct token
        ])->get(self::ENDPOINTS['check_device_status'], [
            'whatsapp' => $phoneNumber,
        ]);

        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->body() ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $response->json(),
        ];
    }
}
