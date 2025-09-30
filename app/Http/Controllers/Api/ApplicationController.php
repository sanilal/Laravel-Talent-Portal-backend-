<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['message' => 'Apply to project - Coming soon']);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['application' => null]);
    }

    public function withdraw(Request $request, $id)
    {
        return response()->json(['message' => 'Withdraw application - Coming soon']);
    }

    public function updateStatus(Request $request, $id)
    {
        return response()->json(['message' => 'Update status - Coming soon']);
    }

    public function addNotes(Request $request, $id)
    {
        return response()->json(['message' => 'Add notes - Coming soon']);
    }
}