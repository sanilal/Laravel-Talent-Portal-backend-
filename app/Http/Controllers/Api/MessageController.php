<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get all messages for authenticated user
     */
    public function index(Request $request)
    {
        $messages = Message::where('sender_id', $request->user()->id)
            ->orWhere('recipient_id', $request->user()->id)
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * Get all conversations
     */
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        // Get unique conversation partners with last message
        // Using created_at instead of MAX(id) to avoid UUID issue
        $conversations = Message::select('messages.*')
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('recipient_id', $userId);
            })
            ->whereIn('created_at', function($query) use ($userId) {
                $query->select(DB::raw('MAX(created_at)'))
                    ->from('messages')
                    ->where(function($q) use ($userId) {
                        $q->where('sender_id', $userId)
                          ->orWhere('recipient_id', $userId);
                    })
                    ->where('deleted_at', null)
                    ->groupBy(DB::raw('CASE 
                        WHEN sender_id = \'' . $userId . '\' THEN recipient_id 
                        ELSE sender_id 
                    END'));
            })
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function($message) use ($userId) {
                $partnerId = $message->sender_id === $userId
                    ? $message->recipient_id
                    : $message->sender_id;

                $partner = $message->sender_id === $userId
                    ? $message->recipient
                    : $message->sender;

                $unreadCount = Message::where('sender_id', $partnerId)
                    ->where('recipient_id', $userId)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'partner' => $partner,
                    'last_message' => $message,
                    'unread_count' => $unreadCount,
                ];
            });

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get conversation with specific user
     */
    public function getConversation(Request $request, $userId)
    {
        $currentUserId = $request->user()->id;

        $messages = Message::where(function($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $userId);
            })
            ->orWhere(function($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $currentUserId);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'conversation' => $messages,
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|uuid|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cannot message yourself
        if ($request->receiver_id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot send a message to yourself',
            ], 400);
        }

        // Check if recipient exists
        $recipient = User::find($request->receiver_id);
        if (!$recipient) {
            return response()->json([
                'message' => 'Recipient not found',
            ], 404);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'message_type' => Message::TYPE_DIRECT,
            'status' => Message::STATUS_SENT,
        ]);

        // TODO: Send notification to recipient

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender', 'receiver']),
        ], 201);
    }

    /**
     * Get single message
     */
    public function show(Request $request, $id)
    {
        $message = Message::where('id', $id)
            ->where(function($query) use ($request) {
                $query->where('sender_id', $request->user()->id)
                      ->orWhere('receiver_id', $request->user()->id);
            })
            ->with(['sender', 'receiver'])
            ->first();

        if (!$message) {
            return response()->json([
                'message' => 'Message not found',
            ], 404);
        }

        // Mark as read if user is receiver
        if ($message->receiver_id === $request->user()->id && !$message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, $id)
    {
        $message = Message::where('id', $id)
            ->where('receiver_id', $request->user()->id)
            ->first();

        if (!$message) {
            return response()->json([
                'message' => 'Message not found',
            ], 404);
        }

        $message->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Message marked as read',
        ]);
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, $id)
    {
        $message = Message::where('id', $id)
            ->where('sender_id', $request->user()->id)
            ->first();

        if (!$message) {
            return response()->json([
                'message' => 'Message not found or you are not authorized to delete it',
            ], 404);
        }

        // Soft delete
        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }
}