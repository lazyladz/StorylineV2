<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

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
            $allStories = $this->supabase->select('stories', '*', []);
            $popularGenres = ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action', 'Drama', 'Adventure', 'Historical'];

            if (!empty($allStories)) {
                $allStories = array_map([$this, 'formatStory'], $allStories);
            } else {
                $allStories = [];
            }

            return view('browse', compact('allStories', 'popularGenres'));

        } catch (\Exception $e) {
            \Log::error("Error in browse page: " . $e->getMessage());
            
            return view('browse', [
                'allStories' => [],
                'popularGenres' => ['Fantasy', 'Romance', 'Mystery', 'Horror', 'Thriller', 'Sci-Fi', 'Comedy', 'Action', 'Drama', 'Adventure', 'Historical']
            ]);
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
            'description' => $story['description'] ?? 'An engaging story waiting to be discovered.',
            'created_at' => $story['created_at'] ?? now()->toISOString()
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