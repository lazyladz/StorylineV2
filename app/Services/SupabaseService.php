<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    private $url;
    private $key;
    
    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->key = config('supabase.key');
        
        if (empty($this->url) || empty($this->key)) {
            throw new \Exception('Supabase configuration is missing. Check your .env file.');
        }
    }

    public function select($table, $columns = '*', $filters = [], $limit = null, $orderBy = null)
    {
        try {
            $url = "{$this->url}/rest/v1/{$table}";
            
            $queryParams = [];
            
            // Select specific columns
            if ($columns !== '*') {
                $queryParams['select'] = $columns;
            }
            
            // Add filters
            foreach ($filters as $key => $value) {
                $queryParams[$key] = 'eq.' . $value;
            }
            
            // Add limit
            if ($limit) {
                $queryParams['limit'] = $limit;
            }
            
            // Add order by
            if ($orderBy) {
                $queryParams['order'] = $orderBy;
            }

            Log::info("🔍 Supabase Query", [
                'table' => $table,
                'url' => $url,
                'query_params' => $queryParams
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->timeout(30)->get($url, $queryParams);

            Log::info("📡 Supabase Response", [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("✅ Supabase Query Successful", [
                    'table' => $table,
                    'records_found' => is_array($data) ? count($data) : 0
                ]);
                return $data;
            }
            
            $errorBody = $response->body();
            $statusCode = $response->status();
            
            switch ($statusCode) {
                case 404:
                    throw new \Exception("Table '{$table}' not found (404). Check if table exists and RLS policies allow access.");
                case 401:
                    throw new \Exception("Authentication failed (401). Check your Supabase key.");
                case 406:
                    throw new \Exception("API not acceptable (406). Check query format.");
                default:
                    throw new \Exception("HTTP {$statusCode}: {$errorBody}");
            }
            
        } catch (\Exception $e) {
            Log::error('💥 Supabase Error', [
                'table' => $table,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function insert($table, $data)
    {
        try {
            $url = "{$this->url}/rest/v1/{$table}";

            Log::info("📝 Supabase Insert", [
                'table' => $table,
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->timeout(30)->post($url, $data);

            if ($response->successful()) {
                $result = $response->json();
                Log::info("✅ Supabase Insert Successful", [
                    'table' => $table,
                    'inserted_records' => count($result)
                ]);
                return $result;
            }

            throw new \Exception("HTTP {$response->status()}: {$response->body()}");

        } catch (\Exception $e) {
            Log::error('💥 Supabase Insert Error', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update records in Supabase
     * FIXED: Now properly sends query parameters in the URL
     */
    public function update($table, $filters, $data)
{
    try {
        $url = "{$this->url}/rest/v1/{$table}";
        
        // Build query string manually
        $queryParts = [];
        foreach ($filters as $key => $value) {
            $queryParts[] = urlencode($key) . '=eq.' . urlencode($value);
        }
        
        if (!empty($queryParts)) {
            $url .= '?' . implode('&', $queryParts);
        }

        Log::info("✏️ Supabase Update", [
            'table' => $table,
            'url' => $url,
            'data' => $data
        ]);

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->timeout(30)->patch($url, $data);

        Log::info("📡 Update Response", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            $result = $response->json();
            Log::info("✅ Supabase Update Successful", [
                'table' => $table,
                'updated_records' => is_array($result) ? count($result) : 0
            ]);
            return $result;
        }

        throw new \Exception("HTTP {$response->status()}: {$response->body()}");

    } catch (\Exception $e) {
        Log::error('💥 Supabase Update Error', [
            'table' => $table,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    public function delete($table, $filters)
{
    try {
        $url = "{$this->url}/rest/v1/{$table}";
        
        // Build query string manually - DELETE needs params in URL
        $queryParts = [];
        foreach ($filters as $key => $value) {
            // URL encode both key and value
            $queryParts[] = urlencode($key) . '=eq.' . urlencode($value);
        }
        
        // Append query string to URL
        if (!empty($queryParts)) {
            $url .= '?' . implode('&', $queryParts);
        } else {
            throw new \Exception("DELETE requires a WHERE clause - no filters provided");
        }

        Log::info("🗑️ Supabase Delete", [
            'table' => $table,
            'filters' => $filters,
            'final_url' => $url
        ]);

        // Send DELETE request with empty body
        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ])->timeout(30)->delete($url);

        Log::info("📡 Delete Response", [
            'status' => $response->status(),
            'body' => $response->body(),
            'successful' => $response->successful()
        ]);

        if ($response->successful()) {
            Log::info("✅ Supabase Delete Successful", [
                'table' => $table,
                'filters' => $filters
            ]);
            return true;
        }

        throw new \Exception("HTTP {$response->status()}: {$response->body()}");

    } catch (\Exception $e) {
        Log::error('💥 Supabase Delete Error', [
            'table' => $table,
            'filters' => $filters,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    public function testConnection()
    {
        try {
            // Test by fetching a small amount of data from a common table
            $result = $this->select('users', 'id', [], 1);
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Supabase',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function testUsersTable()
    {
        try {
            $users = $this->select('users', 'id,email,first_name,last_name', [], 5);
            return [
                'success' => true,
                'message' => 'Users table accessible',
                'user_count' => count($users),
                'users' => $users
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ============================================
    // DEBUG HELPERS
    // ============================================

    /**
     * Check if a user exists in Supabase
     */
    public function userExists(string $email): bool
    {
        try {
            $result = $this->select('users', 'email', ['email' => $email]);
            return !empty($result);
        } catch (\Exception $e) {
            Log::error("Error checking if user exists: " . $e->getMessage());
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
                'columns' => array_keys($result[0]),
                'show_nsfw_value' => $result[0]['show_nsfw'] ?? 'column not found'
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
        Log::info("🔧 Debug Update Starting", [
            'table' => $table,
            'where' => $where,
            'data' => $data
        ]);

        try {
            // Step 1: Check if record exists
            $existing = $this->select($table, '*', $where);
            
            if (empty($existing)) {
                return [
                    'success' => false,
                    'step' => 'check_exists',
                    'message' => 'Record not found with given filters',
                    'where' => $where
                ];
            }

            Log::info("✓ Step 1: Record found", [
                'record' => $existing[0]
            ]);

            // Step 2: Attempt update
            $result = $this->update($table, $where, $data);

            Log::info("✓ Step 2: Update executed", [
                'result' => $result,
                'result_type' => gettype($result),
                'is_array' => is_array($result),
                'count' => is_array($result) ? count($result) : 0
            ]);

            // Step 3: Verify update
            $updated = $this->select($table, '*', $where);
            
            Log::info("✓ Step 3: Verification complete", [
                'updated_record' => $updated[0] ?? null
            ]);

            return [
                'success' => true,
                'before' => $existing[0],
                'after' => $updated[0] ?? null,
                'changes' => $this->getChanges($existing[0], $updated[0] ?? []),
                'update_result' => $result
            ];

        } catch (\Exception $e) {
            Log::error("❌ Debug update error", [
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

    /**
     * Compare before/after to show what changed
     */
    private function getChanges(array $before, array $after): array
    {
        $changes = [];
        foreach ($before as $key => $beforeValue) {
            $afterValue = $after[$key] ?? null;
            if ($beforeValue !== $afterValue) {
                $changes[$key] = [
                    'before' => $beforeValue,
                    'after' => $afterValue
                ];
            }
        }
        return $changes;
    }

    /**
     * Quick test to verify settings update works
     */
    public function testSettingsUpdate(string $email): array
    {
        echo "\n=== Testing Supabase Settings Update ===\n\n";
        
        // Test 1: Connection
        echo "1. Testing connection...\n";
        $connection = $this->testConnection();
        echo "   Result: " . ($connection['success'] ? '✓ Success' : '✗ Failed') . "\n";
        if (!$connection['success']) {
            echo "   Error: " . $connection['error'] . "\n";
            return $connection;
        }
        
        // Test 2: User exists
        echo "\n2. Checking if user exists...\n";
        $userInfo = $this->debugUser($email);
        echo "   User exists: " . ($userInfo['exists'] ? '✓ Yes' : '✗ No') . "\n";
        
        if ($userInfo['exists']) {
            echo "   Available columns: " . implode(', ', $userInfo['columns']) . "\n";
            echo "   Current show_nsfw: " . json_encode($userInfo['show_nsfw_value']) . "\n";
        } else {
            echo "   Error: " . ($userInfo['error'] ?? 'User not found') . "\n";
            return $userInfo;
        }
        
        // Test 3: Update operation
        echo "\n3. Testing update operation...\n";
        $currentValue = $userInfo['user_data']['show_nsfw'] ?? false;
        $newValue = !$currentValue; // Toggle it
        
        echo "   Changing show_nsfw from " . json_encode($currentValue) . " to " . json_encode($newValue) . "\n";
        
        $updateTest = $this->debugUpdate(
            'users',
            ['email' => $email],
            ['show_nsfw' => $newValue]
        );
        
        if ($updateTest['success']) {
            echo "   Update: ✓ Success\n";
            if (!empty($updateTest['changes'])) {
                echo "   Changes made:\n";
                foreach ($updateTest['changes'] as $field => $change) {
                    echo "     - {$field}: " . json_encode($change['before']) . " → " . json_encode($change['after']) . "\n";
                }
            } else {
                echo "   Warning: No changes detected (value might already be {$newValue})\n";
            }
        } else {
            echo "   Update: ✗ Failed\n";
            echo "   Error: " . ($updateTest['error'] ?? 'Unknown error') . "\n";
        }
        
        echo "\n=== Test Complete ===\n\n";
        
        return $updateTest;
    }
}