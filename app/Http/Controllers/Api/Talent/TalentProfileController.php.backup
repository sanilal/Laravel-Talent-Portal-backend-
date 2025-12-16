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
            'skills.skill', // Direct relationship
            'experiences',
            'education',
            'portfolios',
        ]);

        // Merge user fields with talentProfile for frontend compatibility
        $profile = $user->toArray();
        if ($user->talentProfile) {
            $profile = array_merge($profile, $user->talentProfile->toArray());
        }

        // Add image URLs
        if ($user->avatar) {
            $profile['avatar'] = asset('storage/' . $user->avatar);
        }
        if ($user->cover_image) {
            $profile['cover_image'] = asset('storage/' . $user->cover_image);
        }

        // Add skill image URLs
        if (isset($profile['skills'])) {
            foreach ($profile['skills'] as &$skill) {
                if (isset($skill['pivot']['image_path'])) {
                    $skill['pivot']['image_url'] = asset('storage/' . $skill['pivot']['image_path']);
                }
            }
        }

        return response()->json($profile);
    }

    /**
     * Get talent dashboard stats
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_applications' => $user->applications()->count(),
            'pending_applications' => $user->applications()->where('status', 'pending')->count(),
            'profile_completeness' => $this->calculateProfileCompleteness($user),
            'unread_messages' => $user->receivedMessages()->whereNull('read_at')->count(),
            'applications' => $user->applications()->count(),
            'views' => $user->talentProfile->profile_views ?? $user->profile_views ?? 0,
            'messages' => $user->receivedMessages()->whereNull('read_at')->count(),
            'profile_completion' => $this->calculateProfileCompleteness($user),
            'active_projects' => $user->applications()->whereIn('status', ['accepted', 'in_progress'])->count(),
            'reviews' => $user->receivedReviews()->count() ?? 0,
            'average_rating' => $user->talentProfile->average_rating ?? 0,
        ];

        return response()->json($stats);
    }

    /**
     * Update talent profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $talentProfile = $user->talentProfile;

        $validator = Validator::make($request->all(), [
            // User table fields (new)
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'professional_title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'experience_level' => 'nullable|in:entry,intermediate,senior,expert',
            'availability_status' => 'nullable|in:available,busy,not_available',
            'languages' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Model-specific fields
            'height' => 'nullable|string|max:50',
            'weight' => 'nullable|string|max:50',
            'chest' => 'nullable|string|max:50',
            'waist' => 'nullable|string|max:50',
            'hips' => 'nullable|string|max:50',
            'shoe_size' => 'nullable|string|max:50',
            'hair_color' => 'nullable|string|max:50',
            'eye_color' => 'nullable|string|max:50',
            // TalentProfile fields (existing)
            'summary' => 'nullable|string|max:2000',
            'hourly_rate_min' => 'nullable|numeric|min:0',
            'hourly_rate_max' => 'nullable|numeric|min:0|gte:hourly_rate_min',
            'is_available' => 'boolean',
            'notice_period' => 'nullable|integer|min:0',
            'availability_types' => 'nullable|array',
            'work_preferences' => 'nullable|array',
            'preferred_locations' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($user->cover_image) {
                Storage::disk('public')->delete($user->cover_image);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $user->cover_image = $path;
        }

        // Parse languages if it's JSON string
        if ($request->has('languages')) {
            $languages = $request->languages;
            if (is_string($languages)) {
                $decoded = json_decode($languages, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $user->languages = $decoded;
                } else {
                    $user->languages = array_map('trim', explode(',', $languages));
                }
            }
        }

        // Update user table fields
        $userFields = [
            'first_name', 'last_name', 'professional_title', 'bio', 'phone',
            'city', 'state', 'country', 'hourly_rate', 'currency',
            'experience_level', 'availability_status', 'website',
            'linkedin_url', 'twitter_url', 'instagram_url',
            'height', 'weight', 'chest', 'waist', 'hips',
            'shoe_size', 'hair_color', 'eye_color'
        ];

        foreach ($userFields as $field) {
            if ($request->has($field)) {
                $user->$field = $request->$field;
            }
        }

        $user->save();

        // Update talent profile if exists
        if ($talentProfile) {
            $talentProfileFields = [
                'professional_title', 'summary', 'experience_level',
                'hourly_rate_min', 'hourly_rate_max', 'currency',
                'is_available', 'notice_period', 'availability_types',
                'work_preferences', 'preferred_locations', 'is_public'
            ];

            foreach ($talentProfileFields as $field) {
                if ($request->has($field)) {
                    $talentProfile->$field = $request->$field;
                }
            }

            // Update availability timestamp
            if ($request->has('is_available')) {
                $talentProfile->availability_updated_at = now();
            }

            $talentProfile->save();

            // Recalculate profile completion
            $this->updateProfileCompletion($talentProfile);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()->load([
                'talentProfile',
                'skills.skill',
                'experiences',
                'education',
                'portfolios'
            ]),
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

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        // Also update talentProfile if exists
        if ($user->talentProfile) {
            $user->talentProfile->update(['avatar_url' => $path]);
        }

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Calculate and update profile completion percentage
     */
    private function calculateProfileCompleteness($user)
    {
        $completion = 10; // Base for account creation

        // Basic info (30%)
        if ($user->first_name && $user->last_name) $completion += 5;
        if ($user->phone) $completion += 5;
        if ($user->bio) $completion += 5;
        if ($user->avatar) $completion += 5;
        if ($user->city || $user->location) $completion += 5;
        if ($user->email_verified_at) $completion += 5;

        // Professional info (30%)
        if ($user->professional_title) $completion += 10;
        if ($user->bio) $completion += 10;
        if ($user->experience_level) $completion += 5;
        if ($user->hourly_rate) $completion += 5;

        // Skills (15%)
        $skillsCount = 0;
        if ($user->talentProfile) {
            $skillsCount = $user->talentProfile->skills()->count();
        }
        if ($skillsCount >= 1) $completion += 5;
        if ($skillsCount >= 3) $completion += 5;
        if ($skillsCount >= 5) $completion += 5;

        // Experience (10%)
        if ($user->experiences()->count() >= 1) $completion += 10;

        // Education (10%)
        if ($user->education()->count() >= 1) $completion += 10;

        // Portfolio (5%)
        if ($user->portfolios()->count() >= 1) $completion += 5;

        $percentage = min($completion, 100);

        // Update in both places
        $user->profile_completion = $percentage;
        $user->save();

         if ($user->talentProfile) {
                $user->talentProfile->update(['profile_completion_percentage' => $percentage]);
            }

        return $percentage;
    }

    /**
     * Legacy method - kept for backward compatibility
     */
    private function updateProfileCompletion($talentProfile)
    {
        return $this->calculateProfileCompleteness($talentProfile->user);
    }
}