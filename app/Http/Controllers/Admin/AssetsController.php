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
        $js = Cache::remember('h5qrcode_js_v2_3_10', 60 * 60 * 12, function () {
            $urls = [
                'https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
                'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.10/html5-qrcode.min.js',
            ];
            foreach ($urls as $u) {
                try {
                    $res = Http::timeout(10)->get($u);
                    if ($res->ok() && str_contains((string)$res->header('Content-Type'), 'javascript')) {
                        return (string) $res->body();
                    }
                    if ($res->ok()) { return (string) $res->body(); }
                } catch (\Throwable $e) { /* try next */ }
            }
            return '/*! html5-qrcode missing */\n';
        });
        return response($js, 200, ['Content-Type' => 'application/javascript; charset=UTF-8']);
    }
}