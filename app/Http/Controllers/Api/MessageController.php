<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['messages' => []]);
    }

    public function send(Request $request)
    {
        return response()->json(['message' => 'Send message - Coming soon']);
    }

    public function conversations(Request $request)
    {
        return response()->json(['conversations' => []]);
    }

    public function conversation(Request $request, $userId)
    {
        return response()->json(['conversation' => []]);
    }

    public function markAsRead(Request $request, $id)
    {
        return response()->json(['message' => 'Mark as read - Coming soon']);
    }

    public function delete(Request $request, $id)
    {
        return response()->json(['message' => 'Delete message - Coming soon']);
    }
}
