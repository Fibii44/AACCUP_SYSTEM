<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Service\Drive;

class TestDriveAccess extends Command
{
    protected $signature = 'drive:test';
    protected $description = 'Test Google Drive access and list folders';

    public function handle()
    {
        $drive = app(Drive::class);
        
        try {
            // List folders
            $this->info('Listing folders in Drive:');
            
            $optParams = [
                'q' => "mimeType='application/vnd.google-apps.folder'",
                'fields' => 'files(id, name, createdTime)',
                'orderBy' => 'createdTime desc'
            ];
            
            $results = $drive->files->listFiles($optParams);
            
            if (count($results->getFiles()) == 0) {
                $this->info('No folders found.');
            } else {
                foreach ($results->getFiles() as $file) {
                    $this->line("Folder: {$file->getName()} (ID: {$file->getId()})");
                }
            }
            
            // Get service account info
            $about = $drive->about->get(['fields' => 'user']);
            $this->info("\nService Account Email: " . $about->getUser()->getEmailAddress());
            
        } catch (\Exception $e) {
            $this->error('Error accessing Drive: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 