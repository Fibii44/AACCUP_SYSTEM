<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class CreateTestFolder extends Command
{
    protected $signature = 'drive:create-folder {name} {--email=}';
    protected $description = 'Create a test folder in Google Drive and share it';

    public function handle()
    {
        $drive = app(Drive::class);
        $folderName = $this->argument('name');
        $email = $this->option('email') ?? env('GOOGLE_ADMIN_EMAIL');
        
        if (!$email) {
            $this->error('Please provide an email address to share the folder with using --email option or set GOOGLE_ADMIN_EMAIL in .env');
            return 1;
        }
        
        try {
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $drive->files->create($folderMetadata, [
                'fields' => 'id, name, webViewLink'
            ]);

            // Share the folder
            $permission = new Permission([
                'type' => 'user',
                'role' => 'writer',
                'emailAddress' => $email
            ]);

            $drive->permissions->create($folder->getId(), $permission, [
                'sendNotificationEmail' => false
            ]);

            $this->info("Folder created successfully!");
            $this->line("Name: {$folder->getName()}");
            $this->line("ID: {$folder->getId()}");
            $this->line("Link: {$folder->getWebViewLink()}");
            $this->line("Shared with: {$email}");
            
        } catch (\Exception $e) {
            $this->error('Error creating folder: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 