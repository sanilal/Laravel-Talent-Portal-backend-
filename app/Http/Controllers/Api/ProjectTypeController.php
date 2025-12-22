<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectType;
use Illuminate\Http\JsonResponse;

class ProjectTypeController extends Controller
{
    /**
     * Get all project types
     * GET /api/v1/public/project-types
     * 
     * Used by Create Project form to select project type
     * Compatible with frontend expectations
     */
    public function index(): JsonResponse
    {
        $projectTypes = ProjectType::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name, // Frontend expects this
                    'projectTypeName' => $type->name,
                    'postRequestTypes' => $type->name, // Backwards compatible
                    'slug' => $type->slug,
                    'description' => $type->description,
                    'icon' => $type->icon,
                    'orderType' => $type->sort_order,
                    'sort_order' => $type->sort_order,
                    'is_active' => $type->is_active,
                    'createdAt' => $type->created_at ? $type->created_at->toIso8601String() : null,
                    'updatedAt' => $type->updated_at ? $type->updated_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Data Retrieved Successfully',
            'type' => $projectTypes->count(),
            'data' => $projectTypes,
        ]);
    }

    /**
     * Get a single project type
     * GET /api/v1/public/project-types/{id}
     */
    public function show($id): JsonResponse
    {
        $projectType = ProjectType::findOrFail($id);

        return response()->json([
            'status' => 1,
            'message' => 'Project type retrieved successfully',
            'data' => [
                'id' => $projectType->id,
                'name' => $projectType->name,
                'projectTypeName' => $projectType->name,
                'postRequestTypes' => $projectType->name,
                'slug' => $projectType->slug,
                'description' => $projectType->description,
                'icon' => $projectType->icon,
                'orderType' => $projectType->sort_order,
                'sort_order' => $projectType->sort_order,
                'is_active' => $projectType->is_active,
                'createdAt' => $projectType->created_at ? $projectType->created_at->toIso8601String() : null,
                'updatedAt' => $projectType->updated_at ? $projectType->updated_at->toIso8601String() : null,
            ],
        ]);
    }
}