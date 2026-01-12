<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $countries = Country::all();

        return response()->json([
            'status' => 200,
            'message' => 'Countries retrieved successfully',
            'data' => $countries,
        ]);
    }
}
