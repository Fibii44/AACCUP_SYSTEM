<?php

require __DIR__.'/vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get configuration from .env
$repoVendor = $_ENV['SELF_UPDATER_REPO_VENDOR'] ?? 'Fibii44'; 
$repoName = $_ENV['SELF_UPDATER_REPO_NAME'] ?? 'AACCUP_SYSTEM';
$version = 'v1.0.4'; // The version we're testing
$downloadPath = $_ENV['SELF_UPDATER_DOWNLOAD_PATH'] ?? 'storage/app/github-releases';
$token = $_ENV['SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN'] ?? null;

echo "Testing GitHub download for {$repoVendor}/{$repoName} version {$version}\n";
echo "Download path: {$downloadPath}\n";
echo "GitHub token: " . ($token ? "Configured" : "Not configured") . "\n\n";

// Create a download path if it doesn't exist
if (!file_exists($downloadPath)) {
    mkdir($downloadPath, 0755, true);
    echo "Created download directory: {$downloadPath}\n";
}

// Initialize Guzzle HTTP client
$client = new GuzzleHttp\Client();

try {
    // Step 1: Try to get repository information
    echo "Step 1: Checking repository access...\n";
    $repoUrl = "https://api.github.com/repos/{$repoVendor}/{$repoName}";
    $headers = [
        'User-Agent' => 'AACCUP_System_Updater',
    ];
    
    if ($token) {
        $headers['Authorization'] = "token {$token}";
    }
    
    $response = $client->get($repoUrl, ['headers' => $headers]);
    $repoData = json_decode($response->getBody()->getContents(), true);
    echo "Repository information retrieved successfully!\n";
    echo "Repository: {$repoData['full_name']}\n";
    echo "Default branch: {$repoData['default_branch']}\n\n";
    
    // Step 2: Get release information
    echo "Step 2: Checking release information...\n";
    $releaseUrl = "https://api.github.com/repos/{$repoVendor}/{$repoName}/releases/tags/{$version}";
    $response = $client->get($releaseUrl, ['headers' => $headers]);
    $releaseData = json_decode($response->getBody()->getContents(), true);
    
    echo "Release found: {$releaseData['name']}\n";
    echo "Created at: {$releaseData['created_at']}\n";
    
    if (isset($releaseData['assets']) && count($releaseData['assets']) > 0) {
        echo "Release has " . count($releaseData['assets']) . " assets\n";
        foreach ($releaseData['assets'] as $index => $asset) {
            echo "Asset " . ($index + 1) . ": {$asset['name']} ({$asset['content_type']}, {$asset['size']} bytes)\n";
        }
    } else {
        echo "Release has no custom assets, will use source code archives\n";
    }
    
    // Step 3: Try to download the zipball
    echo "\nStep 3: Downloading zipball...\n";
    $zipballUrl = $releaseData['zipball_url'];
    echo "Zipball URL: {$zipballUrl}\n";
    
    $zipFile = $downloadPath . '/' . $version . '.zip';
    echo "Saving to: {$zipFile}\n";
    
    $response = $client->get($zipballUrl, [
        'headers' => $headers,
        'sink' => $zipFile
    ]);
    
    if (file_exists($zipFile)) {
        $fileSize = filesize($zipFile);
        echo "Download successful! File size: {$fileSize} bytes\n";
    } else {
        echo "Download failed: File not found after download attempt\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    if ($e instanceof GuzzleHttp\Exception\ClientException) {
        $response = $e->getResponse();
        $errorBody = $response->getBody()->getContents();
        echo "HTTP Status: " . $response->getStatusCode() . "\n";
        echo "Error details: " . $errorBody . "\n";
    }
} 