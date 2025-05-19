<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Google API services.
    |
    */

    'auth_type' => env('GOOGLE_AUTH_TYPE', 'oauth'), // 'oauth' or 'service_account'
    
    // Service Account Configuration
    'credentials_path' => env('GOOGLE_CREDENTIALS_PATH', 'credentials/credentials.json'),
    
    // OAuth Configuration
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    'admin_email' => env('GOOGLE_ADMIN_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Define the scopes required for your application.
    | Common scopes:
    | - https://www.googleapis.com/auth/drive (Full Drive access)
    | - https://www.googleapis.com/auth/drive.file (Access to files created by the app)
    | - https://www.googleapis.com/auth/spreadsheets (Google Sheets access)
    |
    */
    'scopes' => [
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/drive.appdata',
        'https://www.googleapis.com/auth/spreadsheets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The name of your application as it will appear in Google's OAuth consent screen.
    |
    */
    'application_name' => env('APP_NAME', 'AACCUP System'),
]; 