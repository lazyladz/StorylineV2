<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            // Find user in Supabase
            $supabaseUsers = $this->supabase->select('users', '*', ['email' => $credentials['email']]);
            
            if (empty($supabaseUsers)) {
                return back()->withErrors([
                    'email' => 'No account found with this email.',
                ])->withInput();
            }

            $supabaseUser = $supabaseUsers[0];

            // Check password
            if (Hash::check($credentials['password'], $supabaseUser['password'])) {
                // Create or update local user
                $user = \App\Models\User::updateOrCreate(
                    ['email' => $credentials['email']],
                    [
                        'name' => $supabaseUser['first_name'] . ' ' . $supabaseUser['last_name'],
                        'password' => $supabaseUser['password'],
                        'supabase_id' => $supabaseUser['id']
                    ]
                );

                Auth::login($user);

                $request->session()->regenerate();

                // Redirect based on role
                if (isset($supabaseUser['role']) && $supabaseUser['role'] === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('dashboard');
            }

            return back()->withErrors([
                'password' => 'Invalid password.',
            ])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Login service temporarily unavailable.',
            ])->withInput();
        }
    }

    public function register(Request $request)
    {
        \Log::info('=== REGISTRATION STARTED ===', ['email' => $request->email]);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'terms_agreement' => 'required|accepted',
        ], [
            'terms_agreement.required' => 'You must agree to the terms and conditions.',
            'terms_agreement.accepted' => 'You must agree to the terms and conditions.',
        ]);

        if ($validator->fails()) {
            \Log::error('VALIDATION FAILED', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        try {
            \Log::info('Step 1: Checking Supabase for existing user');

            // Check if email exists in Supabase
            $existingUsers = $this->supabase->select('users', '*', ['email' => $request->email]);
            
            \Log::info('Supabase check result', [
                'existing_users_count' => is_array($existingUsers) ? count($existingUsers) : 0,
            ]);

            if (!empty($existingUsers)) {
                \Log::warning('Email already exists in Supabase', ['email' => $request->email]);
                return back()->withErrors([
                    'email' => 'This email is already registered.',
                ])->withInput();
            }

            \Log::info('Step 2: Creating user in Supabase');

            // Create user in Supabase - ONLY created_at for new records
            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'created_at' => now()->toISOString()
                // updated_at removed - only needed for updates
            ];

            \Log::info('Data being sent to Supabase', $userData);

            $supabaseResult = $this->supabase->insert('users', $userData);
            
            \Log::info('Supabase insert response', ['response' => $supabaseResult]);

            if (empty($supabaseResult)) {
                throw new \Exception('Supabase insert returned empty result - user may not have been created');
            }

            \Log::info('Step 3: Retrieving created user from Supabase');

            // Get the created user from Supabase
            $newUser = $this->supabase->select('users', '*', ['email' => $request->email]);
            
            \Log::info('Retrieved user from Supabase', [
                'user_count' => is_array($newUser) ? count($newUser) : 0,
            ]);

            if (empty($newUser)) {
                throw new \Exception('Could not retrieve created user from Supabase - insertion may have failed');
            }

            $supabaseUserId = $newUser[0]['id'];
            \Log::info('Supabase user created successfully', ['supabase_id' => $supabaseUserId]);

            \Log::info('Step 4: Creating local user');

            // Create local user
            $user = \App\Models\User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'supabase_id' => $supabaseUserId
            ]);

            \Log::info('Local user created', ['user_id' => $user->id]);

            Auth::login($user);
            $request->session()->regenerate();

            \Log::info('=== REGISTRATION SUCCESSFUL ===', ['user_id' => $user->id]);

            return redirect()->route('dashboard')->with('success', 'Account created successfully! Welcome to Storyline!');

        } catch (\Exception $e) {
            \Log::error('REGISTRATION FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'email' => 'Registration failed: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}