<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DropdownValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    /**
     * Get dropdown values by type
     * GET /api/v1/dropdown-list?type=1
     * 
     * Types:
     * 1 = Height
     * 2 = Skin Tone
     * 3 = Weight
     * 4 = Age Range
     * 5 = Vehicle Type
     * 6 = Service Type
     * 7 = Event Type
     * 8 = Budget Range
     * 9 = Eye Color
     * 10 = Hair Color
     * 11 = Body Type
     * 12 = Vocal Range
     * 13 = Experience Level
     * 14 = Language Proficiency
     * 15 = Gender
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|integer|min:1|max:15',
        ]);

        $type = $request->input('type');
        
        $values = DropdownValue::ofType($type)
            ->active()
            ->ordered()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'value' => $item->value,
                    'value_secondary' => $item->value_secondary,
                    'code' => $item->code,
                    'type' => $item->type,
                    'description' => $item->description,
                    'metadata' => $item->metadata,
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Data fetched successfully',
            'type' => $type,
            'type_name' => DropdownValue::getTypeName($type),
            'data' => $values,
        ]);
    }

    /**
     * Get multiple dropdown types at once
     * POST /api/v1/dropdown-list/multiple
     * Body: { "types": [1, 2, 3] }
     */
    public function multiple(Request $request): JsonResponse
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'integer|min:1|max:15',
        ]);

        $types = $request->input('types');
        $result = [];

        foreach ($types as $type) {
            $values = DropdownValue::ofType($type)
                ->active()
                ->ordered()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value,
                        'value_secondary' => $item->value_secondary,
                        'code' => $item->code,
                        'type' => $item->type,
                    ];
                });

            $result[] = [
                'type' => $type,
                'type_name' => DropdownValue::getTypeName($type),
                'values' => $values,
            ];
        }

        return response()->json([
            'status' => 1,
            'message' => 'Data fetched successfully',
            'data' => $result,
        ]);
    }

    /**
     * Get all dropdown types with their values
     * GET /api/v1/dropdown-list/all
     */
    public function all(): JsonResponse
    {
        $allTypes = range(1, 15);
        $result = [];

        foreach ($allTypes as $type) {
            $values = DropdownValue::ofType($type)
                ->active()
                ->ordered()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value,
                        'value_secondary' => $item->value_secondary,
                        'code' => $item->code,
                        'type' => $item->type,
                    ];
                });

            if ($values->isNotEmpty()) {
                $result[] = [
                    'type' => $type,
                    'type_name' => DropdownValue::getTypeName($type),
                    'values' => $values,
                ];
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'All dropdown data fetched successfully',
            'data' => $result,
        ]);
    }
}