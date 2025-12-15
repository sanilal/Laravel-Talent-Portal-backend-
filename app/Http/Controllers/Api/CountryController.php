<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Get all countries with codes
     * GET /api/v1/public/countries
     * Compatible with yourmoca.com/api/getAllCountryCode
     */
    public function index(): JsonResponse
    {
        $countries = Country::active()
            ->orderByName()
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'countryName' => $country->country_name,
                    'countryCode' => $country->country_code,
                    'dialing_code' => $country->dialing_code,
                    'emoji' => $country->emoji,
                    'currency' => $country->currency,
                    'currencySymbol' => $country->currency_symbol,
                    'flag' => $country->flag,
                ];
            });

        return response()->json([
            'message' => 'Getting all countries code are retrived',
            'status' => 1,
            'data' => $countries,
        ]);
    }

    /**
     * Get states for a specific country
     * GET /api/v1/public/states?countryId=50
     * Compatible with yourmoca.com/api/getStates
     */
    public function states(Request $request): JsonResponse
    {
        $request->validate([
            'countryId' => 'required|integer|exists:countries,id',
        ]);

        $countryId = $request->input('countryId');
        $country = Country::with(['states' => function ($query) {
            $query->active()->orderByName();
        }])->findOrFail($countryId);

        $states = $country->states->map(function ($state) {
            return [
                'id' => $state->id,
                'stateName' => $state->state_name,
                'stateCode' => $state->state_code,
                'countryId' => $state->country_id,
                'createdAt' => $state->created_at->toIso8601String(),
                'updatedAt' => $state->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'message' => 'Getting states for the specified country ID',
            'status' => 1,
            'data' => [
                'id' => $country->id,
                'countryName' => $country->country_name,
                'countryCode' => $country->country_code,
                'dialing_code' => $country->dialing_code,
                'states' => $states,
            ],
        ]);
    }

    /**
     * Get a single country with its states
     * GET /api/v1/public/countries/{id}
     */
    public function show($id): JsonResponse
    {
        $country = Country::with(['states' => function ($query) {
            $query->active()->orderByName();
        }])->findOrFail($id);

        return response()->json([
            'status' => 1,
            'message' => 'Country retrieved successfully',
            'data' => [
                'id' => $country->id,
                'countryName' => $country->country_name,
                'countryCode' => $country->country_code,
                'dialing_code' => $country->dialing_code,
                'emoji' => $country->emoji,
                'currency' => $country->currency,
                'currencySymbol' => $country->currency_symbol,
                'states' => $country->states->map(function ($state) {
                    return [
                        'id' => $state->id,
                        'stateName' => $state->state_name,
                        'stateCode' => $state->state_code,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Search countries by name or code
     * GET /api/v1/public/countries/search?q=united
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->input('q');

        $countries = Country::active()
            ->where(function ($q) use ($query) {
                $q->where('country_name', 'like', "%{$query}%")
                    ->orWhere('country_code', 'like', "%{$query}%");
            })
            ->orderByName()
            ->limit(20)
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'countryName' => $country->country_name,
                    'countryCode' => $country->country_code,
                    'dialing_code' => $country->dialing_code,
                    'emoji' => $country->emoji,
                    'currency' => $country->currency,
                    'currencySymbol' => $country->currency_symbol,
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Search results',
            'query' => $query,
            'data' => $countries,
        ]);
    }
}