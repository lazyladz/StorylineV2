<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get user data from Supabase
            $supabaseUsers = $this->supabase->select('users', '*', ['id' => $user->supabase_id]);
            $supabaseUser = $supabaseUsers[0] ?? null;
            
            $profile_image = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
            
            if ($supabaseUser && isset($supabaseUser['profile_image'])) {
                $profile_image = $supabaseUser['profile_image'];
            }

            // Get user's stories count and total reads
            $stories_result = $this->supabase->select('stories', '*', ['user_id' => $user->supabase_id]);
            $total_stories = 0;
            $total_reads = 0;
            
            if (is_array($stories_result) && !empty($stories_result)) {
                $stories = array_values($stories_result);
                $stories = array_filter($stories, 'is_array');
                $total_stories = count($stories);
                
                foreach ($stories as $story) {
                    $total_reads += $story['reads'] ?? 0;
                }
            }

            // Get user's stories for display
            $userStories = $this->getUserStories($user->supabase_id);

            return view('profile.index', compact(
                'user',
                'supabaseUser',
                'profile_image',
                'total_stories',
                'total_reads',
                'userStories'
            ));

        } catch (\Exception $e) {
            \Log::error("Error fetching profile data: " . $e->getMessage());
            
            return view('profile.index', [
                'user' => Auth::user(),
                'supabaseUser' => null,
                'profile_image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
                'total_stories' => 0,
                'total_reads' => 0,
                'userStories' => []
            ]);
        }
    }

    private function getUserStories($userId)
    {
        try {
            $stories = $this->supabase->select('stories', '*', ['user_id' => $userId]);
            
            return array_map([$this, 'formatStory'], $stories);
        } catch (\Exception $e) {
            \Log::error("Error fetching user stories: " . $e->getMessage());
            return [];
        }
    }

    private function formatStory($story)
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
        if (isset($story['chapters'])) {
            if (is_string($story['chapters'])) {
                $chapters_json = stripslashes($story['chapters']);
                $chapters = json_decode($chapters_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $chapters = [];
                }
            } else {
                $chapters = $story['chapters'];
            }
        }
        
        return [
            'id' => $story['id'] ?? null,
            'title' => $story['title'] ?? 'Untitled',
            'author' => $story['author'] ?? 'Unknown Author',
            'genre' => $genre,
            'cover_image' => $story['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'chapters' => $chapters,
            'reads' => $story['reads'] ?? 0,
            'rating' => $story['rating'] ?? 0,
            'created_at' => $story['created_at'] ?? now()->toISOString()
        ];
    }

    public function update(Request $request)
{
    \Log::info('Profile update started', ['user_id' => Auth::id(), 'data' => $request->all()]);

    $validator = \Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email',
        'bio' => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        \Log::error('Profile validation failed', ['errors' => $validator->errors()->toArray()]);
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ]);
    }

    try {
        $user = Auth::user();
        
        // Check if user has supabase_id
        if (empty($user->supabase_id)) {
            \Log::error('User has no supabase_id', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_data' => $user->toArray()
            ]);
            
            // Try to find user in Supabase by email
            try {
                $supabaseUsers = $this->supabase->select('users', '*', ['email' => $user->email]);
                if (!empty($supabaseUsers)) {
                    $supabaseUser = $supabaseUsers[0];
                    $user->supabase_id = $supabaseUser['id'];
                    $user->save();
                    \Log::info('Fixed missing supabase_id', [
                        'user_id' => $user->id,
                        'new_supabase_id' => $supabaseUser['id']
                    ]);
                } else {
                    throw new \Exception('User not found in Supabase');
                }
            } catch (\Exception $e) {
                \Log::error('Could not fix supabase_id: ' . $e->getMessage());
                throw new \Exception('User account not properly synchronized with Supabase');
            }
        }
        
        \Log::info('Updating profile for user', [
            'user_id' => $user->id, 
            'supabase_id' => $user->supabase_id,
            'email' => $user->email
        ]);
        
        // Update user in Supabase
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'bio' => $request->bio ?? '',
            'updated_at' => now()->toISOString()
        ];

        \Log::info('Attempting Supabase update', [
            'table' => 'users',
            'filters' => ['id' => $user->supabase_id],
            'data' => $updateData
        ]);

        // Try the update
        $result = $this->supabase->update('users', ['id' => $user->supabase_id], $updateData);
        
        \Log::info('Supabase update result', [
            'result' => $result,
            'result_type' => gettype($result),
            'is_array' => is_array($result),
            'is_bool' => is_bool($result)
        ]);

        // Check result - it could be an array or boolean
        if ($result === false || $result === null || (is_array($result) && empty($result))) {
            \Log::warning('Supabase update may have failed, checking if update actually happened');
            
            // Verify the update by fetching the user
            $updatedUser = $this->supabase->select('users', '*', ['id' => $user->supabase_id]);
            
            if (!empty($updatedUser)) {
                $currentData = $updatedUser[0];
                \Log::info('Current user data after update attempt', $currentData);
                
                // Check if data matches what we tried to update
                if ($currentData['first_name'] === $updateData['first_name'] && 
                    $currentData['last_name'] === $updateData['last_name']) {
                    \Log::info('Update appears to have succeeded based on data verification');
                    $result = true; // Mark as success
                }
            }
        }

        if (!$result) {
            throw new \Exception('Supabase update failed. Check logs for details.');
        }

        // Update local user
        $user->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email
        ]);

        \Log::info('Profile updated successfully');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);

    } catch (\Exception $e) {
        \Log::error("Error updating profile: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to update profile. Please try again.'
        ]);
    }
}

    // Helper methods for the view
    public function getGenreColor($genre)
    {
        $colors = [
            'Fantasy' => 'bg-primary',
            'Thriller' => 'bg-success',
            'Horror' => 'bg-warning text-dark',
            'Mystery' => 'bg-info text-dark',
            'Action' => 'bg-danger',
            'Sci-Fi' => 'bg-dark',
            'Romance' => 'bg-pink',
            'Comedy' => 'bg-secondary',
            'Drama' => 'bg-light text-dark',
            'Adventure' => 'bg-success',
            'Historical' => 'bg-info text-dark'
        ];
        return $colors[$genre] ?? 'bg-primary';
    }

    public function formatReads($reads)
    {
        if ($reads >= 1000000) {
            return round($reads / 1000000, 1) . 'M';
        } elseif ($reads >= 1000) {
            return round($reads / 1000, 1) . 'k';
        }
        return $reads;
    }
}