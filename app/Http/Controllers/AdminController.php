<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    // Helper method to check admin access
    private function checkAdminAccess()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'admin') {
            \Log::warning('Non-admin access attempt', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email
            ]);
            
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        return null; // All good
    }

    public function index()
    {
        // Check admin access
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
        try {
            // Log admin access
            \Log::info('Admin dashboard accessed', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email
            ]);

            // Get total users
            $users = $this->supabase->select('users', 'id', []);
            $totalUsers = is_array($users) ? count($users) : 0;

            // Get all stories for statistics
            $stories = $this->supabase->select('stories', '*', []);
            $totalStories = is_array($stories) ? count($stories) : 0;

            // Calculate pending stories and genre stats
            $pendingStories = 0;
            $genreStats = [];
            $totalReads = 0;
            $topStories = [];

            if (is_array($stories)) {
                foreach ($stories as $story) {
                    // Count reads
                    $reads = isset($story['reads']) ? intval($story['reads']) : 0;
                    $totalReads += $reads;
                    
                    // Count as pending if has 0 reads
                    if ($reads == 0) {
                        $pendingStories++;
                    }
                    
                    // Collect top stories for chart
                    $topStories[] = [
                        'title' => $story['title'] ?? 'Untitled',
                        'reads' => $reads,
                        'author' => $story['author'] ?? 'Unknown'
                    ];
                    
                    // Process genre data
                    $genres = $this->extractGenres($story);
                    
                    // Count genres
                    if (is_array($genres)) {
                        foreach ($genres as $genre) {
                            if (!isset($genreStats[$genre])) {
                                $genreStats[$genre] = 0;
                            }
                            $genreStats[$genre]++;
                        }
                    } else {
                        // If no genre specified
                        if (!isset($genreStats['Unknown'])) {
                            $genreStats['Unknown'] = 0;
                        }
                        $genreStats['Unknown']++;
                    }
                }
            }

            // Sort genres by count and get top stories
            arsort($genreStats);

            // Sort top stories by reads and get top 10
            usort($topStories, function($a, $b) {
                return $b['reads'] - $a['reads'];
            });
            $topStories = array_slice($topStories, 0, 10);

            // Prepare chart data
            $genreLabels = array_keys($genreStats);
            $genreCounts = array_values($genreStats);

            $storyTitles = array_column($topStories, 'title');
            $storyReads = array_column($topStories, 'reads');

            // Get recent activity (last 10 story creations)
            $recentStories = $this->getRecentStories(10);
            
            // Get user growth (last 30 days)
            $userGrowth = $this->getUserGrowth();

            // Get recent comments/reviews
            $recentComments = $this->getRecentComments(5);

            return view('admin.dashboard', compact(
                'totalUsers',
                'totalStories',
                'pendingStories',
                'totalReads',
                'genreStats',
                'genreLabels',
                'genreCounts',
                'topStories',
                'storyTitles',
                'storyReads',
                'recentStories',
                'userGrowth',
                'recentComments'
            ));

        } catch (\Exception $e) {
            \Log::error("Admin dashboard error: " . $e->getMessage());
            
            return view('admin.dashboard', [
                'totalUsers' => 0,
                'totalStories' => 0,
                'pendingStories' => 0,
                'totalReads' => 0,
                'genreStats' => [],
                'topStories' => []
            ]);
        }
    }

    public function users(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        // Get all users from database
        $users = $this->supabase->select('users', '*', []);
        $users = is_array($users) ? $users : [];
        
        // Calculate statistics
        $totalUsers = count($users);
        $adminUsers = array_filter($users, function($user) {
            return ($user['role'] ?? 'user') === 'admin';
        });
        $regularUsers = array_filter($users, function($user) {
            return ($user['role'] ?? 'user') === 'user';
        });
        
        return view('admin.users', compact(
            'users',
            'totalUsers',
            'adminUsers',
            'regularUsers'
        ));
        
    } catch (\Exception $e) {
        \Log::error("Error fetching users: " . $e->getMessage());
        return view('admin.users', [
            'users' => [],
            'totalUsers' => 0,
            'adminUsers' => [],
            'regularUsers' => []
        ]);
    }
}

   public function stories(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        // Get all stories from database
        $stories = $this->supabase->select('stories', '*', []);
        $stories = is_array($stories) ? $stories : [];
        
        // Calculate statistics
        $totalStories = count($stories);
        $totalReads = 0;
        $genreStats = [];
        
        foreach ($stories as $story) {
            $totalReads += intval($story['reads'] ?? 0);
            
            // Process genre data for stats
            $genres = $this->extractGenres($story);
            
            if (is_array($genres)) {
                foreach ($genres as $genre) {
                    if (!isset($genreStats[$genre])) {
                        $genreStats[$genre] = 0;
                    }
                    $genreStats[$genre]++;
                }
            }
        }
        
        return view('admin.stories', compact(
            'stories',
            'totalStories',
            'totalReads',
            'genreStats'
        ));
        
    } catch (\Exception $e) {
        \Log::error("Error fetching stories: " . $e->getMessage());
        return view('admin.stories', [
            'stories' => [],
            'totalStories' => 0,
            'totalReads' => 0,
            'genreStats' => []
        ]);
    }
}

    public function comments()
    {
        // Check admin access
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
        try {
            $comments = $this->supabase->select('comments', '*', []);
            
            return view('admin.comments', [
                'comments' => is_array($comments) ? $comments : []
            ]);
        } catch (\Exception $e) {
            \Log::error("Error fetching comments: " . $e->getMessage());
            return view('admin.comments', ['comments' => []]);
        }
    }

    public function getStats(Request $request)
    {
        // Check admin access
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
        try {
            $stats = [
                'totalUsers' => $this->getTotalUsers(),
                'totalStories' => $this->getTotalStories(),
                'totalReads' => $this->getTotalReads(),
                'pendingStories' => $this->getPendingStories()
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'updated_at' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    public function extractGenres($story)
    {
        if (!isset($story['genre'])) {
            return ['Unknown'];
        }

        if (is_string($story['genre'])) {
            $genre_json = stripslashes($story['genre']);
            $genres = json_decode($genre_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['Unknown'];
            }
            return $genres;
        } else {
            return $story['genre'];
        }
    }

    private function getRecentStories($limit = 10)
    {
        try {
            $stories = $this->supabase->select('stories', '*', [
                'order' => 'created_at.desc',
                'limit' => $limit
            ]);
            
            if (is_array($stories)) {
                return array_map(function($story) {
                    return [
                        'title' => $story['title'] ?? 'Untitled',
                        'author' => $story['author'] ?? 'Unknown',
                        'reads' => $story['reads'] ?? 0,
                        'created_at' => $story['created_at'] ?? now()->toISOString(),
                        'is_nsfw' => $story['is_nsfw'] ?? false
                    ];
                }, $stories);
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching recent stories: " . $e->getMessage());
        }
        
        return [];
    }

    private function getUserGrowth()
    {
        try {
            // Get users created in last 30 days
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $recentUsers = $this->supabase->select('users', '*', [
                'created_at' => 'gte.' . $thirtyDaysAgo,
                'order' => 'created_at.desc'
            ]);
            
            // Get total users before 30 days
            $oldUsers = $this->supabase->select('users', '*', [
                'created_at' => 'lt.' . $thirtyDaysAgo
            ]);
            
            $recentCount = is_array($recentUsers) ? count($recentUsers) : 0;
            $oldCount = is_array($oldUsers) ? count($oldUsers) : 0;
            
            $growthPercentage = $oldCount > 0 ? ($recentCount / $oldCount) * 100 : 0;
            
            return [
                'recent' => $recentCount,
                'total' => $recentCount + $oldCount,
                'growth' => round($growthPercentage, 2)
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error calculating user growth: " . $e->getMessage());
            return ['recent' => 0, 'total' => 0, 'growth' => 0];
        }
    }

    private function getRecentComments($limit = 5)
    {
        try {
            $comments = $this->supabase->select('comments', '*', [
                'order' => 'created_at.desc',
                'limit' => $limit
            ]);
            
            if (is_array($comments)) {
                return array_map(function($comment) {
                    return [
                        'user_id' => $comment['user_id'] ?? null,
                        'story_id' => $comment['story_id'] ?? null,
                        'comment' => $comment['comment'] ?? '',
                        'rating' => $comment['rating'] ?? 0,
                        'created_at' => $comment['created_at'] ?? now()->toISOString()
                    ];
                }, $comments);
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching recent comments: " . $e->getMessage());
        }
        
        return [];
    }

    private function getTotalUsers()
    {
        $users = $this->supabase->select('users', 'id', []);
        return is_array($users) ? count($users) : 0;
    }

    private function getTotalStories()
    {
        $stories = $this->supabase->select('stories', 'id', []);
        return is_array($stories) ? count($stories) : 0;
    }

    private function getTotalReads()
    {
        $stories = $this->supabase->select('stories', 'reads', []);
        $total = 0;
        
        if (is_array($stories)) {
            foreach ($stories as $story) {
                $total += intval($story['reads'] ?? 0);
            }
        }
        
        return $total;
    }

    private function getPendingStories()
    {
        $stories = $this->supabase->select('stories', '*', []);
        $pending = 0;
        
        if (is_array($stories)) {
            foreach ($stories as $story) {
                if (intval($story['reads'] ?? 0) == 0) {
                    $pending++;
                }
            }
        }
        
        return $pending;
    }

    public function getGenreColor($genre)
    {
        $colors = [
            'Fantasy' => '#36A2EB',
            'Thriller' => '#FF6384', 
            'Horror' => '#9966FF',
            'Mystery' => '#4BC0C0',
            'Action' => '#FF9F40',
            'Sci-Fi' => '#FFCE56',
            'Romance' => '#FF6384',
            'Comedy' => '#C9CBCF',
            'Drama' => '#4BC0C0',
            'Adventure' => '#36A2EB',
            'Historical' => '#9966FF',
            'Unknown' => '#666666'
        ];
        return $colors[$genre] ?? '#36A2EB';
    }

    // Add after the comments() method in your AdminController:

// ADD THIS TO YOUR SupabaseService.php - REPLACE THE EXISTING delete() METHOD

public function delete($table, $filters)
{
    try {
        $url = "{$this->url}/rest/v1/{$table}";
        
        // Build query string manually for DELETE requests
        $queryParts = [];
        foreach ($filters as $key => $value) {
            $queryParts[] = urlencode($key) . '=eq.' . urlencode($value);
        }
        
        if (!empty($queryParts)) {
            $url .= '?' . implode('&', $queryParts);
        }

        Log::info("🗑️ Supabase Delete", [
            'table' => $table,
            'url' => $url,
            'filters' => $filters
        ]);

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
        ])->timeout(30)->delete($url);

        Log::info("📡 Delete Response", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            Log::info("✅ Supabase Delete Successful", [
                'table' => $table
            ]);
            return true;
        }

        throw new \Exception("HTTP {$response->status()}: {$response->body()}");

    } catch (\Exception $e) {
        Log::error('💥 Supabase Delete Error', [
            'table' => $table,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

public function updateStory(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        $storyId = $request->input('story_id');
        $title = trim($request->input('title', ''));
        $author = trim($request->input('author', ''));
        $description = trim($request->input('description', ''));
        
        \Log::info('Update story request', [
            'story_id' => $storyId,
            'title' => $title,
            'author' => $author
        ]);
        
        if (!$storyId || !$title || !$author) {
            return redirect()->route('admin.stories')->with('error', 'Title and author are required.');
        }
        
        $updateData = [
            'title' => $title,
            'author' => $author,
            'description' => $description
        ];
        
        // Correct order: update($table, $filters, $data)
        $result = $this->supabase->update('stories', ['id' => $storyId], $updateData);
        
        if ($result) {
            \Log::info('Story updated successfully', ['story_id' => $storyId]);
            return redirect()->route('admin.stories')->with('message', 'Story updated successfully.');
        } else {
            return redirect()->route('admin.stories')->with('error', 'Failed to update story.');
        }
        
    } catch (\Exception $e) {
        \Log::error("Error updating story: " . $e->getMessage());
        return redirect()->route('admin.stories')->with('error', 'Database error: ' . $e->getMessage());
    }
}

public function deleteStory(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        $storyId = $request->input('story_id');
        
        \Log::info('Delete story request', [
            'story_id' => $storyId,
            'request_all' => $request->all()
        ]);
        
        if (!$storyId) {
            return redirect()->route('admin.stories')->with('error', 'Story ID is required.');
        }
        
        // Call delete with proper filter
        $result = $this->supabase->delete('stories', ['id' => $storyId]);
        
        if ($result) {
            \Log::info('Story deleted successfully', ['story_id' => $storyId]);
            return redirect()->route('admin.stories')->with('message', 'Story deleted successfully.');
        } else {
            return redirect()->route('admin.stories')->with('error', 'Failed to delete story.');
        }
        
    } catch (\Exception $e) {
        \Log::error("Error deleting story", [
            'story_id' => $storyId ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('admin.stories')->with('error', 'Database error: ' . $e->getMessage());
    }
}

public function deleteUser(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return redirect()->route('admin.users')->with('error', 'User ID is required.');
        }
        
        // Don't allow admin to delete themselves
        if ($userId == Auth::user()->supabase_id) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }
        
        $result = $this->supabase->delete('users', ['id' => $userId]);
        
        if ($result) {
            \Log::info('User deleted successfully', ['user_id' => $userId]);
            return redirect()->route('admin.users')->with('message', 'User deleted successfully.');
        } else {
            return redirect()->route('admin.users')->with('error', 'Failed to delete user.');
        }
        
    } catch (\Exception $e) {
        \Log::error("Error deleting user: " . $e->getMessage());
        return redirect()->route('admin.users')->with('error', 'Database error: ' . $e->getMessage());
    }
}

public function updateRole(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        $userId = $request->input('user_id');
        $newRole = $request->input('role');
        
        if (!$userId || !in_array($newRole, ['user', 'admin'])) {
            return redirect()->route('admin.users')->with('error', 'Invalid request.');
        }
        
        // Don't allow admin to change their own role
        $currentUser = Auth::user();
        if ($userId == $currentUser->supabase_id) {
            return redirect()->route('admin.users')->with('error', 'You cannot change your own role.');
        }
        
        $result = $this->supabase->update('users', ['id' => $userId], ['role' => $newRole]);
        
        if ($result) {
            \Log::info('User role updated', ['user_id' => $userId, 'new_role' => $newRole]);
            return redirect()->route('admin.users')->with('message', 'User role updated successfully.');
        } else {
            return redirect()->route('admin.users')->with('error', 'Failed to update user role.');
        }
        
    } catch (\Exception $e) {
        \Log::error("Error updating user role: " . $e->getMessage());
        return redirect()->route('admin.users')->with('error', 'Database error: ' . $e->getMessage());
    }
}

public function addUser(Request $request)
{
    // Check admin access
    $redirect = $this->checkAdminAccess();
    if ($redirect) return $redirect;
    
    try {
        $firstName = trim($request->input('first_name', ''));
        $lastName = trim($request->input('last_name', ''));
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $role = $request->input('role', 'user');
        
        // Validation
        if (!$firstName || !$lastName || !$email || !$password) {
            return redirect()->route('admin.users')->with('error', 'All fields are required.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('admin.users')->with('error', 'Please enter a valid email address.');
        }
        
        if (strlen($password) < 8) {
            return redirect()->route('admin.users')->with('error', 'Password must be at least 8 characters.');
        }
        
        // Check if email exists
        $existingUsers = $this->supabase->select('users', '*', ['email' => $email]);
        if (!empty($existingUsers)) {
            return redirect()->route('admin.users')->with('error', 'Email is already registered.');
        }
        
        $hashedPassword = Hash::make($password);
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'created_at' => now()->toISOString()
        ];
        
        $result = $this->supabase->insert('users', $userData);
        
        if ($result) {
            return redirect()->route('admin.users')->with('message', 'User created successfully.');
        } else {
            return redirect()->route('admin.users')->with('error', 'Failed to create user.');
        }
        
    } catch (\Exception $e) {
        \Log::error("Error adding user: " . $e->getMessage());
        return redirect()->route('admin.users')->with('error', 'Database error: ' . $e->getMessage());
    }
}

    
}