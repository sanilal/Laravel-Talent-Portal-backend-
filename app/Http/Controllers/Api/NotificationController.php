<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['notifications' => []]);
    }

    public function unread(Request $request)
    {
        return response()->json(['unread' => []]);
    }

    public function markAsRead(Request $request, $id)
    {
        return response()->json(['message' => 'Mark as read - Coming soon']);
    }

    public function markAllAsRead(Request $request)
    {
        return response()->json(['message' => 'Mark all as read - Coming soon']);
    }

    public function delete(Request $request, $id)
    {
        return response()->json(['message' => 'Delete notification - Coming soon']);
    }
}
