<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\Indicator;
use App\Models\User;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndicatorController extends Controller
{
    protected $drive;
    protected $adminUser;

    public function __construct()
    {
        try {
            // Get the admin user for Google Drive operations
            $this->adminUser = User::where('role', 'admin')->first();
            
            if (!$this->adminUser) {
                Log::error('IndicatorController: No admin user found for Google Drive operations');
                return;
            }

            // Check if admin has a valid token
            if ($this->adminUser->google_token && $this->adminUser->google_token_expires_at) {
                $expiresAt = $this->adminUser->google_token_expires_at;
                
                // If token is expired and we have refresh token, refresh it
                if (now() >= $expiresAt && $this->adminUser->google_refresh_token) {
                    $client = new \Google_Client();
                    $client->setClientId(config('services.google.client_id'));
                    $client->setClientSecret(config('services.google.client_secret'));
                    $client->refreshToken($this->adminUser->google_refresh_token);
                    $newToken = $client->getAccessToken();
                    
                    // Update the token in the database
                    $this->adminUser->update([
                        'google_token' => $newToken['access_token'],
                        'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                    ]);
                } else if (now() >= $expiresAt && !$this->adminUser->google_refresh_token) {
                    Log::error('IndicatorController: Google token expired and no refresh token available for admin user');
                    return;
                }
                
                // Initialize Google Drive service
                $client = new \Google_Client();
                $client->setAccessToken($this->adminUser->google_token);
                $this->drive = new Drive($client);
            } else {
                Log::error('IndicatorController: Admin user does not have Google credentials');
            }
        } catch (\Exception $e) {
            Log::error('IndicatorController initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the indicators for the specified parameter.
     */
    public function index(Parameter $parameter)
    {
        $indicators = $parameter->indicators()->orderBy('order', 'asc')->get();
        return response()->json($indicators);
    }

    /**
     * Display the specified indicator.
     */
    public function show(Indicator $indicator)
    {
        return response()->json($indicator);
    }

    /**
     * Store a newly created indicator.
     */
    public function store(Request $request, Parameter $parameter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $indicator = $parameter->indicators()->create($validated);
        
        // Create Google Drive folder for the indicator
        if ($this->drive && $parameter->google_drive_folder_id) {
            try {
                $folderId = $this->createGoogleDriveFolder($indicator, $parameter);
                if ($folderId) {
                    $indicator->update(['google_drive_folder_id' => $folderId]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create Google Drive folder for indicator: ' . $e->getMessage());
            }
        }
        
        return response()->json($indicator, 201);
    }

    /**
     * Update the specified indicator.
     */
    public function update(Request $request, Indicator $indicator)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        // Check if name is changed to update Google Drive folder
        if (isset($validated['name']) && $validated['name'] !== $indicator->name) {
            $oldName = $indicator->name;
            $newName = $validated['name'];
            
            // Rename Google Drive folder if it exists
            if ($this->drive && $indicator->google_drive_folder_id) {
                try {
                    $this->renameGoogleDriveFolder($indicator, $newName);
                } catch (\Exception $e) {
                    Log::warning('Failed to rename Google Drive folder for indicator: ' . $e->getMessage());
                }
            }
        }

        $indicator->update($validated);
        
        return response()->json($indicator);
    }

    /**
     * Remove the specified indicator.
     */
    public function destroy(Indicator $indicator)
    {
        // Delete Google Drive folder if it exists
        if ($this->drive && $indicator->google_drive_folder_id) {
            try {
                $this->drive->files->delete($indicator->google_drive_folder_id);
                Log::info('Deleted Google Drive folder for indicator: ' . $indicator->name);
            } catch (\Exception $e) {
                Log::warning('Failed to delete Google Drive folder for indicator: ' . $e->getMessage());
            }
        }

        $indicator->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Update the order of multiple indicators.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'indicators' => 'required|array',
            'indicators.*.id' => 'required|exists:indicators,id',
            'indicators.*.order' => 'required|integer',
        ]);

        foreach ($validated['indicators'] as $indicatorData) {
            Indicator::where('id', $indicatorData['id'])->update(['order' => $indicatorData['order']]);
        }

        return response()->json(['message' => 'Indicator order updated successfully']);
    }

    /**
     * Create a Google Drive folder for the indicator.
     */
    private function createGoogleDriveFolder(Indicator $indicator, Parameter $parameter)
    {
        try {
            if (!$this->adminUser || !$this->adminUser->google_token) {
                Log::warning('Cannot create Google Drive folder: No valid Google token available');
                return null;
            }
            
            // Create folder within the parameter's folder
            $fileMetadata = new DriveFile([
                'name' => $indicator->name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parameter->google_drive_folder_id]
            ]);
            
            $folder = $this->drive->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
            
            // Add permissions - allow anyone with link to write to folder
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                    'allowFileDiscovery' => false
                ]);
                
                $this->drive->permissions->create(
                    $folder->getId(),
                    $permission
                );
                
                Log::info('Google Drive folder permissions set to allow anyone with the link to edit');
            } catch (\Exception $e) {
                Log::warning('Failed to set permissions on Google Drive folder: ' . $e->getMessage());
                // Continue anyway, at least the folder was created
            }
            
            Log::info('Created Google Drive folder for indicator: ' . $indicator->name);
            return $folder->getId();
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() == 401) {
                Log::error('Google token expired while creating folder. Please re-authenticate.');
            } else {
                Log::error('Google API error while creating folder: ' . $e->getMessage());
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error creating Google Drive folder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rename a Google Drive folder for the indicator.
     */
    private function renameGoogleDriveFolder(Indicator $indicator, string $newName)
    {
        try {
            if (!$this->adminUser || !$this->adminUser->google_token) {
                Log::warning('Cannot rename Google Drive folder: No valid Google token available');
                return false;
            }
            
            $fileMetadata = new DriveFile([
                'name' => $newName
            ]);
            
            $this->drive->files->update($indicator->google_drive_folder_id, $fileMetadata);
            Log::info('Renamed Google Drive folder for indicator from: ' . $indicator->name . ' to: ' . $newName);
            return true;
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() == 401) {
                Log::error('Google token expired while renaming folder. Please re-authenticate.');
            } else {
                Log::error('Google API error while renaming folder: ' . $e->getMessage());
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error renaming Google Drive folder: ' . $e->getMessage());
            return false;
        }
    }
} 