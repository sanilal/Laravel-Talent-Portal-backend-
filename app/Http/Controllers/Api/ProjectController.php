<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Get recruiter's projects
     * GET /api/v1/recruiter/projects
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->recruiterProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Recruiter profile not found',
            ], 404);
        }

        $query = Project::where('recruiter_profile_id', $user->recruiterProfile->id)
            ->with(['projectType', 'category']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
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
            'data' => $projects,
        ]);
    }

    /**
     * Create a new project
     * POST /api/v1/recruiter/projects
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->recruiterProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Recruiter profile not found. Please create a recruiter profile first.',
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:10|max:255',
            'description' => 'required|string|min:50',
            'project_type_id' => 'required|exists:project_types,id',
            'primary_category_id' => 'nullable|uuid|exists:categories,id',
            'work_type' => 'nullable|in:on_site,remote,hybrid',
            'experience_level' => 'nullable|in:entry,intermediate,advanced,expert',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'budget_currency' => 'required|string|max:3',
            'budget_type' => 'nullable|in:fixed,hourly,daily,negotiable',
            'budget_negotiable' => 'boolean',
            'positions_available' => 'nullable|integer|min:1',
            'application_deadline' => 'nullable|date|after:today',
            'project_start_date' => 'nullable|date',
            'project_end_date' => 'nullable|date|after:project_start_date',
            'duration' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'skills_required' => 'nullable|array',
            'skills_required.*' => 'uuid|exists:skills,id',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'visibility' => 'nullable|in:public,private,invited_only',
            'urgency' => 'nullable|in:low,normal,high,urgent',
            'is_featured' => 'boolean',
            'requires_portfolio' => 'boolean',
            'requires_demo_reel' => 'boolean',
            'application_questions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create project
            $project = Project::create([
                'recruiter_profile_id' => $user->recruiterProfile->id,
                'posted_by' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'project_type_id' => $request->project_type_id,
                'primary_category_id' => $request->primary_category_id,
                'work_type' => $request->work_type ?? 'on_site',
                'experience_level' => $request->experience_level ?? 'intermediate',
                'budget_min' => $request->budget_min,
                'budget_max' => $request->budget_max,
                'budget_currency' => $request->budget_currency ?? 'AED',
                'budget_type' => $request->budget_type ?? 'fixed',
                'budget_negotiable' => $request->budget_negotiable ?? false,
                'positions_available' => $request->positions_available ?? 1,
                'application_deadline' => $request->application_deadline,
                'project_start_date' => $request->project_start_date,
                'project_end_date' => $request->project_end_date,
                'duration' => $request->duration,
                'location' => $request->location,
                'skills_required' => $request->skills_required ?? [],
                'requirements' => $request->requirements,
                'responsibilities' => $request->responsibilities,
                'deliverables' => $request->deliverables,
                'status' => 'draft', // Start as draft
                'visibility' => $request->visibility ?? 'public',
                'urgency' => $request->urgency ?? 'normal',
                'is_featured' => $request->is_featured ?? false,
                'requires_portfolio' => $request->requires_portfolio ?? false,
                'requires_demo_reel' => $request->requires_demo_reel ?? false,
                'application_questions' => $request->application_questions ?? [],
                'views_count' => 0,
                'applications_count' => 0,
            ]);

            // Attach skills if provided
            if ($request->has('skills_required') && is_array($request->skills_required)) {
                $project->skills()->attach($request->skills_required);
            }

            DB::commit();

            // Load relationships
            $project->load(['projectType', 'category', 'recruiterProfile.user']);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single project
     * GET /api/v1/recruiter/projects/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->with(['projectType', 'category', 'skills', 'applications'])
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Update project
     * PUT/PATCH /api/v1/recruiter/projects/{id}
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        // Validation (same as store but all fields optional)
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|min:10|max:255',
            'description' => 'sometimes|required|string|min:50',
            'project_type_id' => 'sometimes|required|exists:project_types,id',
            'primary_category_id' => 'nullable|uuid|exists:categories,id',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'skills_required' => 'nullable|array',
            // ... other fields
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $project->update($request->only([
                'title', 'description', 'project_type_id', 'primary_category_id',
                'work_type', 'experience_level', 'budget_min', 'budget_max',
                'budget_currency', 'budget_type', 'positions_available',
                'application_deadline', 'project_start_date', 'project_end_date',
                'duration', 'location', 'requirements', 'responsibilities',
                'deliverables', 'visibility', 'urgency', 'is_featured',
                'requires_portfolio', 'requires_demo_reel', 'application_questions'
            ]));

            // Update skills if provided
            if ($request->has('skills_required')) {
                $project->skills()->sync($request->skills_required);
            }

            DB::commit();

            $project->load(['projectType', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete project
     * DELETE /api/v1/recruiter/projects/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Publish project
     * POST /api/v1/recruiter/projects/{id}/publish
     */
    public function publish(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $project->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project published successfully',
            'data' => $project,
        ]);
    }

    /**
     * Close project
     * POST /api/v1/recruiter/projects/{id}/close
     */
    public function close(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $project->update([
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project closed successfully',
            'data' => $project,
        ]);
    }

    /**
     * Get project applications
     * GET /api/v1/recruiter/projects/{id}/applications
     */
    public function applications(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::where('id', $id)
            ->where('recruiter_profile_id', $user->recruiterProfile->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $applications = $project->applications()
            ->with(['talent.talentProfile'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Get public projects listing (for talents browsing projects)
     * GET /api/v1/projects
     */
    public function publicIndex(Request $request)
    {
        $query = Project::where('status', 'published')
            ->where('visibility', 'public')
            ->with(['recruiterProfile.user', 'projectType', 'category']);

        // Filter by project type
        if ($request->has('project_type_id')) {
            $query->where('project_type_id', $request->project_type_id);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('primary_category_id', $request->category_id);
        }

        // Filter by budget range
        if ($request->has('budget_min')) {
            $query->where('budget_max', '>=', $request->budget_min);
        }
        if ($request->has('budget_max')) {
            $query->where('budget_min', '<=', $request->budget_max);
        }

        // Search
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
            'data' => $projects,
        ]);
    }

    /**
     * Get single public project
     * GET /api/v1/projects/{id}
     */
    public function publicShow(Request $request, $id)
    {
        $project = Project::where('id', $id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->with(['recruiterProfile.user', 'projectType', 'category', 'skills'])
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or not available',
            ], 404);
        }

        // Increment views
        $project->incrementViews();

        // Check if current user has applied
        $hasApplied = false;
        if ($request->user()) {
            $hasApplied = $project->applications()
                ->where('talent_id', $request->user()->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data' => $project,
            'has_applied' => $hasApplied,
        ]);
    }
}