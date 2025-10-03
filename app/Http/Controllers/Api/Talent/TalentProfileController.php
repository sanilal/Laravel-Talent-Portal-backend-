<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TalentProfileController extends Controller
{
    /**
     * Get talent profile
     */
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'talentProfile.primaryCategory',
            'talentProfile.skills.skill',
            'experiences',
            'education',
            'portfolios',
        ]);

        return response()->json([
            'profile' => $user,
        ]);
    }

    /**
     * Get talent dashboard stats
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'applications' => $user->applications()->count(),
            'views' => $user->talentProfile->profile_views ?? 0,
            'messages' => $user->receivedMessages()->whereNull('read_at')->count(),
            'profile_completion' => $user->talentProfile->profile_completion_percentage ?? 0,
            'active_projects' => $user->applications()->whereIn('status', ['accepted', 'in_progress'])->count(),
            'reviews' => $user->receivedReviews()->count(),
            'average_rating' => $user->talentProfile->average_rating ?? 0,
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    /**
     * Update talent profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $talentProfile = $user->talentProfile;

        $validator = Validator::make($request->all(), [
            'professional_title' => 'sometimes|nullable|string|max:255',
            'summary' => 'sometimes|nullable|string|max:2000',
            'experience_level' => 'sometimes|nullable|in:entry,intermediate,expert,senior',
            'hourly_rate_min' => 'sometimes|nullable|numeric|min:0',
            'hourly_rate_max' => 'sometimes|nullable|numeric|min:0|gte:hourly_rate_min',
            'currency' => 'sometimes|nullable|string|size:3',
            'is_available' => 'sometimes|boolean',
            'notice_period' => 'sometimes|nullable|integer|min:0',
            'availability_types' => 'sometimes|nullable|array',
            'work_preferences' => 'sometimes|nullable|array',
            'preferred_locations' => 'sometimes|nullable|array',
            'languages' => 'sometimes|nullable|array',
            'is_public' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update talent profile
        $talentProfile->update($request->only([
            'professional_title',
            'summary',
            'experience_level',
            'hourly_rate_min',
            'hourly_rate_max',
            'currency',
            'is_available',
            'notice_period',
            'availability_types',
            'work_preferences',
            'preferred_locations',
            'languages',
            'is_public',
        ]));

        // Update availability timestamp
        if ($request->has('is_available')) {
            $talentProfile->update(['availability_updated_at' => now()]);
        }

        // Recalculate profile completion percentage
        $this->updateProfileCompletion($talentProfile);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $user->fresh()->load('talentProfile'),
        ]);
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->talentProfile->avatar_url) {
            Storage::disk('public')->delete($user->talentProfile->avatar_url);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->talentProfile->update([
            'avatar_url' => $path,
        ]);

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => Storage::url($path),
        ]);
    }

    /**
     * Calculate and update profile completion percentage
     */
    private function updateProfileCompletion($talentProfile)
    {
        $user = $talentProfile->user;
        $completion = 10; // Base for account creation

        // Basic info (30%)
        if ($user->first_name && $user->last_name) $completion += 5;
        if ($user->phone) $completion += 5;
        if ($user->bio) $completion += 5;
        if ($talentProfile->avatar_url) $completion += 5;
        if ($user->location) $completion += 5;
        if ($user->email_verified_at) $completion += 5;

        // Professional info (30%)
        if ($talentProfile->professional_title) $completion += 10;
        if ($talentProfile->summary) $completion += 10;
        if ($talentProfile->experience_level) $completion += 5;
        if ($talentProfile->hourly_rate_min && $talentProfile->hourly_rate_max) $completion += 5;

        // Skills (15%)
        $skillsCount = $user->skills()->count();
        if ($skillsCount >= 1) $completion += 5;
        if ($skillsCount >= 3) $completion += 5;
        if ($skillsCount >= 5) $completion += 5;

        // Experience (10%)
        if ($user->experiences()->count() >= 1) $completion += 10;

        // Education (10%)
        if ($user->education()->count() >= 1) $completion += 10;

        // Portfolio (5%)
        if ($user->portfolios()->count() >= 1) $completion += 5;

        $talentProfile->update([
            'profile_completion_percentage' => min($completion, 100)
        ]);
    }
}