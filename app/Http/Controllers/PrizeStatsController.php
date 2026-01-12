<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\PrizePool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrizeStatsController extends Controller
{
    /**
     * Get prize statistics by country
     * Endpoint: /api/prize-stats?country_id=1 or /api/prize-stats?iso_code=CR
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

        // Get country info
        $country = Country::find($countryId);

        // Get prize pools for this country
        $prizePools = PrizePool::with('prize')
            ->where('country_id', $countryId)
            ->get();

        // Calculate totals
        $totalPrizes = $prizePools->sum('total_quantity');
        $totalAwarded = $prizePools->sum('awarded_quantity');
        $totalRemaining = $totalPrizes - $totalAwarded;

        // Format prize breakdown
        $prizeBreakdown = $prizePools->map(function ($pool) {
            return [
                'prize' => [
                    'id' => $pool->prize->id,
                    'name' => $pool->prize->name,
                ],
                'total' => $pool->total_quantity,
                'awarded' => $pool->awarded_quantity,
                'remaining' => $pool->remaining,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Estadísticas obtenidas exitosamente',
            'data' => [
                'country' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso_code' => $country->iso_code,
                ],
                'summary' => [
                    'total_prizes' => $totalPrizes,
                    'total_awarded' => $totalAwarded,
                    'total_remaining' => $totalRemaining,
                ],
                'prizes' => $prizeBreakdown,
            ],
        ]);
    }
}
