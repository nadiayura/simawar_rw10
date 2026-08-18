<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\FonnteDevice;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }
    public function index()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/get-devices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Authorization: '.config('services.fonnte.account_token'),
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);


        $data = json_decode($response, true);


        if ($data['status']) {
            $devices = $data['data'];
        } else {
            $devices = [];
        }

        $page_title = 'All Devices';

        return view('devices.index', compact('devices', 'page_title'));
    }

    public function create()
    {
        return view('devices.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device' => 'required|string|max:255',
        ]);

        $accountToken = config('services.fonnte.account_token');

        $response = Http::withHeaders([
            'Authorization' => $accountToken,
        ])->post('https://api.fonnte.com/add-device', [
            'name' => $validated['name'],
            'device' => $validated['device'],
            'autoread' => false,
            'personal' => true,
            'group' => false,
        ]);

        if ($response->failed()) {
            return redirect()->back()->withInput()->with('error', $response->json()['reason'] ?? 'Unknown error occurred');
        }

        $response = $response->json();
        if (! $response['status']) {
            return redirect()->back()->withInput()->with('error', $response['reason'] ?? 'Failed to add device.');
        }

        FonnteDevice::create([
            'name' => $validated['name'],
            'device' => $validated['device'],
            'token' => $response['token'] ?? null, // Pastikan untuk mendapatkan token jika ada
        ]);

        return redirect()->route('devices.index')->with('success', 'Device added successfully!');
    }

    public function activateDevice(Request $request)
    {
        $phoneNumber = $request->input('device');
        $deviceToken = $request->input('token');

        $response = $this->fonnteService->requestQRActivation($phoneNumber, $deviceToken);

        if ($response['status']) {

            return response()->json([
                'status' => true,
                'url' => $response['data']['url'],
            ]);
        }

        return response()->json([
            'status' => false,
            'error' => $response['error'] ?? 'Failed to activate the device.',
        ], 500);
    }

    public function show($id)
    {
        $device = FonnteDevice::findOrFail($id);
        $response = $this->fonnteService->getDeviceProfile($device->token);

        if ($response['status']) {
            return response()->json([
                'html' => view('devices.partials.show', compact('device', 'response'))->render(),
            ]);
        }

        return response()->json([
            'status' => false,
            'error' => 'Gagal mendapatkan profil perangkat: '.$response['error'],
        ], 500);
    }

    public function disconnect(Request $request)
    {
        try {
            $deviceToken = $request->input('token');
            $response = $this->fonnteService->disconnectDevice($deviceToken);

            if ($response['status'] === true) {
                return response()->json(['message' => 'Device disconnected successfully'], 200);
            }

            return response()->json(['error' => $response['error'] ?? 'Failed to disconnect device'], 500);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function destroy($deviceId, Request $request)
    {
        if ($request->otp) {
            $delete = $this->fonnteService->submitOTPForDeleteDevice($request->otp, $deviceId);

            if ($delete['status'] == false) {
                return response()->json(['message' => 'Terjadi kesalahan', 'error' => $delete['error']], 501);
            }

            return response()->json(['message' => 'Device berhasil dihapus']);
        }

        $requestToken = $this->fonnteService->requestOTPForDeleteDevice($deviceId);

        if ($requestToken['status'] == true) {
            return response()->json(['message' => 'Berhasil mengirim token']);
        }

        return response()->json(['message' => 'Gagal mengirim token', 'error' => $requestToken['error']], 500);
    }
    protected function requestOTPForDeleteDevice($notificationId, $deviceId)
    {
        $device = FonnteDevice::findOrFail($deviceId);
        $response = $this->fonnteService->requestOTPForDeleteDevice($device->token);

        if ($response['status']) {
            return response()->json(['message' => 'OTP berhasil dikirim!']);
        } else {
            return response()->json(['message' => 'Gagal mengirim OTP.', 'error' => $response['error']], 500);
        }
    }

    protected function submitOTPForDeleteDevice(Request $request, $deviceId)
    {
        $device = FonnteDevice::findOrFail($deviceId);
        $otp = $request->input('otp');

        Log::info('Mengirim OTP untuk menghapus perangkat', ['device_id' => $deviceId, 'otp' => $otp]);
        $response = $this->fonnteService->submitOTPForDeleteDevice($otp, $device->token);

        if ($response['status']) {
            $device->delete();
            Log::info('Perangkat berhasil dihapus dari sistem dan Fonnte', ['device_id' => $deviceId]);

            return response()->json(['message' => 'Perangkat berhasil dihapus!']);
        } else {
            Log::error('Gagal menghapus perangkat', ['error' => $response['error']]);
            return response()->json(['message' => 'Gagal menghapus perangkat.', 'error' => $response['error']], 500);
        }
    }

    public function checkDeviceStatus()
    {
        $accountToken = config('services.fonnte.token');
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/get-devices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: '.$accountToken,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return response()->json(json_decode($response, true));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'target' => 'required|string',
            'message' => 'required|string',
        ]);

        $deviceToken = $request->header('Authorization');

        if (str_starts_with($deviceToken, 'Bearer ')) {
            $deviceToken = substr($deviceToken, 7);
        }

        $response = $this->fonnteService->sendWhatsAppMessage(
            $request->input('target'),
            $request->input('message'),
            $deviceToken
        );

        if (! $response['status'] || (isset($response['data']['status']) && ! $response['data']['status'])) {
            $errorReason = $response['data']['reason'] ?? 'Unknown error occurred';

            return response()->json(['message' => 'Error', 'error' => $errorReason], 500);
        }

        return response()->json(['message' => 'Pesan berhasil dikirim!', 'data' => $response['data']]);
    }
}
