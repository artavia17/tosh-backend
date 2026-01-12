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
        $validator = Validator::make($request->all(), [
            'invoice' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico|max:10240',
        ]);

        if ($validator->fails()) {
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

        return response()->json([
            'status' => 201,
            'message' => 'Invoice uploaded successfully',
            'data' => $code,
        ], 201);
    }
}
