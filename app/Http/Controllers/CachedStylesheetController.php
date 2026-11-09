<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CachedStylesheetController extends Controller
{
    public function __invoke(string $filename): BinaryFileResponse
    {
        abort_unless(preg_match('/\Aapp-[A-Za-z0-9_-]+\.css\z/', $filename) === 1, 404);

        $path = public_path('build/assets/'.$filename);

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Type' => 'text/css; charset=UTF-8',
        ]);
    }
}
