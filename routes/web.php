<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryViewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/debug', function () {
    try {
        $dbConnected = DB::connection()->getPdo() ? true : false;
    } catch (Exception $e) {
        $dbConnected = false;
        $dbError = $e->getMessage();
    }

    return response()->json([
        'status' => 'OK',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
        'database_connected' => $dbConnected,
        'database_error' => $dbError ?? null,
        'storage_writable' => is_writable(storage_path()),
        'bootstrap_writable' => is_writable(base_path('bootstrap/cache')),
        'extensions_loaded' => get_loaded_extensions(),
    ]);
});

Route::get('/test', function () {
    return response()->json(['message' => 'Basic route works!']);
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/browse', [BrowseController::class, 'index'])->name('browse');
Route::get('/stories/{id}', [StoryViewController::class, 'show'])->name('stories.show');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/get-story/{id}', [StoryController::class, 'getStory'])->name('get-story');
Route::get('/get-comments/{story_id}', [CommentController::class, 'getComments'])->name('get-comments');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    
    // Update settings (handles both individual and bulk updates)
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    
    // Get current settings (API endpoint)
    Route::get('/settings/get', [SettingsController::class, 'get'])->name('settings.get');
    
    // Reset settings to defaults
    Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    
    // Story routes
    Route::get('/mystories', [StoryController::class, 'myStories'])->name('mystories');
    Route::get('/write', [StoryController::class, 'write'])->name('write');
    Route::get('/write/{id}', [StoryController::class, 'edit'])->name('write.edit');
    Route::post('/save-story', [StoryController::class, 'saveStory'])->name('save-story');
    Route::post('/update-story', [StoryController::class, 'updateStory'])->name('update-story');
    Route::get('/get-my-stories', [StoryController::class, 'getMyStories'])->name('get-my-stories');
    Route::get('/get-all-stories', [StoryController::class, 'getAllStories'])->name('get-all-stories');
    Route::post('/delete-story', [StoryController::class, 'deleteStory'])->name('delete-story');
    
    // Comment routes
    Route::post('/add-comment', [CommentController::class, 'addComment'])->name('add-comment');
});

// Admin Routes with PreventBackHistory middleware
Route::middleware(['auth', PreventBackHistory::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/stats', [AdminController::class, 'getStats'])->name('admin.stats');
    
    // User management routes
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/update-role', [AdminController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/users/add', [AdminController::class, 'addUser'])->name('admin.users.add');
    
    // Story management routes
    Route::get('/stories', [AdminController::class, 'stories'])->name('admin.stories');
    Route::post('/stories/update', [AdminController::class, 'updateStory'])->name('admin.stories.update');
    Route::post('/stories/delete', [AdminController::class, 'deleteStory'])->name('admin.stories.delete');
    
    // Comments management
    Route::get('/comments', [AdminController::class, 'comments'])->name('admin.comments');
});

// Test routes
Route::get('/test-supabase', function () {
    $supabase = new App\Services\SupabaseService();
    $result = $supabase->testConnection();
    return response()->json($result);
});

Route::get('/debug-profile-update', function() {
    try {
        $user = Auth::user();
        $supabase = new \App\Services\SupabaseService();
        
        // Test update - NO updated_at
        $testData = [
            'first_name' => 'Test_' . rand(100, 999)
        ];
        
        $result = $supabase->update('users', ['id' => $user->supabase_id], $testData);
        
        return response()->json([
            'success' => !empty($result),
            'result' => $result,
            'user' => [
                'id' => $user->id,
                'supabase_id' => $user->supabase_id
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware('auth');