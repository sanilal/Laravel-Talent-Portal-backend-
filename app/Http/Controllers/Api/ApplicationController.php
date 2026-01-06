<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Get all applications for authenticated user
     */
    public function index(Request $request)
    {
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

        // TODO: Send notification to project owner

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application->load('project'),
        ], 201);
    }

    /**
     * Get single application
     */
    public function show(Request $request, $id)
    {
        $application = Application::where('id', $id)
            ->where(function($query) use ($request) {
                $query->where('talent_id', $request->user()->id)
                      ->orWhereHas('project', function($q) use ($request) {
                          $q->where('recruiter_id', $request->user()->id);
                      })
                      ->orWhereHas('castingCall', function($q) use ($request) {
                          $q->where('recruiter_id', $request->user()->id);
                      });
            })
            ->with(['project', 'castingCall', 'talent.talentProfile'])
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'Application not found',
            ], 404);
        }

        return response()->json([
            'application' => $application,
        ]);
    }

    /**
     * Update application status (for recruiters)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,under_review,shortlisted,interview_scheduled,accepted,rejected,withdrawn',
            'notes' => 'nullable|string|max:2000',
            'feedback' => 'nullable|string|max:2000',
            'interview_date' => 'nullable|date',
            'interview_location' => 'nullable|string|max:500',
            'interview_type' => 'nullable|in:in-person,video,phone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Check authorization - only recruiter (project/casting call owner) can update status
        $user = $request->user();
        $isOwner = $application->recruiter_id === $user->id;

        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this application',
            ], 403);
        }

        // Use model method to update status with timestamps
        $success = $application->updateStatus($request->status, $request->notes);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 400);
        }

        // Add feedback to talent if provided
        if ($request->feedback) {
            $application->update(['feedback_to_talent' => $request->feedback]);
        }

        // Add interview details if status is interview_scheduled
        if ($request->status === 'interview_scheduled') {
            $interviewData = [];
            if ($request->interview_date) {
                $interviewData['interview_date'] = $request->interview_date;
            }
            if ($request->interview_location) {
                $interviewData['interview_location'] = $request->interview_location;
            }
            if ($request->interview_type) {
                $interviewData['interview_type'] = $request->interview_type;
            }

            if (!empty($interviewData)) {
                $application->update($interviewData);
            }
        }

        // Mark as read by recruiter
        $application->markAsRead();

        // TODO: Send notification to talent

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $application->fresh()->load(['castingCall', 'project', 'talent.talentProfile']),
        ]);
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
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Only recruiter (project/casting call owner) can add notes
        if ($application->recruiter_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to add notes to this application',
            ], 403);
        }

        $application->update([
            'recruiter_notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes added successfully',
            'data' => $application->fresh(),
        ]);
    }

    /**
     * Withdraw application (for talents)
     */
    public function destroy(Request $request, $id)
    {
        $application = Application::where('id', $id)
            ->where('talent_id', $request->user()->id)
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Check if application can be withdrawn
        if (!$application->canBeWithdrawn()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot withdraw application with current status. Only pending and under review applications can be withdrawn.',
            ], 400);
        }

        // Use model method to update status with timestamps
        $application->updateStatus(Application::STATUS_WITHDRAWN);

        // TODO: Send notification to recruiter

        return response()->json([
            'success' => true,
            'message' => 'Application withdrawn successfully',
        ]);
    }

    /**
     * Get all applications for authenticated talent user
     * GET /api/v1/talent/applications
     */
    public function talentApplications(Request $request)
    {
        $query = Application::where('talent_id', $request->user()->id)
            ->with([
                'project.recruiter',
                'project.category',
                'project.subcategory',
                'castingCall.recruiter',
                'castingCall.genre',
                'castingCall.projectType',
            ]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'applications' => $applications,
        ]);
    }

    /**
     * Get all applications for projects and casting calls owned by authenticated recruiter
     * GET /api/v1/recruiter/applications
     */
    public function recruiterApplications(Request $request)
    {
        // Get applications for both projects AND casting calls owned by this recruiter
        $query = Application::where(function($q) use ($request) {
            // Applications for recruiter's projects
            $q->whereHas('project', function($subQ) use ($request) {
                $subQ->where('recruiter_id', $request->user()->id);
            })
            // OR applications for recruiter's casting calls
            ->orWhereHas('castingCall', function($subQ) use ($request) {
                $subQ->where('recruiter_id', $request->user()->id);
            });
        })->with(['talent.talentProfile', 'project', 'castingCall']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by casting call
        if ($request->has('casting_call_id')) {
            $query->where('casting_call_id', $request->casting_call_id);
        }

        $applications = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'applications' => $applications,
        ]);
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

        // TODO: Send notification to project owner

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application' => $application->load('project'),
        ], 201);
    }
}