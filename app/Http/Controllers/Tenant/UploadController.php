<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\Upload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UploadController extends Controller
{
    protected $drive;
    protected $adminUser;
    
    public function __construct()
    {
        try {
            // Get authenticated user's Google Drive credentials
            $user = Auth::user();
            Log::info('Initializing Google Drive service for user: ' . ($user ? $user->id : 'Guest'));
            
            if ($user && $user->google_token) {
                Log::info('Using user\'s own Google credentials');
                
                $client = new \Google_Client();
                $client->setClientId(config('services.google.client_id'));
                $client->setClientSecret(config('services.google.client_secret'));
                $client->setScopes([\Google_Service_Drive::DRIVE, \Google_Service_Drive::DRIVE_FILE]);
                
                $accessToken = [
                    'access_token' => $user->google_token,
                    'expires_in' => $user->google_token_expires_at ? now()->diffInSeconds($user->google_token_expires_at) : 3600
                ];
                
                if ($user->google_refresh_token) {
                    $accessToken['refresh_token'] = $user->google_refresh_token;
                }
                
                $client->setAccessToken($accessToken);
                
                // Refresh token if expired
                if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
                    Log::info('Refreshing user\'s Google token');
                    $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    $newToken = $client->getAccessToken();
                    
                    // Update the token in the database
                    $user->update([
                        'google_token' => $newToken['access_token'],
                        'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                    ]);
                }
                
                $this->drive = new Drive($client);
                $this->adminUser = null; // Not using admin user in this case
            } else {
                // No valid user token, try to get admin credentials
                Log::info('User has no Google credentials, trying admin user');
                
                $this->adminUser = \App\Models\User::where('role', 'admin')->first();
                
                if ($this->adminUser && $this->adminUser->google_token) {
                    Log::info('Using admin\'s Google credentials');
                    
                    $client = new \Google_Client();
                    $client->setClientId(config('services.google.client_id'));
                    $client->setClientSecret(config('services.google.client_secret'));
                    $client->setScopes([\Google_Service_Drive::DRIVE, \Google_Service_Drive::DRIVE_FILE]);
                    
                    $accessToken = [
                        'access_token' => $this->adminUser->google_token,
                        'expires_in' => $this->adminUser->google_token_expires_at ? now()->diffInSeconds($this->adminUser->google_token_expires_at) : 3600
                    ];
                    
                    if ($this->adminUser->google_refresh_token) {
                        $accessToken['refresh_token'] = $this->adminUser->google_refresh_token;
                    }
                    
                    $client->setAccessToken($accessToken);
                    
                    // Refresh token if expired
                    if ($client->isAccessTokenExpired() && $this->adminUser->google_refresh_token) {
                        Log::info('Refreshing admin\'s Google token');
                        $client->fetchAccessTokenWithRefreshToken($this->adminUser->google_refresh_token);
                        $newToken = $client->getAccessToken();
                        
                        // Update the token in the database
                        $this->adminUser->update([
                            'google_token' => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                        ]);
                    }
                    
                    $this->drive = new Drive($client);
                } else {
                    Log::error('No valid Google credentials found for any user');
                    $this->drive = null;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Drive: ' . $e->getMessage());
            $this->drive = null;
        }
    }
    
    /**
     * Store uploads for an indicator.
     */
    public function store(Request $request, Indicator $indicator)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'files' => 'required|array',
            'files.*' => 'required|file|max:40000', // 40MB max per file to match server limits
        ]);
        
        $uploads = [];
        $errors = [];
        $gdrive_warnings = [];
        
        // Check if Google Drive folder exists for the indicator
        if (!$indicator->google_drive_folder_id) {
            // Create folder if it doesn't exist
            try {
                $parameter = $indicator->parameter;
                if ($parameter && $parameter->google_drive_folder_id) {
                    // Get the admin user for Google Drive operations
                    $adminUser = User::where('role', 'admin')->first();
                    
                    if (!$adminUser) {
                        Log::error('No admin user found for Google Drive operations');
                        throw new \Exception("No admin user available to create Google Drive folder");
                    }
                    
                    if (!$adminUser->google_token) {
                        Log::error('Admin user does not have Google token');
                        throw new \Exception("Admin user is not authenticated with Google Drive");
                    }
                    
                    // Initialize Google Drive service
                    $client = new \Google_Client();
                    $client->setAccessToken($adminUser->google_token);
                    $drive = new Drive($client);
                    
                    // Create folder within the parameter's folder
                    $fileMetadata = new DriveFile([
                        'name' => $indicator->name,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [$parameter->google_drive_folder_id]
                    ]);
                    
                    $folder = $drive->files->create($fileMetadata, [
                        'fields' => 'id'
                    ]);
                    
                    // Add permissions - allow anyone with link to write to folder
                    $permission = new Permission([
                        'type' => 'anyone',
                        'role' => 'writer',
                        'allowFileDiscovery' => false
                    ]);
                    
                    $drive->permissions->create(
                        $folder->getId(),
                        $permission
                    );
                    
                    // Update indicator with new folder ID
                    $indicator->google_drive_folder_id = $folder->getId();
                    $indicator->save();
                    
                    Log::info('Created Google Drive folder for indicator during upload: ' . $indicator->name);
                }
            } catch (\Exception $e) {
                Log::error('Failed to create Google Drive folder: ' . $e->getMessage());
                $gdrive_warnings[] = "Failed to create Google Drive folder: " . $e->getMessage();
            }
        }
        
        // Process each uploaded file
        foreach($request->file('files') as $file) {
            try {
                // Check file size individually
                if ($file->getSize() > 40 * 1024 * 1024) { // 40MB in bytes
                    throw new \Exception("File size exceeds the maximum limit of 40MB");
                }
                
                // Generate a unique name for the file
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Create an upload record (will be completed after upload)
                $upload = new Upload([
                    'indicator_id' => $indicator->id,
                    'user_id' => Auth::id(),
                    'description' => $validated['description'] ?? $file->getClientOriginalName(), // Use file name as description if not provided
                    'file_name' => $fileName,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
                
                // Try to upload to Google Drive first
                $googleDriveSuccess = false;
                
                if ($this->drive && $indicator->google_drive_folder_id) {
                    try {
                        // Verify the folder exists before attempting to upload
                        if (!$this->validateGoogleDriveFolder($indicator->google_drive_folder_id)) {
                            throw new \Exception("Google Drive folder not found or access denied. The folder may have been deleted.");
                        }
                        
                        // Create file metadata
                        $fileMetadata = new DriveFile([
                            'name' => $fileName,
                            'parents' => [$indicator->google_drive_folder_id]
                        ]);
                        
                        // Get file content
                        $content = file_get_contents($file->getRealPath());
                        
                        // Upload file to Google Drive
                        $driveFile = $this->drive->files->create($fileMetadata, [
                            'data' => $content,
                            'mimeType' => $file->getMimeType(),
                            'uploadType' => 'multipart',
                            'fields' => 'id, webViewLink'
                        ]);
                        
                        if ($driveFile && $driveFile->getId()) {
                            $upload->google_drive_file_id = $driveFile->getId();
                            // Store the web view link for direct access
                            $upload->file_path = $driveFile->getWebViewLink();
                            $googleDriveSuccess = true;
                            
                            // Add a comment to track who uploaded the file
                            try {
                                $this->drive->comments->create(
                                    $driveFile->getId(),
                                    new \Google\Service\Drive\Comment([
                                        'content' => "Uploaded by " . Auth::user()->name . " (" . Auth::user()->email . ") via AACCUP System"
                                    ])
                                );
                            } catch (\Exception $e) {
                                Log::warning("Could not add comment to file: " . $e->getMessage());
                                // Continue anyway
                            }
                            
                            // If admin uploaded on behalf of user, add a property to track who it was for
                            if ($this->adminUser) {
                                try {
                                    $properties = [
                                        'uploadedBy' => Auth::user()->name,
                                        'uploadedForEmail' => Auth::user()->email,
                                        'uploadTime' => now()->toDateTimeString()
                                    ];
                                    
                                    $this->drive->files->update(
                                        $driveFile->getId(),
                                        new DriveFile(['properties' => $properties])
                                    );
                                } catch (\Exception $e) {
                                    Log::warning("Could not add properties to file: " . $e->getMessage());
                                    // Continue anyway
                                }
                            }
                        } else {
                            throw new \Exception("Failed to get file ID from Google Drive");
                        }
                    } catch (\Exception $e) {
                        Log::error("Google Drive upload failed: " . $e->getMessage());
                        throw new \Exception("Google Drive upload failed: " . $e->getMessage());
                    }
                } else {
                    if (!$this->drive) {
                        throw new \Exception("Google Drive service not available. Please check your Google credentials.");
                    } else if (!$indicator->google_drive_folder_id) {
                        throw new \Exception("No Google Drive folder exists for this indicator.");
                    }
                }
                
                // Only if Google Drive upload was successful, save the upload record
                if ($googleDriveSuccess) {
                    $upload->save();
                    $uploads[] = $upload;
                }
            } catch (\Exception $e) {
                Log::error('Failed to upload file: ' . $e->getMessage());
                
                // As a fallback, try to store it locally if Google Drive upload failed
                try {
                    // Store locally as fallback
                    $filePath = $file->storeAs('uploads/indicators/' . $indicator->id, $fileName, 'public');
                    $fullUrl = Storage::url($filePath);
                    
                    $upload = new Upload([
                        'indicator_id' => $indicator->id,
                        'user_id' => Auth::id(),
                        'description' => $validated['description'] ?? $file->getClientOriginalName(),
                        'file_name' => $fileName,
                        'file_path' => $fullUrl,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                    
                    $upload->save();
                    $uploads[] = $upload;
                    
                    $gdrive_warnings[] = "File '{$file->getClientOriginalName()}' was saved locally but failed to upload to Google Drive. Error: " . $e->getMessage();
                } catch (\Exception $localError) {
                    $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage() . ' (Local fallback failed: ' . $localError->getMessage() . ')';
                }
            }
        }
        
        $responseStatus = 201;
        $responseMessage = 'Files uploaded successfully';
        
        if (count($errors) > 0 && count($uploads) > 0) {
            $responseStatus = 207; // 207 Multi-Status
            $responseMessage = 'Some files could not be uploaded';
        } else if (count($errors) > 0) {
            $responseStatus = 400;
            $responseMessage = 'Failed to upload files';
        } else if (count($gdrive_warnings) > 0) {
            $responseStatus = 207;
            $responseMessage = 'Files uploaded but with Google Drive warnings';
        }
        
        return response()->json([
            'message' => $responseMessage,
            'uploads' => $uploads,
            'errors' => $errors,
            'gdrive_warnings' => $gdrive_warnings
        ], $responseStatus);
    }
    
    /**
     * Get all uploads for an indicator.
     */
    public function index(Indicator $indicator)
    {
        $uploads = $indicator->uploads()->with(['user:id,name,email,avatar'])->latest()->get();
        
        return response()->json([
            'uploads' => $uploads
        ]);
    }
    
    /**
     * Validate that a Google Drive folder exists and has proper permissions.
     * If not, attempt to fix permissions and verify existence.
     *
     * @param string $folderId 
     * @return bool
     */
    private function validateGoogleDriveFolder($folderId)
    {
        if (!$this->drive) {
            Log::warning('Google Drive service is not initialized');
            return false;
        }
        
        try {
            // Check if folder exists
            $folder = $this->drive->files->get($folderId);
            
            // If using admin user, check and update permissions if needed
            if ($this->adminUser) {
                $permissions = $this->drive->permissions->listPermissions($folderId);
                $needsSharing = true;
                
                foreach ($permissions as $permission) {
                    if ($permission->getType() === 'anyone' && 
                        ($permission->getRole() === 'writer' || $permission->getRole() === 'organizer')) {
                        $needsSharing = false;
                        break;
                    }
                }
                
                if ($needsSharing) {
                    $this->drive->permissions->create(
                        $folderId,
                        new Permission([
                            'type' => 'anyone',
                            'role' => 'writer',
                            'allowFileDiscovery' => false
                        ])
                    );
                    Log::info("Updated folder permissions to allow anyone with the link to edit");
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Google Drive folder validation failed: " . $e->getMessage(), [
                'folder_id' => $folderId
            ]);
            return false;
        }
    }
} 