<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Winner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WinnerController extends Controller
{
    /**
     * Display a listing of the resource.
     * Country filter is required (country_id or iso_code).
     */
    public function index(Request $request): JsonResponse
    {
        // Validate that country filter is provided
        $request->validate([
            'country_id' => 'required_without:iso_code|exists:countries,id',
            'iso_code' => 'required_without:country_id|exists:countries,iso_code',
        ], [
            'country_id.required_without' => 'Debe proporcionar country_id o iso_code',
            'iso_code.required_without' => 'Debe proporcionar country_id o iso_code',
        ]);

        $countryId = null;

        // Get country ID from either parameter
        if ($request->has('country_id')) {
            $countryId = $request->country_id;
        } elseif ($request->has('iso_code')) {
            $country = Country::where('iso_code', $request->iso_code)->first();
            $countryId = $country?->id;
        }

        if (!$countryId) {
            return response()->json([
                'status' => 400,
                'message' => 'País no encontrado',
                'data' => [],
            ], 400);
        }

        // Get winners filtered by country, only from public draw periods
        $winners = Winner::with(['user', 'country', 'code', 'prize', 'drawPeriod'])
            ->where('country_id', $countryId)
            ->whereHas('drawPeriod', function ($query) {
                $query->where('is_public', true);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('draw_period_id')
            ->map(function ($periodGroup) {
                $period = $periodGroup->first()->drawPeriod;
                return [
                    'period' => [
                        'id' => $period->id,
                        'name' => $period->name,
                        'start_date' => $period->start_date->format('Y-m-d'),
                        'end_date' => $period->end_date->format('Y-m-d'),
                    ],
                    'winners' => $periodGroup->map(function ($winner, $index) {
                        return [
                            'id' => $winner->id,
                            'position' => "Ganador " . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                            'code' => $winner->code?->code,
                            'prize' => $winner->prize?->name,
                            'user' => [
                                'id' => $winner->user->id,
                                'name' => $winner->user->name,
                                'email' => $winner->user->email,
                            ],
                            'country' => [
                                'id' => $winner->country->id,
                                'name' => $winner->country->name,
                                'iso_code' => $winner->country->iso_code,
                            ],
                            'notes' => $winner->notes,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'status' => 200,
            'message' => 'Ganadores obtenidos exitosamente',
            'data' => $winners,
        ]);
    }
}
