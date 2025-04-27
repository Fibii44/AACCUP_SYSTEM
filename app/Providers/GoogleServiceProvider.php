<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Auth;

class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            $client = new Client();
            
            if (config('google.auth_type') === 'service_account') {
                // Service Account Authentication
                $credentialsPath = base_path(config('google.credentials_path'));
                
                if (file_exists($credentialsPath)) {
                    $client->setAuthConfig($credentialsPath);
                } else {
                    \Log::warning("Google credentials file not found at: " . $credentialsPath);
                }
            } else {
                // OAuth Authentication
                $client->setClientId(config('google.client_id'));
                $client->setClientSecret(config('google.client_secret'));
                $client->setRedirectUri(config('google.redirect_uri'));
                
                // Get the authenticated user's tokens
                if (Auth::check()) {
                    $user = Auth::user();
                    $accessToken = [
                        'access_token' => $user->google_token,
                        'refresh_token' => $user->google_refresh_token,
                        'expires_in' => 3600, // Default expiration time
                        'created' => $user->google_token_expires_at ? strtotime($user->google_token_expires_at) - 3600 : time()
                    ];
                    
                    $client->setAccessToken($accessToken);
                    
                    // Refresh token if expired
                    if ($client->isAccessTokenExpired()) {
                        if ($client->getRefreshToken()) {
                            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                            
                            // Update user's tokens
                            $user->update([
                                'google_token' => $newToken['access_token'],
                                'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                            ]);
                        }
                    }
                }
            }
            
            $client->setApplicationName(config('google.application_name'));
            $client->setScopes(config('google.scopes'));
            
            return $client;
        });

        $this->app->singleton(Drive::class, function ($app) {
            return new Drive($app->make(Client::class));
        });

        $this->app->singleton(Sheets::class, function ($app) {
            return new Sheets($app->make(Client::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 