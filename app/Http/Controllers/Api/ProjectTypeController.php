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
     * Compatible with yourmoca.com structure
     */
    public function index(): JsonResponse
    {
        $projectTypes = ProjectType::active()
            ->ordered()
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'postRequestTypes' => $type->name,
                    'slug' => $type->slug,
                    'description' => $type->description,
                    'icon' => $type->icon,
                    'createdAt' => $type->created_at->toIso8601String(),
                    'updatedAt' => $type->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 1,
            'message' => 'Data fetched successfully',
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
                'postRequestTypes' => $projectType->name,
                'slug' => $projectType->slug,
                'description' => $projectType->description,
                'icon' => $projectType->icon,
                'createdAt' => $projectType->created_at->toIso8601String(),
                'updatedAt' => $projectType->updated_at->toIso8601String(),
            ],
        ]);
    }
}