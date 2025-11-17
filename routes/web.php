<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryViewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BrowseController; // ADD THIS IMPORT
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/browse', [BrowseController::class, 'index'])->name('browse'); // KEEP ONLY THIS ONE
Route::get('/stories/{id}', [StoryViewController::class, 'show'])->name('stories.show');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // Story routes
    Route::get('/mystories', [StoryController::class, 'myStories'])->name('mystories');
    Route::get('/write', [StoryController::class, 'write'])->name('write');
    Route::get('/write/{id}', [StoryController::class, 'edit'])->name('write.edit');
    Route::post('/save-story', [StoryController::class, 'saveStory'])->name('save-story');
    Route::post('/update-story', [StoryController::class, 'updateStory'])->name('update-story');
    Route::get('/get-my-stories', [StoryController::class, 'getMyStories'])->name('get-my-stories');
    Route::get('/get-all-stories', [StoryController::class, 'getAllStories'])->name('get-all-stories');
    Route::get('/get-story/{id}', [StoryController::class, 'getStory'])->name('get-story');
    Route::post('/delete-story', [StoryController::class, 'deleteStory'])->name('delete-story');
    
    // Comment routes
    Route::get('/get-comments/{story_id}', [CommentController::class, 'getComments'])->name('get-comments');
    Route::post('/add-comment', [CommentController::class, 'addComment'])->name('add-comment');
});

// Test routes
Route::get('/test-supabase', function () {
    $supabase = new App\Services\SupabaseService();
    $result = $supabase->testConnection();
    return response()->json($result);
});