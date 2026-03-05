<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Log para debugging en producción
        \Log::info('Code upload attempt', [
            'has_file' => $request->hasFile('invoice'),
            'file_size' => $request->hasFile('invoice') ? $request->file('invoice')->getSize() : null,
            'mime_type' => $request->hasFile('invoice') ? $request->file('invoice')->getMimeType() : null,
            'extension' => $request->hasFile('invoice') ? $request->file('invoice')->getClientOriginalExtension() : null,
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ]);

        $validator = Validator::make($request->all(), [
            'invoice' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico|max:10240',
        ], [
            'invoice.required' => 'El archivo de factura es requerido',
            'invoice.image' => 'El archivo debe ser una imagen válida',
            'invoice.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg, webp, bmp, tiff o ico',
            'invoice.max' => 'La imagen no debe ser mayor a 10 MB',
        ]);

        if ($validator->fails()) {
            \Log::warning('Code upload validation failed', [
                'errors' => $validator->errors()->toArray(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoicePath = $request->file('invoice')->store('invoices', 'public');

        $code = Code::create([
            'user_id' => $request->user()->id,
            'invoice_path' => $invoicePath,
        ]);

        \Log::info('Code uploaded successfully', [
            'code_id' => $code->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Invoice uploaded successfully',
            'data' => $code,
        ], 201);
    }
}
