<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function show(Request $request, $id)
    {
        $code = Code::find($id);

        if (!$code) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Verificar que el usuario autenticado sea el dueño de la factura
        if ($code->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found',
            ], 404);
        }

        if (!Storage::disk('public')->exists($code->invoice_path)) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice file not found',
            ], 404);
        }

        $file = Storage::disk('public')->get($code->invoice_path);
        $mimeType = Storage::disk('public')->mimeType($code->invoice_path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
