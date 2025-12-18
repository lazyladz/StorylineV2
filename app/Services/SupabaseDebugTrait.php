<?php

namespace App\Services;

/**
 * Debug helper for Supabase operations
 * Add this to your SupabaseService to help diagnose issues
 */
trait SupabaseDebugTrait
{
    /**
     * Test connection to Supabase
     */
    public function testConnection(): array
    {
        try {
            $result = $this->select('users', 'email', [], 1);
            return [
                'success' => true,
                'message' => 'Connection successful',
                'sample_data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a user exists in Supabase
     */
    public function userExists(string $email): bool
    {
        try {
            $result = $this->select('users', 'email', ['email' => $email]);
            return !empty($result);
        } catch (\Exception $e) {
            \Log::error("Error checking if user exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get detailed user info for debugging
     */
    public function debugUser(string $email): array
    {
        try {
            $result = $this->select('users', '*', ['email' => $email]);
            
            if (empty($result)) {
                return [
                    'exists' => false,
                    'message' => 'User not found in database',
                    'email' => $email
                ];
            }

            return [
                'exists' => true,
                'user_data' => $result[0],
                'columns' => array_keys($result[0])
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Test update operation with detailed logging
     */
    public function debugUpdate(string $table, array $where, array $data): array
    {
        \Log::info("Debug Update Starting", [
            'table' => $table,
            'where' => $where,
            'data' => $data
        ]);

        try {
            // Check if record exists first
            $existing = $this->select($table, '*', $where);
            
            if (empty($existing)) {
                return [
                    'success' => false,
                    'message' => 'Record not found',
                    'where' => $where
                ];
            }

            \Log::info("Existing record found", [
                'record' => $existing[0]
            ]);

            // Attempt update
            $result = $this->update($table, $where, $data);

            \Log::info("Update result", [
                'result' => $result,
                'result_type' => gettype($result)
            ]);

            // Verify update
            $updated = $this->select($table, '*', $where);
            
            return [
                'success' => $result !== false,
                'before' => $existing[0],
                'after' => $updated[0] ?? null,
                'result' => $result
            ];

        } catch (\Exception $e) {
            \Log::error("Debug update error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}

/**
 * Command to test Supabase settings update
 * Run this: php artisan tinker
 * Then: app(\App\Services\SupabaseService::class)->testSettingsUpdate('your-email@example.com')
 */
class SupabaseTestCommand
{
    public static function testSettingsUpdate(string $email)
    {
        $supabase = app(\App\Services\SupabaseService::class);
        
        echo "\n=== Testing Supabase Settings Update ===\n\n";
        
        // Test 1: Connection
        echo "1. Testing connection...\n";
        $connection = $supabase->testConnection();
        echo "   Result: " . ($connection['success'] ? '✓ Success' : '✗ Failed') . "\n";
        if (!$connection['success']) {
            echo "   Error: " . $connection['error'] . "\n";
            return;
        }
        
        // Test 2: User exists
        echo "\n2. Checking if user exists...\n";
        $userInfo = $supabase->debugUser($email);
        echo "   User exists: " . ($userInfo['exists'] ? '✓ Yes' : '✗ No') . "\n";
        
        if ($userInfo['exists']) {
            echo "   Available columns: " . implode(', ', $userInfo['columns']) . "\n";
            echo "   Current show_nsfw: " . ($userInfo['user_data']['show_nsfw'] ?? 'not set') . "\n";
        } else {
            echo "   Error: User not found\n";
            return;
        }
        
        // Test 3: Update operation
        echo "\n3. Testing update operation...\n";
        $updateTest = $supabase->debugUpdate(
            'users',
            ['email' => $email],
            ['show_nsfw' => true]
        );
        
        if ($updateTest['success']) {
            echo "   Update: ✓ Success\n";
            echo "   Before: show_nsfw = " . ($updateTest['before']['show_nsfw'] ?? 'not set') . "\n";
            echo "   After: show_nsfw = " . ($updateTest['after']['show_nsfw'] ?? 'not set') . "\n";
        } else {
            echo "   Update: ✗ Failed\n";
            echo "   Error: " . ($updateTest['error'] ?? 'Unknown error') . "\n";
        }
        
        echo "\n=== Test Complete ===\n\n";
    }
}