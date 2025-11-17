<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $supabase;

    public function __construct(\App\Services\SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function getComments($story_id)
    {
        try {
            $story_id = intval($story_id);

            if (!$story_id || $story_id <= 0) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Valid Story ID required'
                ], 400);
            }

            $comments = $this->supabase->select('comments', '*', ['story_id' => $story_id]);

            return response()->json([
                'success' => true, 
                'comments' => $comments
            ]);

        } catch (\Exception $e) {
            \Log::error("Error fetching comments: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Failed to load comments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'story_id' => 'required|integer',
                'comment' => 'required|string|max:1000'
            ]);

            $story_id = intval($validated['story_id']);
            $comment = trim($validated['comment']);

            if (!$story_id || $story_id <= 0 || !$comment) {
                return response()->json([
                    'success' => false,
                    'error' => 'Missing required fields'
                ], 400);
            }

            $comment_data = [
                'story_id' => $story_id,
                'user_id' => $user->id,
                'author' => $user->first_name . ' ' . $user->last_name,
                'comment_text' => $comment,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $result = $this->supabase->insert('comments', $comment_data);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment added successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save comment'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error("Error adding comment: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }
}