<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['projects' => []]);
    }

    public function search(Request $request)
    {
        return response()->json(['results' => []]);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['project' => null]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Create project - Coming soon']);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update project - Coming soon']);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['message' => 'Delete project - Coming soon']);
    }

    public function publish(Request $request, $id)
    {
        return response()->json(['message' => 'Publish project - Coming soon']);
    }

    public function close(Request $request, $id)
    {
        return response()->json(['message' => 'Close project - Coming soon']);
    }

    public function applications(Request $request, $id)
    {
        return response()->json(['applications' => []]);
    }
}