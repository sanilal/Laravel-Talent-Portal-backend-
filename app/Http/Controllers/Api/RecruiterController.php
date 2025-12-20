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
        $user = $request->user();
        
        // Get all projects for this recruiter
        $projects = Project::where('recruiter_id', $user->id)->get();
        $projectIds = $projects->pluck('id');
        
        // Calculate statistics
        $stats = [
            // Count of active/published projects
            'active_projects' => Project::where('recruiter_id', $user->id)
                ->where('status', 'published')
                ->count(),
            
            // Total applications across all projects
            'total_applications' => Application::whereIn('project_id', $projectIds)->count(),
            
            // Pending applications needing review
            'pending_applications' => Application::whereIn('project_id', $projectIds)
                ->where('status', 'pending')
                ->count(),
            
            // Total project views
            'total_views' => Project::where('recruiter_id', $user->id)
                ->sum('views_count') ?? 0,
        ];
        
        return response()->json($stats);
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