<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Application;
use App\Models\Project;

class ReviewController extends Controller
{
    /**
     * Get reviews for a specific user
     */
    public function getUserReviews($userId)
    {
        $reviews = Review::where('reviewed_user_id', $userId)
            ->with(['reviewer', 'project'])
            ->orderByDesc('created_at')
            ->get();

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }

    /**
     * Create a new review
     */
    public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'reviewed_user_id' => 'required|uuid|exists:users,id',
                'project_id' => 'nullable|uuid|exists:projects,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cannot review yourself
            if ($request->reviewed_user_id === $request->user()->id) {
                return response()->json([
                    'message' => 'You cannot review yourself',
                ], 400);
            }

            // Check if already reviewed this user for this project
            if ($request->project_id) {
                $existingReview = Review::where('reviewer_id', $request->user()->id)
                    ->where('reviewed_user_id', $request->reviewed_user_id)
                    ->where('project_id', $request->project_id)
                    ->first();

                if ($existingReview) {
                    return response()->json([
                        'message' => 'You have already reviewed this user for this project',
                    ], 409);
                }
            }

            // Verify they worked together on this project
            if ($request->project_id) {
                $project = Project::find($request->project_id);
                
                // Check if reviewer and reviewed user both participated in this project
                $workedTogether = false;
                
                // Case 1: Recruiter reviewing talent
                if ($project->recruiter_id === $request->user()->id) {
                    $workedTogether = Application::where('project_id', $request->project_id)
                        ->where('talent_id', $request->reviewed_user_id)
                        ->where('status', 'accepted')
                        ->exists();
                }
                
                // Case 2: Talent reviewing recruiter
                elseif ($project && $project->recruiter_id === $request->reviewed_user_id) {
                    $workedTogether = Application::where('project_id', $request->project_id)
                        ->where('talent_id', $request->user()->id)
                        ->where('status', 'accepted')
                        ->exists();
                }
                
                if (!$workedTogether) {
                    return response()->json([
                        'message' => 'You can only review users you have worked with on this project',
                    ], 403);
                }
            }

            $review = Review::create(array_merge(
                $request->only(['reviewed_user_id', 'project_id', 'rating', 'comment']),
                ['reviewer_id' => $request->user()->id]
            ));

            // Update user's average rating
            $this->updateUserRating($request->reviewed_user_id);

            // TODO: Send notification to reviewed user

            return response()->json([
                'message' => 'Review submitted successfully',
                'review' => $review->load('reviewer'),
            ], 201);
        }

    /**
     * Get single review
     */
    public function show($id)
    {
        $review = Review::with(['reviewer', 'reviewedUser', 'project'])
            ->find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        return response()->json([
            'review' => $review,
        ]);
    }

    /**
     * Update a review (only by reviewer)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        // Only reviewer can update their review
        if ($review->reviewer_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this review',
            ], 403);
        }

        $review->update($request->only(['rating', 'comment']));

        // Update user's average rating
        $this->updateUserRating($review->reviewed_user_id);

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->fresh()->load('reviewer'),
        ]);
    }

    /**
     * Delete a review (only by reviewer)
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        // Only reviewer can delete their review
        if ($review->reviewer_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        $reviewedUserId = $review->reviewed_user_id;
        $review->delete();

        // Update user's average rating
        $this->updateUserRating($reviewedUserId);

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Update user's average rating
     */
    private function updateUserRating($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return;
        }

        $reviews = Review::where('reviewed_user_id', $userId)->get();
        $averageRating = $reviews->avg('rating') ?? 0;
        $totalRatings = $reviews->count();

        if ($user->user_type === 'talent' && $user->talentProfile) {
            $user->talentProfile->update([
                'average_rating' => round($averageRating, 2),
                'total_ratings' => $totalRatings,
            ]);
        } elseif ($user->user_type === 'recruiter' && $user->recruiterProfile) {
            $user->recruiterProfile->update([
                'average_rating' => round($averageRating, 2),
                'total_ratings' => $totalRatings,
            ]);
        }
    }
}