<?php

namespace App\Http\Controllers;

use App\Models\UserSettings;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get user settings from Supabase
        $supabaseUsers = $this->supabase->select('users', 'show_nsfw', ['email' => $user->email]);
        $showNsfw = !empty($supabaseUsers) && ($supabaseUsers[0]['show_nsfw'] ?? false);
        
        return view('settings', [
            'show_nsfw' => $showNsfw
        ]);
    }

    public function get()
    {
        try {
            $user = Auth::user();
            
            // Get user settings using UserSettings model
            $settingsModel = new UserSettings($this->supabase, $user->email);
            
            return response()->json([
                'success' => true,
                'settings' => $settingsModel->all()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting settings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve settings'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'show_nsfw' => 'boolean'
            ]);

            // Update using UserSettings model for consistency
            $settingsModel = new UserSettings($this->supabase, $user->email);
            $success = $settingsModel->set('show_nsfw', $validated['show_nsfw'] ?? false);

            if (!$success) {
                throw new \Exception('Failed to update settings');
            }

            \Log::info('Settings updated', [
                'email' => $user->email,
                'show_nsfw' => $validated['show_nsfw'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error updating settings'
            ], 500);
        }
    }
}