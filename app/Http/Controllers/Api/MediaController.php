<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        return response()->json(['message' => 'Upload media - Coming soon']);
    }

    public function delete(Request $request, $id)
    {
        return response()->json(['message' => 'Delete media - Coming soon']);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['media' => null]);
    }
}