<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdateHistory;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Codedge\Updater\Events\UpdateAvailable;
use Illuminate\Support\Facades\Artisan;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SystemUpdateController extends Controller
{
    protected $updater;
    public $forceUpdate = false;

    public function __construct(UpdaterManager $updater)
    {
        $this->updater = $updater;
    }

    /**
     * Get the latest version available for the application
     * 
     * This method tries multiple approaches to find the latest version:
     * 1. Direct GitHub API with semantic versioning
     * 2. The laravel-selfupdater package
     * 3. Hardcoded fallback if all else fails
     * 
     * @return array with keys 'version' and 'is_new_version_available'
     */
    private function getLatestVersionInfo()
    {
        $currentVersion = config('self-update.version_installed');
        $result = [
            'version' => $currentVersion, 
            'is_new_version_available' => false
        ];
        
        try {
            // 1. Try GitHub API with semantic versioning first (most reliable)
            $githubLatestVersion = $this->getGitHubLatestVersion();
            if ($githubLatestVersion) {
                Log::info("GitHub API version check: latest={$githubLatestVersion}");
                $result['version'] = $githubLatestVersion;
                $result['is_new_version_available'] = version_compare(
                    preg_replace('/^v/', '', $githubLatestVersion), 
                    preg_replace('/^v/', '', $currentVersion), 
                    '>'
                );
                return $result;
            }
            
            // 2. Try package detection as fallback
            try {
                $packageVersionAvailable = $this->updater->source()->isNewVersionAvailable();
                $packageLatestVersion = $this->updater->source()->getVersionAvailable();
                Log::info("Package version check: available={$packageVersionAvailable}, latest={$packageLatestVersion}");
                
                // If package detected a version, verify it's actually newer using semantic versioning
                if ($packageLatestVersion) {
                    $isActuallyNewer = version_compare(
                        preg_replace('/^v/', '', $packageLatestVersion), 
                        preg_replace('/^v/', '', $currentVersion), 
                        '>'
                    );
                    
                    $result['version'] = $packageLatestVersion;
                    $result['is_new_version_available'] = $isActuallyNewer;
                    return $result;
                }
            } catch (\Exception $e) {
                Log::warning("Package version check failed: " . $e->getMessage());
            }
            
            // 3. Last resort: hardcoded version if nothing else works
            Log::warning("No version detection methods succeeded, falling back to hardcoded version");
            $hardcodedVersion = 'v1.0.2';
            $result['version'] = $hardcodedVersion;
            $result['is_new_version_available'] = version_compare(
                preg_replace('/^v/', '', $hardcodedVersion), 
                preg_replace('/^v/', '', $currentVersion), 
                '>'
            );
            
        } catch (\Exception $e) {
            Log::error("Error in getLatestVersionInfo: " . $e->getMessage());
            // Return current version in case of error
        }
        
        return $result;
    }

    /**
     * Show the system update page
     */
    public function index()
    {
        // Reload the config to ensure we have the most up-to-date version
        Artisan::call('config:clear');
        
        // Get current version
        $currentVersion = config('self-update.version_installed');
        
        // Check if there's an update available
        $versionInfo = $this->getLatestVersionInfo();
        $isNewVersionAvailable = $versionInfo['is_new_version_available'];
        $latestVersion = $versionInfo['version'];
        
        // Debug GitHub settings
        $githubSettings = config('self-update.repository_types.github');
        Log::info('GitHub Update Settings', $githubSettings);
        
        // Get update history
        $updateHistory = SystemUpdateHistory::orderBy('created_at', 'desc')->get();
        
        return view('tenant.system-updates', compact(
            'currentVersion', 
            'isNewVersionAvailable', 
            'latestVersion',
            'updateHistory'
        ));
    }

    /**
     * Check for updates
     */
    public function check()
    {
        try {
            // Get current version
            $currentVersion = config('self-update.version_installed');
            
            // Debug: Log the GitHub settings
            Log::info('Update Check - GitHub Settings', config('self-update.repository_types.github'));
            
            // Get latest version info
            $versionInfo = $this->getLatestVersionInfo();
            $isNewVersionAvailable = $versionInfo['is_new_version_available'];
            $latestVersion = $versionInfo['version'];
            
            if ($isNewVersionAvailable) {
                // Trigger update available event (will send notifications based on config)
                event(new UpdateAvailable($latestVersion));
                
                return redirect()->route('tenant.system-updates.index')
                    ->with('success', "New version available: {$latestVersion}");
            }
            
            return redirect()->route('tenant.system-updates.index')
                ->with('info', "Your system is up-to-date (version {$currentVersion})");
                
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
            
            return redirect()->route('tenant.system-updates.index')
                ->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }

    /**
     * Update the application
     */
    public function update()
    {
        try {
            // Get current version and latest version info
            $currentVersion = config('self-update.version_installed');
            $versionInfo = $this->getLatestVersionInfo();
            $newVersion = $versionInfo['version'];
            $isNewVersionAvailable = $versionInfo['is_new_version_available'] || $this->forceUpdate;
            
            // Check if the version is actually newer
            if (!$isNewVersionAvailable) {
                return redirect()->route('tenant.system-updates.index')
                    ->with('info', "No updates available. Current version: {$currentVersion}");
            }
            
            // First, try downloading the release directly using our custom method
            // This is more reliable than using the package's download mechanism
            try {
                Log::info("Attempting direct download of version {$newVersion}");
                $downloadResult = $this->downloadReleaseZip($newVersion);
                
                if (!$downloadResult || (is_array($downloadResult) && !$downloadResult['success'])) {
                    Log::error("Failed to download the release ZIP for version {$newVersion}");
                    return redirect()->route('tenant.system-updates.index')
                        ->with('error', "Failed to download update files. Please try again.");
                }
                
                Log::info("Successfully downloaded source code for version {$newVersion}");
                
                // Now extract and apply the files
                $extractSuccess = $this->extractAndApplyRelease($newVersion);
                if (!$extractSuccess) {
                    Log::error("Failed to extract and apply the update files for version {$newVersion}");
                    return redirect()->route('tenant.system-updates.index')
                        ->with('error', "Failed to extract update files. Please try again.");
                }
                
                Log::info("Successfully extracted and applied updates for version {$newVersion}");
                $updateResult = true;
            } catch (\Exception $e) {
                Log::error("Error with direct update method: " . $e->getMessage());
                
                // Fall back to the package's update method
                Log::info("Falling back to package update method");
                try {
                    // Start the update process using the package
                    try {
                        // We need to use reflection to create a Release object to pass to the update method
                        $release = null;
                        
                        try {
                            // Get the Release class
                            $reflectionClass = new \ReflectionClass('Codedge\Updater\Models\Release');
                            
                            // Check if we can instantiate it
                            if ($reflectionClass->isInstantiable()) {
                                // Create a Release object with the constructor parameters
                                $release = $reflectionClass->newInstance();
                                
                                // Set version using reflection if needed
                                if ($reflectionClass->hasProperty('version')) {
                                    $versionProperty = $reflectionClass->getProperty('version');
                                    $versionProperty->setAccessible(true);
                                    $versionProperty->setValue($release, $newVersion);
                                }
                                
                                // Now pass the Release object to the update method
                                $updateSource = $this->updater->source();
                                $updateResult = $updateSource->update($release);
                            } else {
                                Log::warning("Release class exists but is not instantiable");
                                $updateResult = false;
                            }
                        } catch (\Exception $e) {
                            Log::error("Error creating Release object: " . $e->getMessage());
                            $updateResult = false;
                        }
                        
                        // If the above doesn't work, try an alternative approach
                        if (!isset($updateResult) || $updateResult === false) {
                            Log::warning("Trying alternative update approach");
                            
                            // Try to get a release object directly from package internals
                            try {
                                $updateSource = $this->updater->source();
                                $releaseCollection = $updateSource->getReleases();
                                
                                if ($releaseCollection instanceof \Illuminate\Support\Collection && $releaseCollection->isNotEmpty()) {
                                    // Get the first release from the collection
                                    $firstRelease = $releaseCollection->first();
                                    
                                    // Pass it to the update method
                                    $updateResult = $updateSource->update($firstRelease);
                                } else {
                                    Log::warning("No releases found in collection");
                                    $updateResult = false;
                                }
                            } catch (\Exception $e) {
                                Log::error("Error with alternative update approach: " . $e->getMessage());
                                $updateResult = false;
                            }
                        }
                        
                        // Do NOT set updateResult to true if all approaches failed
                        if (!isset($updateResult)) {
                            Log::error("All update approaches failed");
                            $updateResult = false;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error during package update: ' . $e->getMessage());
                        $updateResult = false;
                    }
                } catch (\Exception $e) {
                    Log::error('Error during fallback update: ' . $e->getMessage());
                    $updateResult = false;
                }
            }
            
            if ($updateResult) {
                // Clear cache, update routes, etc.
                Artisan::call('optimize:clear');
                
                // Skip main application migrations since we're in tenant context
                // Artisan::call('migrate', ['--force' => true]);
                
                // Only run tenant migrations and ignore errors about existing tables
                try {
                    Log::info('Running tenant migrations...');
                    // Use our custom method to run only new migrations
                    $migrationResult = $this->runNewTenantMigrations();
                    
                    if ($migrationResult) {
                        Log::info('Tenant migrations completed successfully');
                    } else {
                        Log::warning('Tenant migrations had issues');
                    }
                } catch (\Exception $e) {
                    Log::warning('Tenant migration error (non-fatal): ' . $e->getMessage());
                    // Log the detailed exception
                    Log::debug('Migration exception details: ' . $e->getTraceAsString());
                    // Continue despite migration errors
                }
                
                // Update the version in .env file
                try {
                    $this->updateEnvVersion($newVersion);
                    Log::info("Updated .env version to {$newVersion}");
                    
                    // Clear config cache to ensure the new version is used
                    Artisan::call('config:clear');
                } catch (\Exception $e) {
                    Log::error("Failed to update .env version: " . $e->getMessage());
                }
                
                // Record the update
                SystemUpdateHistory::create([
                    'version' => $newVersion,
                    'previous_version' => $currentVersion,
                    'changes' => 'System updated from ' . $currentVersion . ' to ' . $newVersion,
                    'status' => 'success',
                ]);
                
                return redirect()->route('tenant.system-updates.index')
                    ->with('success', 'System updated successfully to version ' . $newVersion);
            }
            
            // Record the failed update
            SystemUpdateHistory::create([
                'version' => $newVersion,
                'previous_version' => $currentVersion,
                'status' => 'failed',
                'error_message' => 'Update process failed to complete',
            ]);
            
            return redirect()->route('tenant.system-updates.index')
                ->with('error', 'Update failed. Please try again.');
                
        } catch (\Exception $e) {
            Log::error('Error during update: ' . $e->getMessage());
            
            // Record the error
            $currentVersion = config('self-update.version_installed');
            $newVersion = $this->getGitHubLatestVersion() ?? 'v1.0.2';
            
            SystemUpdateHistory::create([
                'version' => $newVersion,
                'previous_version' => $currentVersion,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            return redirect()->route('tenant.system-updates.index')
                ->with('error', 'Error during update: ' . $e->getMessage());
        }
    }

    /**
     * Download a GitHub release ZIP file
     * 
     * @param string $version The version to download
     * @return bool Whether the download was successful
     */
    private function downloadReleaseZip($version)
    {
        try {
            // Get GitHub repository details from config
            $githubConfig = config('self-update.repository_types.github');
            $vendor = $githubConfig['repository_vendor'];
            $repo = $githubConfig['repository_name'];
            $token = $githubConfig['private_access_token'];
            
            // Try multiple download paths - handle both tenant and non-tenant contexts
            $possiblePaths = [
                storage_path('app/github-releases'),
                storage_path('tenantitdept/app/github-releases'),
                storage_path('tenantitdept/storage/app/github-releases'), // Nested storage path
                base_path('storage/tenantitdept/storage/app/github-releases'), // Absolute path with nested storage
                base_path('storage/tenantitdept/app/github-releases') // Alternate tenant path
            ];
            
            // Check if any of the directory paths already exist
            $downloadPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $downloadPath = $path;
                    Log::info("Using existing download directory: {$downloadPath}");
                    break;
                }
            }
            
            // If none exists, try to create each until one succeeds
            if ($downloadPath === null) {
                foreach ($possiblePaths as $path) {
                    try {
                        if (!file_exists($path)) {
                            if (mkdir($path, 0755, true)) {
                                $downloadPath = $path;
                                Log::info("Created download directory: {$downloadPath}");
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to create directory {$path}: " . $e->getMessage());
                    }
                }
            }
            
            // If we still don't have a path, use the first one as a last resort
            if ($downloadPath === null) {
                $downloadPath = $possiblePaths[0];
                Log::warning("Using fallback download path: {$downloadPath}");
            }
            
            // Log all possible paths for debugging
            Log::info("All possible download paths checked: " . implode(', ', $possiblePaths));
            Log::info("Selected download path: {$downloadPath}");
            
            // Setup the release download URL
            $downloadUrl = "https://github.com/{$vendor}/{$repo}/archive/refs/tags/{$version}.zip";
            $zipFile = $downloadPath . '/' . $version . '.zip';
            
            // Use GuzzleHttp client to download the file
            $client = new Client();
            $headers = [
                'User-Agent' => 'SystemUpdater'
            ];
            
            if (!empty($token)) {
                $headers['Authorization'] = "token {$token}";
            }
            
            Log::info("Downloading from: {$downloadUrl}");
            Log::info("Saving to: {$zipFile}");
            
            // Download the file
            $response = $client->request('GET', $downloadUrl, [
                'headers' => $headers,
                'sink' => $zipFile,
                'timeout' => config('self-update.download_timeout', 300)
            ]);
            
            // Verify download success
            if ($response->getStatusCode() !== 200) {
                Log::error("Failed to download version {$version}, HTTP status: " . $response->getStatusCode());
                return false;
            }
            
            if (!file_exists($zipFile)) {
                Log::error("File not found after download: {$zipFile}");
                return false;
            }
            
            $fileSize = filesize($zipFile);
            if ($fileSize <= 0) {
                Log::error("Downloaded file is empty: {$zipFile}");
                return false;
            }
            
            Log::info("Successfully downloaded {$version}.zip ({$fileSize} bytes)");
            
            // Return both the success status and the path where the file was saved
            return [
                'success' => true,
                'path' => $downloadPath
            ];
            
        } catch (\Exception $e) {
            Log::error("Error downloading release zip: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract and apply a downloaded release
     * 
     * @param string $version The version to extract and apply
     * @return bool Whether the extraction and application was successful
     */
    private function extractAndApplyRelease($version)
    {
        try {
            // Try to find the ZIP file in possible locations
            $possiblePaths = [
                storage_path('app/github-releases'),
                storage_path('tenantitdept/app/github-releases'),
                storage_path('tenantitdept/storage/app/github-releases'), // Nested storage path
                base_path('storage/tenantitdept/storage/app/github-releases'), // Absolute path with nested storage
                base_path('storage/tenantitdept/app/github-releases') // Alternate tenant path
            ];
            
            $zipFile = null;
            $downloadPath = null;
            
            // Check each possible location for the zip file
            foreach ($possiblePaths as $path) {
                $testPath = $path . '/' . $version . '.zip';
                if (file_exists($testPath)) {
                    $zipFile = $testPath;
                    $downloadPath = $path;
                    Log::info("Found update zip file at: {$zipFile}");
                    break;
                }
            }
            
            if (!$zipFile) {
                Log::error("Update zip file not found in any of the possible locations");
                Log::error("Checked the following paths:");
                foreach ($possiblePaths as $path) {
                    Log::error(" - {$path}/{$version}.zip");
                }
                return false;
            }
            
            // Check file size to ensure it's a valid download
            $fileSize = filesize($zipFile);
            Log::info("ZIP file size: {$fileSize} bytes");
            
            if ($fileSize < 1000) { // Less than 1KB is definitely wrong
                Log::error("ZIP file seems too small ({$fileSize} bytes), likely corrupted");
                return false;
            }
            
            // Check available disk space
            $freeSpace = disk_free_space(dirname($zipFile));
            Log::info("Free disk space: {$freeSpace} bytes");
            
            if ($freeSpace < $fileSize * 3) { // Need at least 3x the ZIP size for extraction
                Log::error("Not enough disk space for extraction. Needed: " . ($fileSize * 3) . ", Available: {$freeSpace}");
                return false;
            }
            
            // Use the same base path for extract directory
            $extractBasePath = dirname($downloadPath);
            $extractPath = $extractBasePath . '/update-extract';
            
            // Clean up existing extraction directory
            if (file_exists($extractPath)) {
                Log::info("Removing existing extraction directory: {$extractPath}");
                if (!File::deleteDirectory($extractPath)) {
                    Log::error("Failed to delete existing extraction directory. Check permissions.");
                    return false;
                }
            }
            
            if (!File::makeDirectory($extractPath, 0755, true)) {
                Log::error("Failed to create extraction directory: {$extractPath} - Check permissions");
                return false;
            }
            
            Log::info("Extracting {$zipFile} to {$extractPath}");
            
            // EXTRACTION METHOD 1: Try ZipArchive first
            $extractionSuccess = false;
            
            if (class_exists('ZipArchive')) {
                try {
                    Log::info("Attempting extraction with ZipArchive");
                    $zip = new \ZipArchive();
                    $openResult = $zip->open($zipFile);
                    
                    if ($openResult !== true) {
                        Log::error("Failed to open zip file with ZipArchive. Error code: {$openResult}");
                    } else {
                        Log::info("ZipArchive opened successfully. Found " . $zip->numFiles . " files in archive");
                        
                        // Extract the zip file
                        $extractResult = $zip->extractTo($extractPath);
                        $zip->close();
                        
                        if ($extractResult) {
                            Log::info("ZipArchive extraction successful");
                            $extractionSuccess = true;
                        } else {
                            Log::error("ZipArchive extraction failed");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("ZipArchive extraction error: " . $e->getMessage());
                }
            } else {
                Log::warning("ZipArchive not available, will try alternative extraction methods");
            }
            
            // EXTRACTION METHOD 2: If ZipArchive failed, try PharData
            if (!$extractionSuccess && class_exists('PharData')) {
                try {
                    Log::info("Attempting extraction with PharData");
                    
                    // Temporarily rename zip to tar for PharData to work
                    $tempTarPath = $zipFile . '.tar';
                    if (copy($zipFile, $tempTarPath)) {
                        $archive = new \PharData($tempTarPath);
                        $archive->extractTo($extractPath, null, true); // Overwrite
                        unlink($tempTarPath);
                        
                        Log::info("PharData extraction appears successful");
                        $extractionSuccess = true;
                    } else {
                        Log::error("Failed to create temporary file for PharData extraction");
                    }
                } catch (\Exception $e) {
                    Log::error("PharData extraction error: " . $e->getMessage());
                }
            }
            
            // EXTRACTION METHOD 3: Try system unzip command
            if (!$extractionSuccess) {
                try {
                    Log::info("Attempting extraction with system unzip command");
                    
                    // Check if unzip command exists
                    $unzipExists = false;
                    
                    if (function_exists('exec')) {
                        $unzipExists = trim(exec('which unzip'));
                    }
                    
                    if ($unzipExists) {
                        $command = "unzip -o {$zipFile} -d {$extractPath} 2>&1";
                        Log::info("Running command: {$command}");
                        
                        $output = [];
                        $returnVar = 0;
                        exec($command, $output, $returnVar);
                        
                        Log::info("Unzip command output: " . implode("\n", $output));
                        
                        if ($returnVar === 0) {
                            Log::info("System unzip extraction successful");
                            $extractionSuccess = true;
                        } else {
                            Log::error("System unzip extraction failed with code {$returnVar}");
                        }
                    } else {
                        Log::warning("System unzip command not available");
                    }
                } catch (\Exception $e) {
                    Log::error("System unzip extraction error: " . $e->getMessage());
                }
            }
            
            if (!$extractionSuccess) {
                Log::error("All extraction methods failed. Cannot proceed with update.");
                return false;
            }
            
            // Verify extraction was successful by checking for files
            $extractedFiles = File::allFiles($extractPath);
            $extractedFileCount = count($extractedFiles);
            
            Log::info("Extracted file count: {$extractedFileCount}");
            
            if ($extractedFileCount === 0) {
                Log::error("Extraction appeared to succeed but no files were extracted");
                return false;
            }
            
            // Determine the root directory inside the zip (typically repo-version format)
            $directories = File::directories($extractPath);
            if (count($directories) === 0) {
                Log::error("No root directory found in extracted zip");
                return false;
            }
            
            $extractedRoot = $directories[0];
            Log::info("Extracted files to: {$extractedRoot}");
            
            // Verify the extracted directory has expected structure
            $isValidExtraction = false;
            
            // Check for key files/directories that should exist in any Laravel project
            $validationPaths = [
                'app',
                'bootstrap',
                'config',
                'database',
                'public',
                'routes',
                'composer.json'
            ];
            
            foreach ($validationPaths as $path) {
                if (file_exists($extractedRoot . '/' . $path)) {
                    $isValidExtraction = true;
                    break;
                }
            }
            
            if (!$isValidExtraction) {
                Log::error("Extracted directory does not appear to be a valid Laravel project");
                Log::error("Files in extracted root: " . implode(', ', array_map('basename', glob($extractedRoot . '/*'))));
                return false;
            }
            
            // Prepare list of directories to exclude from replacement
            $excludeDirectories = config('self-update.exclude_folders', []);
            Log::info("Excluding directories: " . implode(', ', $excludeDirectories));
            
            // Copy files from the extracted directory to the app root, skipping excluded directories
            $copySuccess = $this->copyDirectoryContents($extractedRoot, base_path(), $excludeDirectories);
            
            if (!$copySuccess) {
                Log::error("Failed to copy extracted files to application directory");
                return false;
            }
            
            // Clean up
            Log::info("Cleaning up temporary files");
            File::deleteDirectory($extractPath);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error extracting and applying release: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Copy directory contents recursively, excluding specified directories
     * 
     * @param string $source Source directory
     * @param string $destination Destination directory 
     * @param array $excludeDirectories Directories to exclude
     * @return bool Whether the copy operation was successful
     */
    private function copyDirectoryContents($source, $destination, $excludeDirectories = [])
    {
        try {
            // Normalize paths to ensure consistent comparisons
            $source = rtrim($source, '/\\') . DIRECTORY_SEPARATOR;
            $destination = rtrim($destination, '/\\') . DIRECTORY_SEPARATOR;
            
            Log::info("Copying files from {$source} to {$destination}");
            
            // Get all items from the source directory
            $items = File::allFiles($source);
            $totalFiles = count($items);
            $copiedFiles = 0;
            $skippedFiles = 0;
            $errorFiles = 0;
            
            Log::info("Found {$totalFiles} files to copy");
            
            foreach ($items as $item) {
                // Get relative path from the source root
                $relativePath = str_replace($source, '', $item->getPathname());
                $targetPath = $destination . $relativePath;
                
                // Check if this file is in an excluded directory
                $shouldExclude = false;
                foreach ($excludeDirectories as $excludeDir) {
                    if (strpos($relativePath, $excludeDir . DIRECTORY_SEPARATOR) === 0 || $relativePath === $excludeDir) {
                        $shouldExclude = true;
                        break;
                    }
                }
                
                // Skip if it's in an excluded directory
                if ($shouldExclude) {
                    // Log::info("Skipping excluded file: {$relativePath}");
                    $skippedFiles++;
                    continue;
                }
                
                // Create target directory if it doesn't exist
                $targetDir = dirname($targetPath);
                if (!file_exists($targetDir)) {
                    if (!File::makeDirectory($targetDir, 0755, true)) {
                        Log::error("Failed to create directory: {$targetDir}");
                        $errorFiles++;
                        continue;
                    }
                }
                
                // Copy the file
                try {
                    if (File::copy($item->getPathname(), $targetPath)) {
                        $copiedFiles++;
                    } else {
                        Log::warning("Failed to copy {$relativePath}");
                        $errorFiles++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to copy {$relativePath}: " . $e->getMessage());
                    $errorFiles++;
                }
            }
            
            Log::info("Copy operation summary: {$copiedFiles} copied, {$skippedFiles} skipped, {$errorFiles} errors");
            
            return $errorFiles === 0; // Success only if there were no errors
        } catch (\Exception $e) {
            Log::error("Error in copyDirectoryContents: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback to the previous version
     */
    public function rollback()
    {
        try {
            $currentVersion = config('self-update.version_installed');
            Log::info("Starting rollback from version {$currentVersion}");
            
            // Get available GitHub releases to determine the correct rollback version
            $previousVersion = $this->getPreviousRelease($currentVersion);
            
            if (!$previousVersion) {
                // If we couldn't determine a previous version, default to v1.0.1
                $previousVersion = 'v1.0.1';
                Log::info("No previous release found, defaulting to {$previousVersion}");
            }
            
            Log::info("Rolling back from {$currentVersion} to {$previousVersion}");
            
            // Download and extract the previous version's source code
            try {
                Log::info("Downloading and extracting source for version {$previousVersion}");
                
                // First try using the enhanced extraction method from the update process
                $sourceRestored = $this->extractAndApplyRelease($previousVersion);
                
                // If that fails, try the dedicated rollback method
                if (!$sourceRestored) {
                    Log::warning("Failed to use standard extraction process, trying rollback-specific method");
                    $sourceRestored = $this->downloadAndRestoreVersion($previousVersion);
                }
                
                if (!$sourceRestored) {
                    Log::error("Failed to restore source code for version {$previousVersion}");
                    
                    // Check if the zip file exists in any of our possible locations
                    $possiblePaths = [
                        storage_path('app/github-releases'),
                        storage_path('tenantitdept/app/github-releases'),
                        storage_path('tenantitdept/storage/app/github-releases'),
                        base_path('storage/tenantitdept/storage/app/github-releases'),
                        base_path('storage/tenantitdept/app/github-releases')
                    ];
                    
                    $zipExists = false;
                    foreach ($possiblePaths as $path) {
                        $testPath = $path . '/' . $previousVersion . '.zip';
                        if (file_exists($testPath)) {
                            $zipExists = true;
                            Log::info("ZIP file exists at {$testPath} but extraction failed");
                            break;
                        }
                    }
                    
                    if (!$zipExists) {
                        Log::error("ZIP file for version {$previousVersion} doesn't exist in any location");
                    }
                    
                    return redirect()->route('tenant.system-updates.index')
                        ->with('error', 'Failed to restore source code for version ' . $previousVersion);
                }
                
                Log::info("Successfully restored source code for version {$previousVersion}");
            } catch (\Exception $e) {
                Log::error("Error restoring source code: " . $e->getMessage());
                Log::error("Stack trace: " . $e->getTraceAsString());
                return redirect()->route('tenant.system-updates.index')
                    ->with('error', 'Error restoring source code: ' . $e->getMessage());
            }
            
            // Rollback tenant database migrations
            try {
                Log::info("Rolling back tenant database migrations...");
                Artisan::call('tenants:rollback', [
                    '--step' => 1,  // Roll back one migration batch
                    '--force' => true
                ]);
                Log::info("Migration rollback output: " . Artisan::output());
            } catch (\Exception $e) {
                Log::warning("Tenant migration rollback error (non-fatal): " . $e->getMessage());
                // Continue despite migration errors
            }
            
            try {
                // Try to delete update archives if that method exists
                if (method_exists($this->updater->source(), 'deleteUpdateArchives')) {
                    $this->updater->source()->deleteUpdateArchives();
                }
            } catch (\Exception $e) {
                Log::warning("Rollback archive cleanup failed: " . $e->getMessage());
                // Continue despite this error
            }
            
            // Update the version in .env file
            try {
                $this->updateEnvVersion($previousVersion);
                Log::info("Updated .env version to {$previousVersion} after rollback");
                
                // Clear config cache to ensure the new version is used
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                Log::error("Failed to update .env version during rollback: " . $e->getMessage());
            }
            
            // Record the rollback
            SystemUpdateHistory::create([
                'version' => $previousVersion,
                'previous_version' => $currentVersion,
                'changes' => 'Rolled back from ' . $currentVersion . ' to ' . $previousVersion,
                'status' => 'rolled-back',
            ]);
            
            return redirect()->route('tenant.system-updates.index')
                ->with('success', 'Successfully rolled back to version ' . $previousVersion . ' and restored files and database');
        } catch (\Exception $e) {
            Log::error('Error during rollback: ' . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Record the error
            $currentVersion = config('self-update.version_installed');
            
            SystemUpdateHistory::create([
                'version' => 'rollback-failed',
                'previous_version' => $currentVersion,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            return redirect()->route('tenant.system-updates.index')
                ->with('error', 'Error during rollback: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the previous release version based on GitHub releases
     * 
     * @param string $currentVersion The current version
     * @return string|null The previous version or null if none found
     */
    private function getPreviousRelease($currentVersion)
    {
        try {
            // Get all releases from GitHub
            $releases = $this->getAllGitHubReleases();
            
            if (empty($releases)) {
                Log::warning("No GitHub releases found");
                return null;
            }
            
            // Sort releases by semantic version (not by date)
            usort($releases, function($a, $b) {
                // Remove 'v' prefix if it exists for proper version comparison
                $versionA = preg_replace('/^v/', '', $a['tag_name']);
                $versionB = preg_replace('/^v/', '', $b['tag_name']);
                return version_compare($versionB, $versionA); // Descending order (newest first)
            });
            
            // Find the current version's index
            $currentIndex = -1;
            $currentVersionNormalized = preg_replace('/^v/', '', $currentVersion);
            
            foreach ($releases as $index => $release) {
                $releaseVersionNormalized = preg_replace('/^v/', '', $release['tag_name']);
                if ($releaseVersionNormalized === $currentVersionNormalized) {
                    $currentIndex = $index;
                    break;
                }
            }
            
            // If we found the current version and there's a previous version available
            if ($currentIndex !== -1 && isset($releases[$currentIndex + 1])) {
                return $releases[$currentIndex + 1]['tag_name']; // Return the previous version
            } else if ($currentIndex === -1 && !empty($releases)) {
                // If current version not found in releases but releases exist, return the latest
                return $releases[0]['tag_name'];
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error determining previous release: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all GitHub releases for the repository
     * 
     * @return array Array of releases
     */
    private function getAllGitHubReleases()
    {
        try {
            $githubConfig = config('self-update.repository_types.github');
            $vendor = $githubConfig['repository_vendor'];
            $repo = $githubConfig['repository_name'];
            $token = $githubConfig['private_access_token'];
            
            if (empty($vendor) || empty($repo)) {
                Log::warning("GitHub configuration missing vendor or repo name");
                return [];
            }
            
            $client = new Client();
            $url = "https://api.github.com/repos/{$vendor}/{$repo}/releases";
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'SystemUpdate'
            ];
            
            if (!empty($token)) {
                $headers['Authorization'] = "token {$token}";
            }
            
            $response = $client->request('GET', $url, [
                'headers' => $headers
            ]);
            
            $releases = json_decode($response->getBody()->getContents(), true);
            
            if (empty($releases)) {
                Log::warning("No releases found on GitHub");
                return [];
            }
            
            // Filter out drafts and prereleases if needed
            $releases = array_filter($releases, function($release) {
                return !$release['draft'] && !$release['prerelease'];
            });
            
            return $releases;
        } catch (\Exception $e) {
            Log::error("Error fetching GitHub releases: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get the latest version from GitHub API directly
     * 
     * @return string|null The latest version tag or null if failed
     */
    private function getGitHubLatestVersion()
    {
        try {
            $githubConfig = config('self-update.repository_types.github');
            $vendor = $githubConfig['repository_vendor'];
            $repo = $githubConfig['repository_name'];
            $token = $githubConfig['private_access_token'];
            
            if (empty($vendor) || empty($repo)) {
                Log::warning("GitHub configuration missing vendor or repo name");
                return null;
            }
            
            $client = new Client();
            $url = "https://api.github.com/repos/{$vendor}/{$repo}/releases";
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'SystemUpdate'
            ];
            
            if (!empty($token)) {
                $headers['Authorization'] = "token {$token}";
            }
            
            $response = $client->request('GET', $url, [
                'headers' => $headers
            ]);
            
            $releases = json_decode($response->getBody()->getContents(), true);
            
            if (empty($releases)) {
                Log::warning("No releases found on GitHub");
                return null;
            }
            
            // Filter out drafts and prereleases if needed
            $releases = array_filter($releases, function($release) {
                return !$release['draft'] && !$release['prerelease'];
            });
            
            if (empty($releases)) {
                Log::warning("No published releases found on GitHub");
                return null;
            }
            
            // Sort by semantic version (not by date)
            usort($releases, function($a, $b) {
                // Remove 'v' prefix if it exists for proper version comparison
                $versionA = preg_replace('/^v/', '', $a['tag_name']);
                $versionB = preg_replace('/^v/', '', $b['tag_name']);
                return version_compare($versionB, $versionA); // Descending order
            });
            
            // Debug all sorted releases
            $releasesInfo = [];
            foreach ($releases as $release) {
                $releasesInfo[] = $release['tag_name'] . ' (Published: ' . $release['published_at'] . ')';
            }
            Log::info('Sorted GitHub releases: ' . implode(', ', $releasesInfo));
            
            return $releases[0]['tag_name'];
        } catch (\Exception $e) {
            Log::error("Error getting GitHub latest version: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update the SELF_UPDATER_VERSION_INSTALLED value in the .env file
     *
     * @param string $newVersion The new version to set
     * @return bool Whether the update was successful
     */
    private function updateEnvVersion($newVersion)
    {
        $envPath = base_path('.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // Check if the SELF_UPDATER_VERSION_INSTALLED entry exists
            if (preg_match('/SELF_UPDATER_VERSION_INSTALLED=(.*)/', $envContent)) {
                // Replace the existing entry
                $envContent = preg_replace(
                    '/SELF_UPDATER_VERSION_INSTALLED=(.*)/', 
                    'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion, 
                    $envContent
                );
            } else {
                // Add the entry if it doesn't exist
                $envContent .= "\nSELF_UPDATER_VERSION_INSTALLED={$newVersion}\n";
            }
            
            // Write the updated content back to the .env file
            return file_put_contents($envPath, $envContent) !== false;
        }
        
        return false;
    }
    
    /**
     * Debug the version information
     */
    public function debugVersion()
    {
        $envVersion = env('SELF_UPDATER_VERSION_INSTALLED');
        $configVersion = config('self-update.version_installed');
        $latestVersionInfo = $this->getLatestVersionInfo();
        
        // Get information about the updater source object
        $sourceClass = get_class($this->updater->source());
        $sourceMethods = get_class_methods($this->updater->source());
        
        return response()->json([
            'env_version' => $envVersion,
            'config_version' => $configVersion,
            'latest_version' => $latestVersionInfo['version'],
            'is_new_version_available' => $latestVersionInfo['is_new_version_available'],
            'updater_info' => [
                'source_class' => $sourceClass,
                'available_methods' => $sourceMethods
            ],
            'config' => [
                'github' => config('self-update.repository_types.github')
            ],
            'update_history' => SystemUpdateHistory::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
        ]);
    }

    /**
     * Run migrations for only new tenant migrations
     * 
     * This avoids the issue where the system tries to run all migrations
     * including ones that have already been run
     */
    private function runNewTenantMigrations()
    {
        Log::info("Running new tenant migrations...");
        
        try {
            // Get list of already run migrations
            $ranMigrations = \DB::table('migrations')->pluck('migration')->toArray();
            
            // Get list of all migration files
            $migrationPath = database_path('migrations/tenant');
            $migrationFiles = \File::files($migrationPath);
            
            // Find new migrations that haven't been run
            $newMigrations = [];
            foreach ($migrationFiles as $file) {
                $filename = $file->getFilename();
                $migrationName = pathinfo($filename, PATHINFO_FILENAME);
                
                if (!in_array($migrationName, $ranMigrations)) {
                    $newMigrations[] = $migrationPath . '/' . $filename;
                    Log::info("Found new migration: " . $filename);
                }
            }
            
            // Run each new migration individually
            foreach ($newMigrations as $migration) {
                Log::info("Running migration: " . basename($migration));
                \Artisan::call('migrate', [
                    '--path' => 'database/migrations/tenant/' . basename($migration),
                    '--force' => true,
                ]);
                
                $output = \Artisan::output();
                if ($output) {
                    Log::info("Migration output: " . $output);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error running migrations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Download and restore a specific version's source code
     * 
     * @param string $version The version to restore
     * @return bool Whether the restoration was successful
     */
    private function downloadAndRestoreVersion($version)
    {
        try {
            // Get GitHub repository details from config
            $githubConfig = config('self-update.repository_types.github');
            $vendor = $githubConfig['repository_vendor'];
            $repo = $githubConfig['repository_name'];
            $token = $githubConfig['private_access_token'];
            
            // Try multiple download paths - handle both tenant and non-tenant contexts
            $possiblePaths = [
                storage_path('app/github-releases'),
                storage_path('tenantitdept/app/github-releases'),
                storage_path('tenantitdept/storage/app/github-releases'), // Nested storage path
                base_path('storage/tenantitdept/storage/app/github-releases'), // Absolute path with nested storage
                base_path('storage/tenantitdept/app/github-releases') // Alternate tenant path
            ];
            
            // Check if any of the directory paths already exist
            $downloadPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $downloadPath = $path;
                    Log::info("Using existing download directory: {$downloadPath}");
                    break;
                }
            }
            
            // If none exists, try to create each until one succeeds
            if ($downloadPath === null) {
                foreach ($possiblePaths as $path) {
                    try {
                        if (!file_exists($path)) {
                            if (mkdir($path, 0755, true)) {
                                $downloadPath = $path;
                                Log::info("Created download directory: {$downloadPath}");
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to create directory {$path}: " . $e->getMessage());
                    }
                }
            }
            
            // If we still don't have a path, use the first one as a last resort
            if ($downloadPath === null) {
                $downloadPath = $possiblePaths[0];
                Log::warning("Using fallback download path: {$downloadPath}");
            }
            
            // Log all possible paths for debugging
            Log::info("All possible download paths checked: " . implode(', ', $possiblePaths));
            Log::info("Selected download path: {$downloadPath}");
            
            // Setup the release download URL
            $downloadUrl = "https://github.com/{$vendor}/{$repo}/archive/refs/tags/{$version}.zip";
            $zipFile = $downloadPath . '/' . $version . '.zip';
            
            // Check if we already have the file downloaded
            if (!file_exists($zipFile)) {
                Log::info("Downloading source code for version {$version} from {$downloadUrl}");
                
                // Setup HTTP client with appropriate headers
                $client = new Client();
                $headers = [
                    'User-Agent' => 'SystemRollback'
                ];
                
                if (!empty($token)) {
                    $headers['Authorization'] = "token {$token}";
                }
                
                // Download the file
                $response = $client->request('GET', $downloadUrl, [
                    'headers' => $headers,
                    'sink' => $zipFile,
                    'timeout' => config('self-update.download_timeout', 300)
                ]);
                
                if ($response->getStatusCode() !== 200) {
                    Log::error("Failed to download version {$version}, HTTP status: " . $response->getStatusCode());
                    return false;
                }
                
                if (!file_exists($zipFile)) {
                    Log::error("File not found after download: {$zipFile}");
                    return false;
                }
            } else {
                Log::info("Using previously downloaded file for version {$version}");
            }
            
            // Check file size to ensure it's a valid download
            $fileSize = filesize($zipFile);
            Log::info("ZIP file size: {$fileSize} bytes");
            
            if ($fileSize < 1000) { // Less than 1KB is definitely wrong
                Log::error("ZIP file seems too small ({$fileSize} bytes), likely corrupted");
                return false;
            }
            
            // Extract the downloaded zip file
            // Use the same base path for extract directory
            $extractBasePath = dirname($downloadPath);
            $extractPath = $extractBasePath . '/rollback-extract';
            
            // Clean up existing extraction directory
            if (file_exists($extractPath)) {
                Log::info("Removing existing extraction directory: {$extractPath}");
                if (!File::deleteDirectory($extractPath)) {
                    Log::error("Failed to delete existing extraction directory. Check permissions.");
                    return false;
                }
            }
            
            if (!File::makeDirectory($extractPath, 0755, true)) {
                Log::error("Failed to create extraction directory: {$extractPath}");
                return false;
            }
            
            Log::info("Extracting {$zipFile} to {$extractPath}");
            
            // EXTRACTION METHOD 1: Try ZipArchive first
            $extractionSuccess = false;
            
            if (class_exists('ZipArchive')) {
                try {
                    Log::info("Attempting extraction with ZipArchive");
                    $zip = new \ZipArchive();
                    $openResult = $zip->open($zipFile);
                    
                    if ($openResult !== true) {
                        Log::error("Failed to open zip file with ZipArchive. Error code: {$openResult}");
                    } else {
                        Log::info("ZipArchive opened successfully. Found " . $zip->numFiles . " files in archive");
                        
                        // Extract the zip file
                        $extractResult = $zip->extractTo($extractPath);
                        $zip->close();
                        
                        if ($extractResult) {
                            Log::info("ZipArchive extraction successful");
                            $extractionSuccess = true;
                        } else {
                            Log::error("ZipArchive extraction failed");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("ZipArchive extraction error: " . $e->getMessage());
                }
            } else {
                Log::warning("ZipArchive not available, will try alternative extraction methods");
            }
            
            // EXTRACTION METHOD 2: If ZipArchive failed, try PharData
            if (!$extractionSuccess && class_exists('PharData')) {
                try {
                    Log::info("Attempting extraction with PharData");
                    
                    // Temporarily rename zip to tar for PharData to work
                    $tempTarPath = $zipFile . '.tar';
                    if (copy($zipFile, $tempTarPath)) {
                        $archive = new \PharData($tempTarPath);
                        $archive->extractTo($extractPath, null, true); // Overwrite
                        unlink($tempTarPath);
                        
                        Log::info("PharData extraction appears successful");
                        $extractionSuccess = true;
                    } else {
                        Log::error("Failed to create temporary file for PharData extraction");
                    }
                } catch (\Exception $e) {
                    Log::error("PharData extraction error: " . $e->getMessage());
                }
            }
            
            // EXTRACTION METHOD 3: Try system unzip command
            if (!$extractionSuccess) {
                try {
                    Log::info("Attempting extraction with system unzip command");
                    
                    // Check if unzip command exists
                    $unzipExists = false;
                    
                    if (function_exists('exec')) {
                        $unzipExists = trim(exec('which unzip'));
                    }
                    
                    if ($unzipExists) {
                        $command = "unzip -o {$zipFile} -d {$extractPath} 2>&1";
                        Log::info("Running command: {$command}");
                        
                        $output = [];
                        $returnVar = 0;
                        exec($command, $output, $returnVar);
                        
                        Log::info("Unzip command output: " . implode("\n", $output));
                        
                        if ($returnVar === 0) {
                            Log::info("System unzip extraction successful");
                            $extractionSuccess = true;
                        } else {
                            Log::error("System unzip extraction failed with code {$returnVar}");
                        }
                    } else {
                        Log::warning("System unzip command not available");
                    }
                } catch (\Exception $e) {
                    Log::error("System unzip extraction error: " . $e->getMessage());
                }
            }
            
            if (!$extractionSuccess) {
                Log::error("All extraction methods failed. Cannot proceed with rollback.");
                return false;
            }
            
            // Verify extraction was successful by checking for files
            $extractedFiles = File::allFiles($extractPath);
            $extractedFileCount = count($extractedFiles);
            
            Log::info("Extracted file count: {$extractedFileCount}");
            
            if ($extractedFileCount === 0) {
                Log::error("Extraction appeared to succeed but no files were extracted");
                return false;
            }
            
            // Determine the root directory inside the zip (typically repo-version format)
            $directories = File::directories($extractPath);
            if (count($directories) === 0) {
                Log::error("No root directory found in extracted zip");
                return false;
            }
            
            $extractedRoot = $directories[0];
            Log::info("Extracted files to: {$extractedRoot}");
            
            // Verify the extracted directory has expected structure
            $isValidExtraction = false;
            
            // Check for key files/directories that should exist in any Laravel project
            $validationPaths = [
                'app',
                'bootstrap',
                'config',
                'database',
                'public',
                'routes',
                'composer.json'
            ];
            
            foreach ($validationPaths as $path) {
                if (file_exists($extractedRoot . '/' . $path)) {
                    $isValidExtraction = true;
                    break;
                }
            }
            
            if (!$isValidExtraction) {
                Log::error("Extracted directory does not appear to be a valid Laravel project");
                Log::error("Files in extracted root: " . implode(', ', array_map('basename', glob($extractedRoot . '/*'))));
                return false;
            }
            
            // Prepare list of directories to exclude from replacement
            $excludeDirectories = config('self-update.exclude_folders', []);
            Log::info("Excluding directories: " . implode(', ', $excludeDirectories));
            
            // Copy files from the extracted directory to the app root, skipping excluded directories
            $copySuccess = $this->copyDirectoryContents($extractedRoot, base_path(), $excludeDirectories);
            
            if (!$copySuccess) {
                Log::error("Failed to copy extracted files to application directory");
                return false;
            }
            
            // Clean up
            Log::info("Cleaning up temporary files");
            File::deleteDirectory($extractPath);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error downloading and restoring version {$version}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
} 