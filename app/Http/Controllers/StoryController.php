<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    // STORY VIEWING METHODS
    public function show($id)
    {
        return view('stories', ['story_id' => $id]);
    }

    public function getStory($id)
    {
        try {
            \Log::info("Fetching story with ID: " . $id);
            
            $stories = $this->supabase->select('stories', '*', ['id' => $id]);
            
            \Log::info("Raw story data from Supabase", [
                'story_id' => $id,
                'found' => !empty($stories),
                'count' => is_array($stories) ? count($stories) : 0
            ]);

            if (empty($stories)) {
                \Log::error("Story not found for ID: " . $id);
                return response()->json([
                    'success' => false,
                    'error' => 'Story not found'
                ], 404);
            }

            $story = $stories[0];
            $formattedStory = $this->formatStory($story, true); // Load full content
            
            \Log::info("Story formatted successfully", [
                'title' => $formattedStory['title'],
                'chapter_count' => count($formattedStory['chapters'] ?? [])
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $formattedStory
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error fetching story {$id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load story: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getComments($id)
    {
        try {
            \Log::info("Fetching comments for story ID: " . $id);
            
            $comments = $this->supabase->select('comments', '*', ['story_id' => $id], 50, 0, ['created_at' => 'desc']);
            
            \Log::info("Raw comments data", [
                'story_id' => $id,
                'comments_count' => is_array($comments) ? count($comments) : 0
            ]);

            $formattedComments = [];
            
            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    // Get user info for the comment
                    $user = $this->supabase->select('users', 'first_name, last_name', ['id' => $comment['user_id']]);
                    $authorName = 'Unknown User';
                    
                    if (!empty($user)) {
                        $user = $user[0];
                        $authorName = $user['first_name'] . ' ' . $user['last_name'];
                    }
                    
                    $formattedComments[] = [
                        'id' => $comment['id'],
                        'comment_text' => $comment['comment_text'],
                        'author' => $authorName,
                        'created_at' => $comment['created_at'],
                        'user_id' => $comment['user_id']
                    ];
                }
            }
            
            \Log::info("Formatted comments", [
                'count' => count($formattedComments)
            ]);
            
            return response()->json([
                'success' => true,
                'comments' => $formattedComments
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error fetching comments for story {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load comments',
                'comments' => []
            ]);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $request->validate([
                'story_id' => 'required|integer',
                'comment' => 'required|string|max:1000'
            ]);

            \Log::info("Adding comment", [
                'story_id' => $request->story_id,
                'user_id' => auth()->id(),
                'comment_length' => strlen($request->comment)
            ]);

            $commentData = [
                'story_id' => $request->story_id,
                'user_id' => auth()->id(),
                'comment_text' => $request->comment,
                'created_at' => now()->toISOString()
            ];

            $result = $this->supabase->insert('comments', $commentData);
            
            \Log::info("Comment added successfully", [
                'result' => $result
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error adding comment: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myStories()
{
    try {
        $user = Auth::user();
        
        // Get profile image
        $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=667eea&color=fff';
        
        if (!empty($user->supabase_id)) {
            $supabaseUsers = $this->supabase->select('users', '*', ['id' => $user->supabase_id]);
            if (!empty($supabaseUsers)) {
                $supabaseUser = $supabaseUsers[0];
                if (!empty($supabaseUser['profile_image'])) {
                    $profileImage = $supabaseUser['profile_image'];
                }
            }
        }
        
        return view('mystories', compact('profileImage'));
        
    } catch (\Exception $e) {
        \Log::error("Error in myStories: " . $e->getMessage());
        
        return view('mystories', [
            'profileImage' => 'https://ui-avatars.com/api/?name=User&background=667eea&color=fff'
        ]);
    }
}

    public function write()
{
    try {
        $user = Auth::user();
        
        // Get profile image and user data
        $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=667eea&color=fff';
        $authorName = $user->name;
        
        if (!empty($user->supabase_id)) {
            $supabaseUsers = $this->supabase->select('users', '*', ['id' => $user->supabase_id]);
            if (!empty($supabaseUsers)) {
                $supabaseUser = $supabaseUsers[0];
                if (!empty($supabaseUser['profile_image'])) {
                    $profileImage = $supabaseUser['profile_image'];
                }
                // Get full name from Supabase
                if (!empty($supabaseUser['first_name']) && !empty($supabaseUser['last_name'])) {
                    $authorName = $supabaseUser['first_name'] . ' ' . $supabaseUser['last_name'];
                }
            }
        }
        
        return view('write', [
            'isEditMode' => false,
            'existingStory' => null,
            'storyId' => null,
            'profileImage' => $profileImage,
            'authorName' => $authorName
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Error in write: " . $e->getMessage());
        
        return view('write', [
            'isEditMode' => false,
            'existingStory' => null,
            'storyId' => null,
            'profileImage' => 'https://ui-avatars.com/api/?name=User&background=667eea&color=fff',
            'authorName' => auth()->user()->name
        ]);
    }
}
public function edit($id)
{
    try {
        $user = Auth::user();
        
        // Get profile image and user data
        $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=667eea&color=fff';
        $authorName = $user->name;
        
        // Get Supabase user ID
        $supabaseUsers = $this->supabase->select('users', '*', ['email' => $user->email]);
        
        if (empty($supabaseUsers)) {
            return redirect()->route('write')->with('error', 'User not found in Supabase.');
        }
        
        $supabaseUser = $supabaseUsers[0];
        $supabaseUserId = $supabaseUser['id'];
        
        // Get profile image
        if (!empty($supabaseUser['profile_image'])) {
            $profileImage = $supabaseUser['profile_image'];
        }
        
        // Get full name
        if (!empty($supabaseUser['first_name']) && !empty($supabaseUser['last_name'])) {
            $authorName = $supabaseUser['first_name'] . ' ' . $supabaseUser['last_name'];
        }
        
        $storyId = intval($id);
        
        $stories = $this->supabase->select('stories', '*', ['id' => $storyId, 'user_id' => $supabaseUserId]);
        
        if (empty($stories)) {
            return redirect()->route('write')->with('error', 'Story not found or access denied.');
        }

        $existingStory = $stories[0];
        
        if (isset($existingStory['genre']) && is_string($existingStory['genre'])) {
            $existingStory['genre'] = json_decode($existingStory['genre'], true) ?? [];
        }
        
        if (isset($existingStory['chapters']) && is_string($existingStory['chapters'])) {
            $existingStory['chapters'] = json_decode($existingStory['chapters'], true) ?? [];
        }

        return view('write', [
            'isEditMode' => true,
            'existingStory' => $existingStory,
            'storyId' => $storyId,
            'profileImage' => $profileImage,
            'authorName' => $authorName
        ]);

    } catch (\Exception $e) {
        \Log::error("Error loading story for editing: " . $e->getMessage());
        return redirect()->route('write')->with('error', 'Error loading story for editing.');
    }
}

    public function saveStory(Request $request)
{
    try {
        $user = Auth::user();
        
        // Get Supabase user ID
        $supabaseUsers = $this->supabase->select('users', 'id', ['email' => $user->email]);
        
        if (empty($supabaseUsers)) {
            \Log::error('No Supabase user found for email: ' . $user->email);
            return response()->json([
                'success' => false,
                'error' => 'User not found in Supabase'
            ], 404);
        }
        
        $supabaseUserId = $supabaseUsers[0]['id'];
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'genre' => 'required|array',
            'cover_image' => 'required|string',
            'chapters' => 'required|array',
            'is_nsfw' => 'boolean' // ADD THIS
        ]);

        \Log::info("Saving story for Supabase user: " . $supabaseUserId);

        $storyData = [
            'title' => $validated['title'],
            'author' => $validated['author'],
            'description' => $validated['description'] ?? '',
            'genre' => json_encode($validated['genre'], JSON_UNESCAPED_UNICODE),
            'chapters' => json_encode($validated['chapters'], JSON_UNESCAPED_UNICODE),
            'cover_image' => $validated['cover_image'],
            'user_id' => $supabaseUserId,
            'is_nsfw' => $validated['is_nsfw'] ?? false, // ADD THIS
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        $result = $this->supabase->insert('stories', $storyData);

        \Log::info("Story saved successfully: " . $validated['title']);

        return response()->json([
            'success' => true,
            'message' => 'Story published successfully!',
            'data' => $result
        ]);

    } catch (\Exception $e) {
        \Log::error('Error saving story: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Error saving story: ' . $e->getMessage()
        ], 500);
    }
}
    public function updateStory(Request $request)
{
    try {
        $user = Auth::user();
        
        // Get Supabase user ID
        $supabaseUsers = $this->supabase->select('users', 'id', ['email' => $user->email]);
        
        if (empty($supabaseUsers)) {
            return response()->json([
                'success' => false,
                'error' => 'User not found in Supabase'
            ], 404);
        }
        
        $supabaseUserId = $supabaseUsers[0]['id'];
        
        $validated = $request->validate([
            'id' => 'required|integer',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'genre' => 'required|array',
            'cover_image' => 'required|string',
            'chapters' => 'required|array',
            'is_nsfw' => 'boolean' // ADD THIS
        ]);

        $existingStory = $this->supabase->select('stories', 'id', [
            'id' => $validated['id'],
            'user_id' => $supabaseUserId
        ]);

        if (empty($existingStory)) {
            return response()->json([
                'success' => false,
                'error' => 'Story not found or access denied'
            ], 404);
        }

        $storyData = [
            'title' => $validated['title'],
            'author' => $validated['author'],
            'description' => $validated['description'] ?? '',
            'genre' => json_encode($validated['genre'], JSON_UNESCAPED_UNICODE),
            'chapters' => json_encode($validated['chapters'], JSON_UNESCAPED_UNICODE),
            'cover_image' => $validated['cover_image'],
            'is_nsfw' => $validated['is_nsfw'] ?? false, // ADD THIS
            'updated_at' => now()->toISOString()
        ];

        $result = $this->supabase->update('stories', ['id' => $validated['id']], $storyData);

        return response()->json([
            'success' => true,
            'message' => 'Story updated successfully!',
            'data' => $result
        ]);

    } catch (\Exception $e) {
        \Log::error('Error updating story: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Error updating story: ' . $e->getMessage()
        ], 500);
    }
}

    public function getMyStories(Request $request)
{
    try {
        $user = Auth::user();
        
        \Log::info('getMyStories called', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name
        ]);

        // First, get the Supabase user ID for this email
        $supabaseUsers = $this->supabase->select('users', 'id', ['email' => $user->email]);
        
        \Log::info('Supabase users found for email', [
            'email' => $user->email,
            'found_count' => is_array($supabaseUsers) ? count($supabaseUsers) : 0,
            'supabase_users' => $supabaseUsers
        ]);
        
        if (empty($supabaseUsers)) {
            \Log::warning('No Supabase user found for email: ' . $user->email);
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $supabaseUserId = $supabaseUsers[0]['id'];
        
        \Log::info('Found Supabase user ID', [
            'laravel_user_id' => $user->id,
            'supabase_user_id' => $supabaseUserId,
            'email' => $user->email
        ]);

        if ($request->has('id')) {
            $story_id = intval($request->id);
            $stories = $this->supabase->select('stories', '*', ['id' => $story_id]);
            $load_full_content = true;
        } else {
            $stories = $this->supabase->select('stories', '*', ['user_id' => $supabaseUserId]);
            $load_full_content = false;
        }

        \Log::info('Raw stories data from Supabase', [
            'stories_count' => is_array($stories) ? count($stories) : 0,
            'load_full_content' => $load_full_content,
            'query_user_id' => $supabaseUserId
        ]);

        if (empty($stories)) {
            \Log::info('No stories found for user', [
                'supabase_user_id' => $supabaseUserId,
                'email' => $user->email
            ]);
            return response()->json(['success' => true, 'data' => []]);
        }

        $formattedStories = [];
        
        foreach ($stories as $story) {
            $formattedStory = $this->formatStory($story, $load_full_content);
            $formattedStories[] = $formattedStory;
        }
        
        \Log::info('Formatted stories ready', [
            'formatted_count' => count($formattedStories)
        ]);
        
        if ($request->has('id')) {
            return response()->json([
                'success' => true, 
                'data' => $formattedStories[0] ?? null
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'success' => true, 
                'data' => $formattedStories
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

    } catch (\Exception $e) {
        \Log::error('Error in getMyStories: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ], 500);
    }
}

    public function getAllStories(Request $request)
{
    try {
        $user = Auth::user();
        
        // Get user's NSFW preference from Supabase
        $supabaseUsers = $this->supabase->select('users', 'show_nsfw', ['email' => $user->email]);
        $showNsfw = !empty($supabaseUsers) && ($supabaseUsers[0]['show_nsfw'] ?? false);
        
        $stories = $this->supabase->select('stories', '*', []);
        
        \Log::info('getAllStories result', [
            'stories_count' => is_array($stories) ? count($stories) : 0,
            'show_nsfw' => $showNsfw
        ]);

        if (empty($stories)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $formattedStories = [];
        
        foreach ($stories as $story) {
            // Filter out NSFW stories if user has disabled them
            if (!$showNsfw && ($story['is_nsfw'] ?? false)) {
                continue;
            }
            
            $formattedStory = $this->formatStory($story, false);
            $formattedStories[] = $formattedStory;
        }
        
        return response()->json([
            'success' => true, 
            'data' => $formattedStories
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getAllStories: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ], 500);
    }
}

    public function deleteStory(Request $request)
{
    try {
        $user = Auth::user();
        
        // Get Supabase user ID
        $supabaseUsers = $this->supabase->select('users', 'id', ['email' => $user->email]);
        
        if (empty($supabaseUsers)) {
            return response()->json([
                'success' => false,
                'error' => 'User not found in Supabase'
            ], 404);
        }
        
        $supabaseUserId = $supabaseUsers[0]['id'];
        $storyId = $request->input('story_id');
        
        \Log::info('deleteStory called', [
            'email' => $user->email,
            'supabase_user_id' => $supabaseUserId,
            'story_id' => $storyId
        ]);

        $story = $this->supabase->select('stories', 'id', ['id' => $storyId, 'user_id' => $supabaseUserId]);
        
        if (empty($story)) {
            return response()->json([
                'success' => false,
                'error' => 'Story not found or access denied'
            ], 404);
        }

        $result = $this->supabase->delete('stories', ['id' => $storyId]);

        \Log::info('Story deleted successfully', ['story_id' => $storyId]);

        return response()->json([
            'success' => true,
            'message' => 'Story deleted successfully'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in deleteStory: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

    private function formatStory($story, $load_full_content = false)
{
    $genre = [];
    if (isset($story['genre'])) {
        if (is_string($story['genre'])) {
            $genre_json = stripslashes($story['genre']);
            $genre = json_decode($genre_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $genre = ['Unknown'];
            }
        } else {
            $genre = $story['genre'];
        }
    }
    
    $chapters = [];
    $chapter_count = 0;
    $first_chapter_title = 'No chapters yet';
    
    if (isset($story['chapters'])) {
        if (is_string($story['chapters'])) {
            $chapters_json = stripslashes($story['chapters']);
            $chapters_data = json_decode($chapters_json, true, 512, JSON_UNESCAPED_UNICODE);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($chapters_data)) {
                $chapter_count = count($chapters_data);
                
                if ($load_full_content) {
                    $chapters = $chapters_data;
                } else {
                    $chapters = [];
                    if ($chapter_count > 0 && isset($chapters_data[0]['title'])) {
                        $first_chapter_title = $chapters_data[0]['title'];
                    }
                }
            }
        } else {
            $chapters = $story['chapters'];
            $chapter_count = count($chapters);
            if ($chapter_count > 0 && isset($chapters[0]['title'])) {
                $first_chapter_title = $chapters[0]['title'];
            }
        }
    }
    
    $formattedStory = [
        'id' => $story['id'] ?? null,
        'title' => $story['title'] ?? 'Untitled',
        'author' => $story['author'] ?? 'Unknown Author',
        'description' => $story['description'] ?? '',
        'genre' => $genre,
        'cover_image' => $story['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'is_nsfw' => $story['is_nsfw'] ?? false, // ADD THIS
        'created_at' => $story['created_at'] ?? now()->toISOString()
    ];
    
    if ($load_full_content) {
        $formattedStory['chapters'] = $chapters;
    } else {
        $formattedStory['chapter_count'] = $chapter_count;
        $formattedStory['first_chapter_title'] = $first_chapter_title;
        $formattedStory['chapters'] = [];
    }
    
    return $formattedStory;
}
}