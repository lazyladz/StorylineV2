<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckDatabase extends Command
{
    protected $signature = 'db:check';
    protected $description = 'Check database structure';

    public function handle()
    {
        $this->info('Checking users table structure...');
        
        if (Schema::hasTable('users')) {
            $columns = Schema::getColumnListing('users');
            
            $this->info('Users table columns:');
            foreach ($columns as $column) {
                $this->line("  - $column");
            }
            
            // Check if role column exists
            if (in_array('role', $columns)) {
                $this->info('✓ Role column exists in users table');
                
                // Show some sample data
                $users = \App\Models\User::select('email', 'role')->limit(5)->get();
                $this->info('Sample user data:');
                foreach ($users as $user) {
                    $this->line("  - {$user->email}: role = '{$user->role}'");
                }
            } else {
                $this->error('✗ Role column does NOT exist in users table');
            }
        } else {
            $this->error('Users table does not exist!');
        }
        
        return 0;
    }
}