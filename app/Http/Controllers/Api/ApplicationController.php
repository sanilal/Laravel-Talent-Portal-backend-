<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    /**
     * Get all applications for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $query = $request->user()->applications()
                ->with(['project.recruiter', 'project.category']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $applications = $query->orderByDesc('created_at')->get();

            return response()->json([
                'applications' => $applications,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch applications: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new application
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|uuid|exists:projects,id',
            'cover_letter' => 'required|string|min:100|max:2000',
            'proposed_rate' => 'nullable|numeric|min:0',
            'proposed_duration' => 'nullable|integer|min:1',
            'estimated_start_date' => 'nullable|date|after:today',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if project exists and is open
            $project = Project::find($request->project_id);

            if (!$project) {
                return response()->json([
                    'message' => 'Project not found',
                ], 404);
            }

            if ($project->status !== 'open') {
                return response()->json([
                    'message' => 'This project is not accepting applications',
                ], 400);
            }

            // Check if user already applied
            $existingApplication = Application::where('project_id', $request->project_id)
                ->where('talent_id', $request->user()->id)
                ->first();

            if ($existingApplication) {
                return response()->json([
                    'message' => 'You have already applied to this project',
                ], 409);
            }

            // Create application
            $application = Application::create(array_merge(
                $request->only([
                    'project_id', 'cover_letter', 'proposed_rate', 
                    'proposed_duration', 'estimated_start_date', 'attachments'
                ]),
                [
                    'talent_id' => $request->user()->id,
                    'status' => 'pending',
                ]
            ));

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => $application->load('project'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create application: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single application
     */
    public function show(Request $request, $id)
    {
        try {
            $application = Application::where('id', $id)
                ->where(function($query) use ($request) {
                    $query->where('talent_id', $request->user()->id)
                          ->orWhereHas('project', function($q) use ($request) {
                              $q->where('recruiter_id', $request->user()->id);
                          });
                })
                ->with(['project', 'talent.talentProfile'])
                ->first();

            if (!$application) {
                return response()->json([
                    'message' => 'Application not found',
                ], 404);
            }

            return response()->json([
                'application' => $application,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch application: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update application status (for recruiters)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewing,shortlisted,accepted,rejected,withdrawn',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $application = Application::find($id);

            if (!$application) {
                return response()->json([
                    'message' => 'Application not found',
                ], 404);
            }

            // Check authorization - only project owner or talent can update
            $user = $request->user();
            $isProjectOwner = $application->project->recruiter_id === $user->id;
            $isTalent = $application->talent_id === $user->id;

            if (!$isProjectOwner && !$isTalent) {
                return response()->json([
                    'message' => 'Unauthorized to update this application',
                ], 403);
            }

            // Talents can only withdraw their own applications
            if ($isTalent && $request->status !== 'withdrawn') {
                return response()->json([
                    'message' => 'You can only withdraw your application',
                ], 403);
            }

            $application->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Application status updated successfully',
                'application' => $application->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update application status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update application status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add notes to application (for recruiters)
     */
    public function addNotes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $application = Application::find($id);

            if (!$application) {
                return response()->json([
                    'message' => 'Application not found',
                ], 404);
            }

            // Only project owner can add notes
            if ($application->project->recruiter_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized to add notes to this application',
                ], 403);
            }

            $application->update([
                'recruiter_notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Notes added successfully',
                'application' => $application->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add notes: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to add notes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Withdraw application (for talents)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $application = Application::where('id', $id)
                ->where('talent_id', $request->user()->id)
                ->first();

            if (!$application) {
                return response()->json([
                    'message' => 'Application not found',
                ], 404);
            }

            // Can only withdraw pending or reviewing applications
            if (!in_array($application->status, ['pending', 'reviewing'])) {
                return response()->json([
                    'message' => 'Cannot withdraw application with current status',
                ], 400);
            }

            $application->update(['status' => 'withdrawn']);

            return response()->json([
                'message' => 'Application withdrawn successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to withdraw application: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to withdraw application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get all applications for authenticated talent user
     * GET /api/v1/talent/applications
     * 
     * Returns format matching frontend expectations:
     * { data: [...], pagination: {...} }
     */
    public function talentApplications(Request $request)
    {
        try {
            $query = Application::where('talent_id', $request->user()->id)
                ->with(['project.recruiter', 'project.category', 'project.subcategory']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Support limit parameter (default 15, from frontend request it's 5)
            $limit = $request->input('limit', 15);
            $applications = $query->orderByDesc('created_at')->paginate($limit);

            // ✅ FIXED: Return 'data' instead of 'applications' to match frontend expectations
            return response()->json([
                'success' => true,
                'data' => $applications->items(),
                'pagination' => [
                    'total' => $applications->total(),
                    'per_page' => $applications->perPage(),
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'from' => $applications->firstItem(),
                    'to' => $applications->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch talent applications: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applications',
                'error' => $e->getMessage(),
                'data' => [], // Return empty array on error
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get all applications for projects owned by authenticated recruiter
     * GET /api/v1/recruiter/applications
     */
    public function recruiterApplications(Request $request)
    {
        try {
            $query = Application::whereHas('project', function($q) use ($request) {
                $q->where('recruiter_id', $request->user()->id);
            })->with(['talent.talentProfile', 'project']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by project
            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Support limit parameter
            $limit = $request->input('limit', 15);
            $applications = $query->orderByDesc('created_at')->paginate($limit);

            // ✅ FIXED: Return 'data' instead of 'applications'
            return response()->json([
                'success' => true,
                'data' => $applications->items(),
                'pagination' => [
                    'total' => $applications->total(),
                    'per_page' => $applications->perPage(),
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'from' => $applications->firstItem(),
                    'to' => $applications->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch recruiter applications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applications',
                'error' => $e->getMessage(),
                'data' => [], // Return empty array on error
            ], 500);
        }
    }

    /**
     * Apply to a project (for talents)
     * POST /api/v1/projects/{id}/apply
     */
    public function apply(Request $request, $id)
    {
        // This is the same as store method, just with different route parameter
        $validator = Validator::make(array_merge($request->all(), ['project_id' => $id]), [
            'project_id' => 'required|uuid|exists:projects,id',
            'cover_letter' => 'required|string|min:100|max:2000',
            'proposed_rate' => 'nullable|numeric|min:0',
            'proposed_duration' => 'nullable|integer|min:1',
            'estimated_start_date' => 'nullable|date|after:today',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if project exists and is open
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'message' => 'Project not found',
                ], 404);
            }

            if ($project->status !== 'open') {
                return response()->json([
                    'message' => 'This project is not accepting applications',
                ], 400);
            }

            // Check if user already applied
            $existingApplication = Application::where('project_id', $id)
                ->where('talent_id', $request->user()->id)
                ->first();

            if ($existingApplication) {
                return response()->json([
                    'message' => 'You have already applied to this project',
                ], 409);
            }

            // Create application
            $application = Application::create(array_merge(
                $request->only([
                    'cover_letter', 'proposed_rate',
                    'proposed_duration', 'estimated_start_date', 'attachments'
                ]),
                [
                    'project_id' => $id,
                    'talent_id' => $request->user()->id,
                    'status' => 'pending',
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'application' => $application->load('project'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to apply to project: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}