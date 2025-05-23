<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Recruiter',
            'email' => 'recruiter@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::RECRUITER,
        ]);

        User::factory()->create([
            'name' => 'Admin Candidate',
            'email' => 'candidate@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::CANDIDATE,
        ]);
        
        User::factory(8)->create(['role' => UserRole::RECRUITER]);
        User::factory(20)->create(['role' => UserRole::CANDIDATE]);
        
        \App\Models\JobListing::factory(30)->create();
        
        \App\Models\JobApplication::factory(50)->create();
    }
}
