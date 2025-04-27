<?php

namespace Database\Seeders;

use App\Models\Instrument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InstrumentUserSeeder extends Seeder
{
    /**
     * Seed 50+ faculty users and link them to an instrument.
     */
    public function run(): void
    {
        // Create at least one instrument if none exists
        if (Instrument::count() === 0) {
            $instrument = Instrument::create([
                'name' => 'Sample Instrument for Testing',
                'order' => 1,
                'google_drive_folder_id' => json_encode(['folderId' => 'sample_folder_id_123']),
            ]);
        } else {
            $instrument = Instrument::first();
        }

        // Clean up existing users except admin before seeding
        User::where('role', '!=', 'admin')->delete();
        
        // Create 50 faculty users
        $users = [];
        $departments = ['Math', 'Science', 'History', 'English', 'Computer Science', 'Psychology', 'Philosophy', 'Economics'];
        
        for ($i = 1; $i <= 50; $i++) {
            $department = $departments[array_rand($departments)];
            $user = User::create([
                'name' => "Faculty Member $i - $department",
                'email' => "faculty$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
            
            $users[] = $user;
        }
        
        // Assign all users to the instrument with different statuses
        foreach ($users as $index => $user) {
            // 60% of users have uploaded documents, 40% pending
            $hasUploaded = rand(1, 10) <= 6;
            
            if ($hasUploaded) {
                // Random upload date between 1 and 20 days ago
                $uploadedAt = Carbon::now()->subDays(rand(1, 20))->subHours(rand(1, 23));
                
                $instrument->users()->attach($user->id, [
                    'google_drive_file_id' => 'file_id_' . Str::random(20),
                    'file_name' => 'submission_' . $user->id . '.pdf',
                    'file_type' => 'application/pdf',
                    'file_size' => rand(100000, 5000000), // 100KB to 5MB
                    'uploaded_at' => $uploadedAt,
                    'created_at' => $uploadedAt,
                    'updated_at' => $uploadedAt,
                ]);
            } else {
                // Never uploaded, just assigned
                $assignedAt = Carbon::now()->subDays(rand(5, 30));
                
                $instrument->users()->attach($user->id, [
                    'created_at' => $assignedAt,
                    'updated_at' => $assignedAt,
                ]);
            }
        }
        
        $this->command->info('Created 50 faculty users and linked them to instrument: ' . $instrument->name);
        $this->command->info('Upload status: ' . count(array_filter($users, function($index) { return $index <= 30; })) . ' uploaded, ' . 
                            count(array_filter($users, function($index) { return $index > 30; })) . ' pending');
    }
} 