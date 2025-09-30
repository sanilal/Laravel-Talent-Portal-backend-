<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load('talentProfile');
        return response()->json(['profile' => $user]);
    }

    public function updateProfile(Request $request)
    {
        return response()->json(['message' => 'Profile update - Coming soon']);
    }

    public function updateAvatar(Request $request)
    {
        return response()->json(['message' => 'Avatar update - Coming soon']);
    }

    public function portfolios(Request $request)
    {
        return response()->json(['portfolios' => []]);
    }

    public function createPortfolio(Request $request)
    {
        return response()->json(['message' => 'Create portfolio - Coming soon']);
    }

    public function updatePortfolio(Request $request, $id)
    {
        return response()->json(['message' => 'Update portfolio - Coming soon']);
    }

    public function deletePortfolio(Request $request, $id)
    {
        return response()->json(['message' => 'Delete portfolio - Coming soon']);
    }

    public function skills(Request $request)
    {
        return response()->json(['skills' => []]);
    }

    public function attachSkill(Request $request)
    {
        return response()->json(['message' => 'Attach skill - Coming soon']);
    }

    public function updateSkill(Request $request, $id)
    {
        return response()->json(['message' => 'Update skill - Coming soon']);
    }

    public function detachSkill(Request $request, $id)
    {
        return response()->json(['message' => 'Detach skill - Coming soon']);
    }

    public function experiences(Request $request)
    {
        return response()->json(['experiences' => []]);
    }

    public function createExperience(Request $request)
    {
        return response()->json(['message' => 'Create experience - Coming soon']);
    }

    public function updateExperience(Request $request, $id)
    {
        return response()->json(['message' => 'Update experience - Coming soon']);
    }

    public function deleteExperience(Request $request, $id)
    {
        return response()->json(['message' => 'Delete experience - Coming soon']);
    }

    public function education(Request $request)
    {
        return response()->json(['education' => []]);
    }

    public function createEducation(Request $request)
    {
        return response()->json(['message' => 'Create education - Coming soon']);
    }

    public function updateEducation(Request $request, $id)
    {
        return response()->json(['message' => 'Update education - Coming soon']);
    }

    public function deleteEducation(Request $request, $id)
    {
        return response()->json(['message' => 'Delete education - Coming soon']);
    }

    public function applications(Request $request)
    {
        return response()->json(['applications' => []]);
    }

    public function showApplication(Request $request, $id)
    {
        return response()->json(['application' => null]);
    }

    public function dashboard(Request $request)
    {
        return response()->json([
            'stats' => [
                'applications' => 0,
                'views' => 0,
                'messages' => 0,
            ]
        ]);
    }
}