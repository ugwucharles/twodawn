<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;

class AssetsController extends Controller
{
    public function html5qrcode(): Response
    {
        $js = Cache::remember('h5qrcode_js_v2_3_10_v2', 60 * 60 * 12, function () {
            $urls = [
                'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
                'https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.10/html5-qrcode.min.js',
            ];
            foreach ($urls as $u) {
                try {
                    $res = Http::timeout(12)
                        ->withHeaders([
                            'Accept' => 'text/javascript,application/javascript,*/*;q=0.8',
                            // Avoid brotli which can reach PHP undecoded
                            'Accept-Encoding' => 'gzip, deflate, identity',
                            'User-Agent' => '2DAWN/host-assets'
                        ])->get($u);
                    if ($res->ok()) {
                        $body = (string) $res->body();
                        // If gzipped bytes slipped through, try to decode
                        $enc = strtolower((string) $res->header('Content-Encoding'));
                        if ((str_contains($enc, 'gzip') || str_starts_with($body, "\x1f\x8b\x08")) && function_exists('gzdecode')) {
                            $decoded = @gzdecode($body);
                            if ($decoded !== false) { $body = $decoded; }
                        }
                        return $body;
                    }
                } catch (\Throwable $e) { /* try next */ }
            }
            return '/*! html5-qrcode missing */\n';
        });
        return response($js, 200, ['Content-Type' => 'application/javascript; charset=UTF-8']);
    }
}