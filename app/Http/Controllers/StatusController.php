<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => 'Backend running',
            'data' => [
                'date' => now()->toIso8601String(),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'php_ini_path' => php_ini_loaded_file(),
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
            ],
        ]);
    }
}
