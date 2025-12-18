<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['projects' => []]);
    }

    public function search(Request $request)
    {
        return response()->json(['results' => []]);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['project' => null]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Create project - Coming soon']);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update project - Coming soon']);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['message' => 'Delete project - Coming soon']);
    }

    public function publish(Request $request, $id)
    {
        return response()->json(['message' => 'Publish project - Coming soon']);
    }

    public function close(Request $request, $id)
    {
        return response()->json(['message' => 'Close project - Coming soon']);
    }

    public function applications(Request $request, $id)
    {
        return response()->json(['applications' => []]);
    }

    /**
     * Get public projects listing (for authenticated users browsing projects)
     * GET /api/v1/projects
     */
    public function publicIndex(Request $request)
    {
        $query = \App\Models\Project::where('status', 'open')
            ->with(['recruiter', 'category', 'subcategory']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filter by budget range
        if ($request->has('budget_min')) {
            $query->where('budget', '>=', $request->budget_min);
        }
        if ($request->has('budget_max')) {
            $query->where('budget', '<=', $request->budget_max);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(15);

        return response()->json([
            'success' => true,
            'projects' => $projects,
        ]);
    }

    /**
     * Get single public project details (for authenticated users viewing a project)
     * GET /api/v1/projects/{id}
     */
    public function publicShow(Request $request, $id)
    {
        $project = \App\Models\Project::where('id', $id)
            ->where('status', 'open')
            ->with(['recruiter', 'category', 'subcategory', 'applications'])
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or not available',
            ], 404);
        }

        // Check if current user has already applied
        $hasApplied = false;
        if ($request->user()) {
            $hasApplied = \App\Models\Application::where('project_id', $id)
                ->where('talent_id', $request->user()->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'project' => $project,
            'has_applied' => $hasApplied,
        ]);
    }
}