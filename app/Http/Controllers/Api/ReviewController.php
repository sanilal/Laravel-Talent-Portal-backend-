<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['message' => 'Create review - Coming soon']);
    }

    public function userReviews(Request $request, $userId)
    {
        return response()->json(['reviews' => []]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update review - Coming soon']);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['message' => 'Delete review - Coming soon']);
    }
}
