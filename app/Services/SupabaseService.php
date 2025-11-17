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

    public function update($table, $filters, $data)
    {
        try {
            $url = "{$this->url}/rest/v1/{$table}";
            
            $queryParams = [];
            foreach ($filters as $key => $value) {
                $queryParams[$key] = 'eq.' . $value;
            }

            Log::info("✏️ Supabase Update", [
                'table' => $table,
                'filters' => $filters,
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->timeout(30)->patch($url, $data, ['query' => $queryParams]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info("✅ Supabase Update Successful", [
                    'table' => $table,
                    'updated_records' => count($result)
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
            
            $queryParams = [];
            foreach ($filters as $key => $value) {
                $queryParams[$key] = 'eq.' . $value;
            }

            Log::info("🗑️ Supabase Delete", [
                'table' => $table,
                'filters' => $filters
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ])->timeout(30)->delete($url, $queryParams);

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
}