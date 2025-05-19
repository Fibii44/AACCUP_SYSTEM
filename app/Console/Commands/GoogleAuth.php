<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;

class GoogleAuth extends Command
{
    protected $signature = 'google:auth';
    protected $description = 'Authenticate with Google OAuth';

    public function handle()
    {
        if (config('google.auth_type') !== 'oauth') {
            $this->error('OAuth is not configured. Please set GOOGLE_AUTH_TYPE=oauth in your .env file.');
            return 1;
        }

        $client = app(Client::class);
        
        // Generate auth URL
        $authUrl = $client->createAuthUrl();
        
        $this->info('Please visit this URL to authorize the application:');
        $this->line($authUrl);
        
        $this->info("\nAfter authorization, please enter the code you received:");
        $authCode = $this->ask('Enter the authorization code');
        
        try {
            // Exchange the code for tokens
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            // Save the tokens
            $tokenPath = storage_path('app/google/token.json');
            if (!is_dir(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->info('Authentication successful! Tokens have been saved.');
            
        } catch (\Exception $e) {
            $this->error('Authentication failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 