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
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $code = Code::create([
            'user_id' => $request->user()->id,
            'code' => $request->code,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Code submitted successfully',
            'data' => $code,
        ], 201);
    }
}
