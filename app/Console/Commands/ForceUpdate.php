<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Tenant\SystemUpdateController;
use Illuminate\Support\Facades\App;

class ForceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:force-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force a system update regardless of version check';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting forced system update...');
        
        try {
            // Get an instance of the SystemUpdateController
            $controller = App::make(SystemUpdateController::class);
            
            // Call the update method but bypass version check
            $reflection = new \ReflectionObject($controller);
            $method = $reflection->getMethod('update');
            $method->setAccessible(true);
            
            // We need to stub the request for the controller to work properly
            $request = new \Illuminate\Http\Request();
            $controller->forceUpdate = true; // Add this property to allow bypassing version check
            
            // Call the update method
            $result = $method->invoke($controller);
            
            $this->info('Update completed.');
            
            // Also run our test migration to be sure
            $this->info('Running test migration...');
            $this->call('migrate', [
                '--path' => 'database/migrations/tenant/2025_04_25_000000_create_test_updates_table.php',
                '--force' => true
            ]);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during forced update: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
} 