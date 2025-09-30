<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return response()->json(['stats' => []]);
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