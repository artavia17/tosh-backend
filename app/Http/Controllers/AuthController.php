<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'email' => 'required|string|email|max:255|unique:users',
            'id_type' => 'required|string|max:50',
            'id_number' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'marketing_opt_in' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
            'phone_opt_in' => 'boolean',
            'email_opt_in' => 'boolean',
            'sms_opt_in' => 'boolean',
            'data_treatment_accepted' => 'required|accepted',
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country_id' => $request->country_id,
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'phone_number' => $request->phone_number,
            'marketing_opt_in' => $request->marketing_opt_in ?? false,
            'whatsapp_opt_in' => $request->whatsapp_opt_in ?? false,
            'phone_opt_in' => $request->phone_opt_in ?? false,
            'email_opt_in' => $request->email_opt_in ?? false,
            'sms_opt_in' => $request->sms_opt_in ?? false,
            'data_treatment_accepted' => $request->data_treatment_accepted,
            'terms_accepted' => $request->terms_accepted,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('id_number', $request->id_number)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function account(Request $request): JsonResponse
    {
        $user = $request->user()->load('codes');

        $codes = $user->codes->map(function ($code) {
            return [
                'id' => $code->id,
                'invoice_url' => url("/api/protected/invoices/{$code->id}"),
                'created_at' => $code->created_at,
                'updated_at' => $code->updated_at,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Account retrieved successfully',
            'data' => [
                'name' => $user->name,
                'codes' => $codes,
            ],
        ]);
    }
}
