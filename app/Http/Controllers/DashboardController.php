<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
        
        \Log::info('Dashboard loading for user', [
            'user_id' => $user->id,
            'supabase_id' => $user->supabase_id,
            'email' => $user->email
        ]);

        // Get user's stories using the SUPABASE ID, not Laravel ID
        $userStories = $this->supabase->select('stories', '*', ['user_id' => $user->supabase_id]);
        
        \Log::info('User stories query', [
            'supabase_id_used' => $user->supabase_id,
            'stories_count' => is_array($userStories) ? count($userStories) : 0
        ]);

        // Get all stories (for genre sections)
        $allStories = $this->supabase->select('stories', '*', []);
        
        \Log::info('Dashboard data fetched', [
            'user_stories_count' => is_array($userStories) ? count($userStories) : 0,
            'all_stories_count' => is_array($allStories) ? count($allStories) : 0
        ]);

        // Get reading progress - also update this to use supabase_id
        $continueStories = $this->getReadingProgress($user);

        // Format stories
        $userStories = is_array($userStories) ? array_map([$this, 'formatStory'], $userStories) : [];
        $allStories = is_array($allStories) ? array_map([$this, 'formatStory'], $allStories) : [];
        $continueStories = is_array($continueStories) ? array_map([$this, 'formatStory'], $continueStories) : [];

        $popularGenres = ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action'];

        return view('dashboard.index', compact(
            'userStories', 
            'allStories', 
            'continueStories', 
            'popularGenres'
        ));

    } catch (\Exception $e) {
        \Log::error("Dashboard error: " . $e->getMessage());
        \Log::error("Dashboard stack trace: " . $e->getTraceAsString());
        
        return view('dashboard.index', [
            'userStories' => [],
            'allStories' => [],
            'continueStories' => [],
            'popularGenres' => ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action']
        ]);
    }
}

private function getReadingProgress($user)
{
    try {
        // Use supabase_id for reading progress too
        $progressRecords = $this->supabase->select('reading_progress', '*', ['user_id' => $user->supabase_id]);
        $continueStories = [];

        if ($progressRecords && is_array($progressRecords)) {
            foreach ($progressRecords as $progress) {
                if (isset($progress['story_id'])) {
                    $story = $this->supabase->select('stories', '*', ['id' => $progress['story_id']]);
                    if ($story && is_array($story) && count($story) > 0) {
                        $story = $story[0];
                        $continueStories[] = array_merge($story, [
                            'current_chapter_index' => $progress['current_chapter_index'] ?? 0,
                            'progress_percentage' => $progress['progress_percentage'] ?? 0,
                            'last_read_at' => $progress['last_read_at'] ?? now()
                        ]);
                    }
                }
            }
            
            // Sort by last_read_at descending
            usort($continueStories, function($a, $b) {
                $timeA = strtotime($a['last_read_at'] ?? '2000-01-01');
                $timeB = strtotime($b['last_read_at'] ?? '2000-01-01');
                return $timeB - $timeA;
            });
            
            return array_slice($continueStories, 0, 6);
        }
    } catch (\Exception $e) {
        \Log::error("Reading progress error: " . $e->getMessage());
    }
    
    return [];
}

    private function formatStory($story)
    {
        if (!is_array($story)) {
            return [
                'id' => null,
                'title' => 'Untitled',
                'author' => 'Unknown Author',
                'genre' => ['Unknown'],
                'cover_image' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'chapters' => [],
                'reads' => 0,
                'rating' => 0,
                'created_at' => now()->toISOString(),
                'current_chapter_index' => 0,
                'progress_percentage' => 0
            ];
        }

        // Handle genre data
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
        
        // Handle chapters data
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
            'created_at' => $story['created_at'] ?? now()->toISOString(),
            'current_chapter_index' => $story['current_chapter_index'] ?? 0,
            'progress_percentage' => $story['progress_percentage'] ?? 0
        ];
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

    public function getStoriesByGenre($stories, $genre)
    {
        $genreStories = [];
        foreach ($stories as $story) {
            if (!empty($story['genre']) && is_array($story['genre']) && in_array($genre, $story['genre'])) {
                $genreStories[] = $story;
            }
        }
        return $genreStories;
    }
}