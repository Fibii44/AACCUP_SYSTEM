<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class TestUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:update {version=v1.0.4} {--skip-extract} {--skip-migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the update process by extracting the release archive and running migrations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $version = $this->argument('version');
        $skipExtract = $this->option('skip-extract');
        $skipMigration = $this->option('skip-migration');

        $this->info("Testing update for version {$version}");

        $downloadPath = config('self-update.repository_types.github.download_path', 'storage/app/github-releases');
        $zipFile = "{$downloadPath}/{$version}.zip";
        
        if (!File::exists($zipFile)) {
            $this->error("Update file not found: {$zipFile}");
            return 1;
        }

        $this->info("Found update file: {$zipFile} (" . File::size($zipFile) . " bytes)");

        if (!$skipExtract) {
            // Extract to a temporary directory
            $extractPath = storage_path('app/update-extract');
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            File::makeDirectory($extractPath, 0755, true);

            $this->info("Extracting to: {$extractPath}");

            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                // Get the folder name inside the zip (GitHub adds a folder with repo-version name)
                $folderName = $zip->getNameIndex(0);
                $this->info("Zip contains folder: {$folderName}");
                
                $zip->extractTo($extractPath);
                $zip->close();
                $this->info("Extraction complete");
                
                // Migration files should be in database/migrations/tenant
                $migrationPath = $extractPath . '/' . $folderName . 'database/migrations/tenant';
                
                if (File::exists($migrationPath)) {
                    $migrations = File::files($migrationPath);
                    $this->info("Found " . count($migrations) . " migrations:");
                    
                    foreach ($migrations as $migration) {
                        $this->line(" - " . $migration->getFilename());
                    }
                    
                    // Check specifically for our test migration
                    $testMigrationFound = false;
                    foreach ($migrations as $migration) {
                        if (str_contains($migration->getFilename(), 'create_test_updates_table')) {
                            $testMigrationFound = true;
                            $this->info("Test migration found: " . $migration->getFilename());
                            break;
                        }
                    }
                    
                    if (!$testMigrationFound) {
                        $this->warn("Test migration not found in the release archive");
                    }
                } else {
                    $this->warn("No migration directory found in the extracted update");
                }
            } else {
                $this->error("Failed to open the zip file");
                return 1;
            }
        }

        if (!$skipMigration) {
            $this->info("Running migrations...");
            $this->line("php artisan migrate --path=database/migrations/tenant --force");
            
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);
            
            $this->info(Artisan::output());
            $this->info("Migration complete");
            
            // Check if our test table exists
            $this->info("Checking for test_updates table...");
            try {
                $tableExists = \Illuminate\Support\Facades\Schema::hasTable('test_updates');
                if ($tableExists) {
                    $this->info("Success! test_updates table exists");
                } else {
                    $this->error("test_updates table does not exist, migration might have failed");
                }
            } catch (\Exception $e) {
                $this->error("Error checking for table: " . $e->getMessage());
            }
        }

        $this->info("Update test completed");
        return 0;
    }
} 