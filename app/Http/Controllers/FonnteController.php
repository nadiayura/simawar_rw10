<?php

namespace App\Http\Controllers;

use App\Models\FonnteDevice;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteController extends Controller
{
    public function qr(FonnteDevice $device, Request $request)
    {
        $service = app(FonnteService::class);
        $resp = $service->requestQRActivation($device->device, $device->token);
        $data = $resp['status'] ? ($resp['data'] ?? []) : [];
        $url = $data['url'] ?? null;
        $pngBase64 = $data['qr'] ?? $data['image'] ?? $data['png'] ?? null;

        if ($url && is_string($url)) {
            $trim = trim($url);
            if (str_starts_with($trim, 'data:image')) {
                if (preg_match('/data:image\\/(?:png|jpeg);base64,([A-Za-z0-9+\\/=]+)/i', $trim, $m)) {
                    $pngBase64 = $m[1];
                    $url = null;
                }
            } elseif (! preg_match('#^https?://#i', $trim)) {
                if (str_starts_with($trim, 'iVBOR')) {
                    $pngBase64 = $trim;
                    $url = null;
                }
            }
        }

        if ($url && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://api.fonnte.com'.(str_starts_with($url, '/') ? $url : '/'.$url);
        }

        if (! $pngBase64 && $url) {
            try {
                $parts = parse_url($url);
                if (! empty($parts['query'])) {
                    parse_str($parts['query'], $q);
                    $maybe = $q['qru'] ?? $q['qr'] ?? null;
                    if (is_string($maybe) && str_starts_with($maybe, 'iVBOR')) {
                        $pngBase64 = $maybe;
                        $url = null;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Parse Fonnte QR url failed', ['error' => $e->getMessage()]);
            }
        }

        if ($url && ! $pngBase64) {
            try {
                $respImg = Http::withHeaders([
                    'Accept' => 'image/png,image/*;q=0.8,*/*;q=0.5',
                ])->get($url);
                if ($respImg->ok()) {
                    $ct = strtolower((string) ($respImg->header('Content-Type') ?? ''));
                    $body = $respImg->body();
                    if (str_starts_with($ct, 'image/')) {
                        $pngBase64 = base64_encode($body);
                        $url = null;
                    } else {
                        if (is_string($body)) {
                            if (preg_match('/data:image\\/(?:png|jpeg);base64,([A-Za-z0-9+\\/=]+)/i', $body, $m)) {
                                $pngBase64 = $m[1];
                                $url = null;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Fetch Fonnte QR image failed', ['error' => $e->getMessage()]);
            }
        }

        if (! $url && ! $pngBase64) {
            return response()->view('fonnte.qr', ['url' => null, 'png' => null, 'error' => $resp['error'] ?? 'Gagal memuat QR'], 500);
        }

        return view('fonnte.qr', ['url' => $url, 'png' => $pngBase64, 'error' => null, 'device' => $device]);
    }
}
