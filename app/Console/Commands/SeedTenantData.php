<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\TenantScope;

class SeedTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed-demo {tenant : The tenant ID to seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a tenant database with 50 faculty users and instruments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');

        // Find the tenant
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant {$tenantId} not found");
            return 1;
        }

        // Run the seeding in tenant context
        $tenant->run(function () {
            $this->info('Seeding in tenant context...');
            
            // Create at least one instrument if none exists
            if (Instrument::count() === 0) {
                $instrument = Instrument::create([
                    'name' => 'Sample Instrument for Testing',
                    'order' => 1,
                    'google_drive_folder_id' => json_encode(['folderId' => 'sample_folder_id_123']),
                ]);
                $this->info("Created instrument: {$instrument->name}");
            } else {
                $instrument = Instrument::first();
                $this->info("Using existing instrument: {$instrument->name}");
            }

            // Clean up existing users except admin before seeding
            $deletedCount = User::where('role', '!=', 'admin')->delete();
            $this->info("Deleted {$deletedCount} existing non-admin users");
            
            // Create 50 faculty users
            $users = [];
            $departments = ['Math', 'Science', 'History', 'English', 'Computer Science', 'Psychology', 'Philosophy', 'Economics'];
            
            $this->info('Creating 50 faculty users...');
            $bar = $this->output->createProgressBar(50);
            $bar->start();
            
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
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            // Count for statistics
            $uploadedCount = 0;
            $pendingCount = 0;
            
            // Assign all users to the instrument with different statuses
            $this->info('Assigning users to instrument...');
            $bar = $this->output->createProgressBar(count($users));
            $bar->start();
            
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
                    
                    $uploadedCount++;
                } else {
                    // Never uploaded, just assigned
                    $assignedAt = Carbon::now()->subDays(rand(5, 30));
                    
                    $instrument->users()->attach($user->id, [
                        'created_at' => $assignedAt,
                        'updated_at' => $assignedAt,
                    ]);
                    
                    $pendingCount++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            $this->info("Created 50 faculty users and linked them to instrument: {$instrument->name}");
            $this->info("Upload status: {$uploadedCount} uploaded, {$pendingCount} pending");
        });

        $this->info("Seeding completed for tenant: {$tenantId}");
        return 0;
    }
} 