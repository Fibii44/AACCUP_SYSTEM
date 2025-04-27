<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Google\Service\Drive;

class AreaController extends Controller
{
    private $driveService;

    public function __construct()
    {
        try {
            // Get the admin user to use their OAuth credentials
            $admin = \App\Models\User::where('role', 'admin')->first();
            
            if ($admin && $admin->google_token) {
                $client = new \Google\Client();
                $client->setApplicationName(config('google.application_name'));
                
                // Set the admin's OAuth credentials
                $accessToken = [
                    'access_token' => $admin->google_token,
                    'refresh_token' => $admin->google_refresh_token,
                    'expires_in' => 3600,
                    'created' => $admin->google_token_expires_at ? strtotime($admin->google_token_expires_at) - 3600 : time()
                ];
                
                $client->setAccessToken($accessToken);
                
                // Refresh token if expired
                if ($client->isAccessTokenExpired()) {
                    if ($client->getRefreshToken()) {
                        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        
                        // Update the admin's token in the database
                        $admin->google_token = $client->getAccessToken()['access_token'];
                        $admin->google_token_expires_at = date('Y-m-d H:i:s', time() + $client->getAccessToken()['expires_in']);
                        $admin->save();
                    } else {
                        Log::error('No refresh token available, admin needs to reauthorize the app');
                    }
                }
                
                $this->driveService = new Drive($client);
            } else {
                Log::warning('Admin user not found or has no Google OAuth tokens in AreaController');
            }
        } catch (\Exception $e) {
            Log::error('Error setting up Google Drive service in AreaController', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of the areas for the specified instrument.
     */
    public function index(Instrument $instrument)
    {
        $areas = $instrument->areas()->orderBy('order', 'asc')->get();
        return response()->json($areas);
    }

    /**
     * Display the specified area.
     */
    public function show(Area $area)
    {
        return response()->json($area->load('parameters'));
    }

    /**
     * Store a newly created area.
     */
    public function store(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // Create the area
            $area = $instrument->areas()->create($validated);

            // Create Google Drive folder for the area inside the instrument folder
            if ($instrument->google_drive_folder_id) {
                $instrumentFolderId = is_array($instrument->google_drive_folder_id) ? 
                    $instrument->google_drive_folder_id['id'] : 
                    $instrument->google_drive_folder_id;
                
                $folderId = $this->createGoogleDriveFolder($area->name, $instrumentFolderId);
                
                if ($folderId) {
                    $area->google_drive_folder_id = $folderId;
                    $area->save();
                } else {
                    Log::warning('Failed to create Google Drive folder for area', [
                        'area_id' => $area->id,
                        'instrument_id' => $instrument->id
                    ]);
                }
            }

            DB::commit();
            
            // Check if the request is AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json($area, 201);
            }
            
            // For regular form submissions, redirect back to the instrument show page
            return redirect()->route('tenant.instruments.show', $instrument->id)
                ->with('success', 'Area created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating area', [
                'error' => $e->getMessage()
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create area: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create area: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified area.
     */
    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // Store the old name for comparison
            $oldName = $area->name;
            
            // Update area
            $area->update($validated);
            
            // Only rename the folder if the name has changed and folder exists
            if ($oldName !== $request->name && $area->google_drive_folder_id) {
                // Rename the Google Drive folder
                $renamed = $this->renameGoogleDriveFolder($area->google_drive_folder_id, $request->name);
                
                if (!$renamed) {
                    Log::warning('Failed to rename Google Drive folder for area', [
                        'area_id' => $area->id,
                        'old_name' => $oldName,
                        'new_name' => $request->name
                    ]);
                }
            }

            DB::commit();
            
            // Check if the request is AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json($area);
            }
            
            // For regular form submissions, redirect back to the instrument show page
            return redirect()->route('tenant.instruments.show', $area->instrument_id)
                ->with('success', 'Area updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating area', [
                'id' => $area->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to update area: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update area: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified area.
     */
    public function destroy(Area $area)
    {
        $instrumentId = $area->instrument_id;
        
        try {
            // Delete Google Drive folder if it exists
            if ($area->google_drive_folder_id) {
                try {
                    $this->driveService->files->delete($area->google_drive_folder_id);
                    Log::info('Google Drive folder deleted for area', [
                        'area_id' => $area->id, 
                        'folder_id' => $area->google_drive_folder_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting Google Drive folder for area', [
                        'area_id' => $area->id,
                        'folder_id' => $area->google_drive_folder_id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with area deletion even if folder deletion fails
                }
            }

            $area->delete();
            
            // Check if the request is AJAX
            if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(null, 204);
            }
            
            // For regular form submissions, redirect back to the instrument show page
            return redirect()->route('tenant.instruments.show', $instrumentId)
                ->with('success', 'Area deleted successfully');
                
        } catch (\Exception $e) {
            Log::error('Error deleting area', [
                'id' => $area->id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting area: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting area: ' . $e->getMessage());
        }
    }

    /**
     * Update the order of multiple areas.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'areas' => 'required|array',
            'areas.*.id' => 'required|exists:areas,id',
            'areas.*.order' => 'required|integer',
        ]);

        foreach ($validated['areas'] as $areaData) {
            Area::where('id', $areaData['id'])->update(['order' => $areaData['order']]);
        }

        return response()->json(['message' => 'Area order updated successfully']);
    }

    /**
     * Create a folder in Google Drive for the area inside the parent instrument folder
     *
     * @param string $folderName
     * @param string $parentFolderId
     * @return string|null Folder ID if successful, null if failed
     */
    private function createGoogleDriveFolder($folderName, $parentFolderId)
    {
        try {
            // Get the admin user
            $admin = \App\Models\User::where('role', 'admin')->first();
            
            if (!$admin || empty($admin->google_token)) {
                Log::warning('Admin user not found or has no Google OAuth tokens');
                return null;
            }
            
            // Create a new client using admin's OAuth token
            $client = new \Google\Client();
            $client->setApplicationName(config('google.application_name'));
            
            // Set required scopes
            $client->setScopes([
                'https://www.googleapis.com/auth/drive',
                'https://www.googleapis.com/auth/drive.file'
            ]);
            
            // Set admin's OAuth credentials
            $accessToken = [
                'access_token' => $admin->google_token,
                'refresh_token' => $admin->google_refresh_token,
                'expires_in' => 3600,
                'created' => $admin->google_token_expires_at ? strtotime($admin->google_token_expires_at) - 3600 : time()
            ];
            
            $client->setAccessToken($accessToken);
            
            // Check if token is expired
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    
                    // Update the admin's token in the database
                    $admin->google_token = $client->getAccessToken()['access_token'];
                    $admin->google_token_expires_at = date('Y-m-d H:i:s', time() + $client->getAccessToken()['expires_in']);
                    $admin->save();
                } else {
                    Log::error('No refresh token available, admin needs to reauthorize the app');
                    return null;
                }
            }
            
            $driveService = new Drive($client);
            
            // Create folder metadata
            $folderMetadata = new Drive\DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId]
            ]);
            
            // Create folder
            $folder = $driveService->files->create($folderMetadata, [
                'fields' => 'id'
            ]);
            
            // Add permissions - allow anyone with link to write to folder
            try {
                $permission = new Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                    'allowFileDiscovery' => false
                ]);
                
                $driveService->permissions->create(
                    $folder->getId(),
                    $permission
                );
                
                Log::info('Google Drive folder permissions set to allow anyone with the link to edit');
            } catch (\Exception $e) {
                Log::warning('Failed to set permissions on Google Drive folder: ' . $e->getMessage());
                // Continue anyway, at least the folder was created
            }
            
            Log::info('Google Drive folder created for area', [
                'folder_name' => $folderName,
                'folder_id' => $folder->getId(),
                'parent_folder_id' => $parentFolderId
            ]);
            
            return $folder->getId();
        } catch (\Google\Service\Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            $errorMessage = 'Unknown Google API error';
            
            if (isset($errors['error']['status'])) {
                $errorMessage = $errors['error']['status'];
                
                if ($errorMessage === 'PERMISSION_DENIED' && 
                    isset($errors['error']['details'][0]['reason']) && 
                    $errors['error']['details'][0]['reason'] === 'ACCESS_TOKEN_SCOPE_INSUFFICIENT') {
                    Log::error('Insufficient Google OAuth scopes. Admin should reauthorize with full permissions.', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::error('Google Drive API error: ' . $errorMessage, [
                'error' => $e->getMessage()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error creating Google Drive folder for area', [
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Rename a Google Drive folder
     *
     * @param string $folderId
     * @param string $newName
     * @return bool Success status
     */
    private function renameGoogleDriveFolder($folderId, $newName)
    {
        try {
            Log::info('Renaming Google Drive folder for area', ['folderId' => $folderId, 'newName' => $newName]);
            
            // Create new metadata with updated name
            $folderMetadata = new Drive\DriveFile([
                'name' => $newName
            ]);
            
            // Update folder
            $this->driveService->files->update($folderId, $folderMetadata);
            
            Log::info('Google Drive folder renamed successfully for area');
            return true;
        } catch (\Exception $e) {
            Log::error('Error renaming Google Drive folder for area', [
                'folderId' => $folderId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
} 