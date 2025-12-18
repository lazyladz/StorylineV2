<?php

namespace App\Models;

use App\Services\SupabaseService;

class UserSettings
{
    protected $supabase;
    protected $email;
    protected $settings = [];

    // Define available settings with their defaults
    const AVAILABLE_SETTINGS = [
        'show_nsfw' => false,
        'email_notifications' => true,
        'dark_mode' => false,
        'auto_save' => true,
    ];

    public function __construct(SupabaseService $supabase, string $email)
    {
        $this->supabase = $supabase;
        $this->email = $email;
        $this->loadSettings();
    }

    /**
     * Load settings from Supabase
     */
    protected function loadSettings()
    {
        try {
            $result = $this->supabase->select('users', '*', ['email' => $this->email]);
            
            if (!empty($result)) {
                foreach (self::AVAILABLE_SETTINGS as $key => $default) {
                    // Get value from Supabase
                    $value = $result[0][$key] ?? $default;
                    
                    // Convert Supabase's 1/0 to proper boolean
                    if (is_bool($default)) {
                        $value = (bool) $value; // Converts 1 to true, 0 to false
                    }
                    
                    $this->settings[$key] = $value;
                }
                
                \Log::info("Settings loaded for {$this->email}", [
                    'raw_data' => $result[0],
                    'processed_settings' => $this->settings
                ]);
            } else {
                // No user found, use defaults
                $this->settings = self::AVAILABLE_SETTINGS;
            }
        } catch (\Exception $e) {
            \Log::error("Failed to load settings for {$this->email}: " . $e->getMessage());
            $this->settings = self::AVAILABLE_SETTINGS;
        }
    }

    /**
     * Get a specific setting
     */
    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default ?? self::AVAILABLE_SETTINGS[$key] ?? null;
    }

    /**
     * Get all settings
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * Update a single setting
     */
    public function set(string $key, $value): bool
    {
        if (!array_key_exists($key, self::AVAILABLE_SETTINGS)) {
            throw new \InvalidArgumentException("Invalid setting key: {$key}");
        }

        return $this->update([$key => $value]);
    }

    /**
     * Update multiple settings
     */
    public function update(array $settings): bool
    {
        try {
            // Validate settings
            $validatedSettings = [];
            foreach ($settings as $key => $value) {
                if (!array_key_exists($key, self::AVAILABLE_SETTINGS)) {
                    continue; // Skip invalid keys
                }
                
                // Type validation
                $validatedSettings[$key] = $this->castValue($key, $value);
            }

            if (empty($validatedSettings)) {
                return true; // Nothing to update
            }

            // Check if user exists first
            $existingUser = $this->supabase->select('users', 'email', ['email' => $this->email]);
            
            if (empty($existingUser)) {
                \Log::error("User not found in Supabase: {$this->email}");
                return false;
            }

            // Update in Supabase
            $result = $this->supabase->update(
                'users',
                ['email' => $this->email],
                $validatedSettings
            );

            if ($result === false) {
                \Log::error("Supabase update failed for {$this->email}", [
                    'settings' => $validatedSettings
                ]);
                return false;
            }

            // Update local cache
            foreach ($validatedSettings as $key => $value) {
                $this->settings[$key] = $value;
            }

            \Log::info("Settings updated successfully for {$this->email}", [
                'settings' => $validatedSettings
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Error updating settings for {$this->email}: " . $e->getMessage(), [
                'settings' => $settings,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Cast value to appropriate type based on setting key
     */
    protected function castValue(string $key, $value)
    {
        $default = self::AVAILABLE_SETTINGS[$key];
        
        if (is_bool($default)) {
            // Convert boolean to 1/0 for Supabase
            return $value ? 1 : 0;
        }
        
        if (is_int($default)) {
            return (int) $value;
        }
        
        if (is_float($default)) {
            return (float) $value;
        }
        
        return (string) $value;
    }

    /**
     * Reset all settings to defaults
     */
    public function reset(): bool
    {
        return $this->update(self::AVAILABLE_SETTINGS);
    }

    /**
     * Check if a setting is enabled (for boolean settings)
     */
    public function isEnabled(string $key): bool
    {
        return (bool) $this->get($key, false);
    }
}