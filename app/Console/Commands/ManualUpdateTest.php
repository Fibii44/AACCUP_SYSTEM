<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ManualUpdateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:manual {version=v1.0.4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually extract and apply an update from a downloaded zip file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $version = $this->argument('version');
        $this->info("Starting manual update for version {$version}");

        // 1. Find the downloaded ZIP file
        $downloadPath = config('self-update.repository_types.github.download_path', 'storage/app/github-releases');
        $zipFile = "{$downloadPath}/{$version}.zip";
        
        if (!File::exists($zipFile)) {
            $this->error("Update file not found: {$zipFile}");
            return 1;
        }

        $this->info("Found update file: {$zipFile} (" . File::size($zipFile) . " bytes)");

        // 2. Extract to a temporary directory
        $extractPath = storage_path('app/manual-update');
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        File::makeDirectory($extractPath, 0755, true);

        $this->info("Extracting to: {$extractPath}");

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                // Get the folder name inside the zip (GitHub adds a folder with repo-version name)
                $folderName = $zip->getNameIndex(0);
                $this->info("Zip contains folder: {$folderName}");
                
                $zip->extractTo($extractPath);
                $zip->close();
                $this->info("Extraction complete");
                
                // 3. Find all migration files in the extracted archive
                $migrationSourcePath = $extractPath . '/' . $folderName . 'database/migrations/tenant';
                
                if (!File::exists($migrationSourcePath)) {
                    $this->error("Migration directory not found in extracted update: {$migrationSourcePath}");
                    return 1;
                }
                
                // 4. List all tenant migrations in the archive
                $migrations = File::files($migrationSourcePath);
                $this->info("Found " . count($migrations) . " migrations in the update:");
                
                foreach ($migrations as $migration) {
                    $this->line(" - " . $migration->getFilename());
                }
                
                // 5. Check for our test migration specifically
                $testMigration = null;
                foreach ($migrations as $migration) {
                    $filename = $migration->getFilename();
                    if (str_contains($filename, 'create_test_updates_table')) {
                        $testMigration = $migration;
                        $this->info("Test migration found: " . $filename);
                        break;
                    }
                }
                
                if (!$testMigration) {
                    $this->warn("Test migration not found in the release archive!");
                } else {
                    // 6. Copy the test migration to the correct location
                    $targetPath = database_path('migrations/tenant/' . $testMigration->getFilename());
                    File::copy($testMigration->getPathname(), $targetPath);
                    $this->info("Copied test migration to: {$targetPath}");
                    
                    // 7. Run the migration specifically on the tenant database
                    $this->info("Running migration on tenant database...");
                    
                    Artisan::call('tenants:migrate', [
                        '--path' => 'database/migrations/tenant/' . $testMigration->getFilename(),
                        '--force' => true
                    ]);
                    
                    $this->info(Artisan::output());
                    
                    // 8. Verify the table exists in the tenant database
                    // We need to check if the table exists in the tenant DB context
                    $this->info("Checking if table exists in tenant database...");
                    
                    // Connect to the tenant database (assuming it's already configured)
                    try {
                        $tenantDb = config('database.connections.tenant.database');
                        $this->info("Tenant database: {$tenantDb}");
                        
                        // Use raw query to check if table exists in tenant DB
                        $tableExists = \DB::connection('tenant')
                            ->select("SHOW TABLES LIKE 'test_updates'");
                        
                        if (count($tableExists) > 0) {
                            $this->info("SUCCESS: test_updates table created successfully in tenant database!");
                        } else {
                            $this->error("FAILED: test_updates table was not created in tenant database!");
                        }
                    } catch (\Exception $dbException) {
                        $this->error("Database error: " . $dbException->getMessage());
                    }
                }
                
                $this->info("Manual update process completed.");
                return 0;
            } else {
                $this->error("Failed to open the zip file");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error during manual update: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
} 