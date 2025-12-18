<?php

namespace App\Http\Controllers;

use App\Models\UserSettings;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrowseController extends Controller
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
            $userSettings = [];
            
            // Get user settings if logged in
            if ($user) {
                $settings = new UserSettings($this->supabase, $user->email);
                $userSettings = $settings->all();
            } else {
                // Guest users can't see NSFW content
                $userSettings['show_nsfw'] = false;
            }
            
            // Get all stories
            $allStories = $this->supabase->select('stories', '*', []);
            $popularGenres = [
                'Fantasy', 'Romance', 'Mystery', 'Horror', 
                'Thriller', 'Sci-Fi', 'Comedy', 'Action', 
                'Drama', 'Adventure', 'Historical'
            ];

            // Filter stories based on NSFW preference
            $filteredStories = [];
            if (!empty($allStories)) {
                foreach ($allStories as $story) {
                    $formattedStory = $this->formatStory($story);
                    
                    // Skip NSFW stories if user doesn't want to see them
                    if (($story['is_nsfw'] ?? false) && !($userSettings['show_nsfw'] ?? false)) {
                        continue;
                    }
                    
                    $filteredStories[] = $formattedStory;
                }
            }

            return view('browse', [
                'allStories' => $filteredStories, 
                'popularGenres' => $popularGenres,
                'user_settings' => $userSettings
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in browse page: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('browse', [
                'allStories' => [],
                'popularGenres' => [
                    'Fantasy', 'Romance', 'Mystery', 'Horror', 
                    'Thriller', 'Sci-Fi', 'Comedy', 'Action', 
                    'Drama', 'Adventure', 'Historical'
                ],
                'user_settings' => ['show_nsfw' => false]
            ]);
        }
    }

    private function formatStory($story)
    {
        // Parse genre
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
        
        // Parse chapters
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
            'description' => $story['description'] ?? 'An engaging story waiting to be discovered.',
            'created_at' => $story['created_at'] ?? now()->toISOString(),
            'is_nsfw' => $story['is_nsfw'] ?? false
        ];
    }

    public static function getGenreColor($genre)
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

    public static function formatReads($reads)
    {
        if ($reads >= 1000000) {
            return round($reads / 1000000, 1) . 'M';
        } elseif ($reads >= 1000) {
            return round($reads / 1000, 1) . 'k';
        }
        return $reads;
    }
}