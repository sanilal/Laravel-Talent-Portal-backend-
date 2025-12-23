<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RecruiterController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json(['message' => 'Recruiter profile - Coming soon']);
    }

    public function updateProfile(Request $request)
    {
        return response()->json(['message' => 'Update profile - Coming soon']);
    }

    public function updateLogo(Request $request)
    {
        return response()->json(['message' => 'Update logo - Coming soon']);
    }

    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get recruiter profile first
            $recruiterProfile = $user->recruiterProfile;
            
            if (!$recruiterProfile) {
                return response()->json([
                    'message' => 'Recruiter profile not found',
                    'active_projects' => 0,
                    'total_applications' => 0,
                    'pending_applications' => 0,
                    'total_views' => 0,
                ]);
            }
            
            // Use recruiter_profile_id instead of recruiter_id
            $projects = Project::where('recruiter_profile_id', $recruiterProfile->id)->get();
            $projectIds = $projects->pluck('id');
            
            // Calculate statistics
            $stats = [
                'active_projects' => Project::where('recruiter_profile_id', $recruiterProfile->id)
                    ->where('status', 'published')
                    ->count(),
                
                'total_applications' => $projectIds->isNotEmpty() 
                    ? Application::whereIn('project_id', $projectIds)->count()
                    : 0,
                
                'pending_applications' => $projectIds->isNotEmpty()
                    ? Application::whereIn('project_id', $projectIds)
                        ->where('status', 'pending')
                        ->count()
                    : 0,
                
                'total_views' => Project::where('recruiter_profile_id', $recruiterProfile->id)
                    ->sum('views_count') ?? 0,
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            \Log::error('Recruiter Dashboard Error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to load dashboard',
                'active_projects' => 0,
                'total_applications' => 0,
                'pending_applications' => 0,
                'total_views' => 0,
            ], 500);
        }
    }

    public function searchTalents(Request $request)
    {
        return response()->json(['talents' => []]);
    }

    public function viewTalent(Request $request, $id)
    {
        return response()->json(['talent' => null]);
    }

    public function saveTalent(Request $request, $id)
    {
        return response()->json(['message' => 'Save talent - Coming soon']);
    }

    public function unsaveTalent(Request $request, $id)
    {
        return response()->json(['message' => 'Unsave talent - Coming soon']);
    }
}