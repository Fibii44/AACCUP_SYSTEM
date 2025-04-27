<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\User;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InstrumentController extends Controller
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
                if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
                    try {
                        $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        
                        // Update admin's tokens
                        $admin->update([
                            'google_token' => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to refresh Google token', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Set scopes
                $client->setScopes([
                    'https://www.googleapis.com/auth/drive',
                    'https://www.googleapis.com/auth/drive.file',
                    'https://www.googleapis.com/auth/drive.metadata.readonly'
                ]);
                
                // Create Drive service with admin's credentials
                $this->driveService = new \Google\Service\Drive($client);
                
                \Log::info('Using admin OAuth credentials for Google Drive', [
                    'admin_email' => $admin->email,
                    'has_token' => !empty($admin->google_token),
                    'has_refresh_token' => !empty($admin->google_refresh_token)
                ]);
            } else {
                // Fallback to default client if no admin with tokens is found
                $client = app(\Google\Client::class);
                $this->driveService = app(\Google\Service\Drive::class);
                
                \Log::warning('No admin with Google tokens found, using default client');
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to initialize Google Client in InstrumentController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function index()
    {
        $instruments = Instrument::orderBy('order')->get();
        $users = User::where('role', '!=', 'admin')->get();
        
        return view('tenant.instruments.index', compact('instruments', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the instrument
            $instrument = new Instrument();
            $instrument->name = $request->name;
            // Set order using max order value
            $instrument->order = Instrument::max('order') + 1;
            $instrument->save();

            // Create Google Drive folder for the instrument
            $folderId = $this->createGoogleDriveFolder($instrument->name);
            
            if ($folderId) {
                $instrument->google_drive_folder_id = $folderId;
                $instrument->save();
            } else {
                \Log::warning('Failed to create Google Drive folder', [
                    'instrument' => $instrument->id
                ]);
            }

            DB::commit();

            // Check if request is AJAX/expecting JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Instrument created successfully',
                    'data' => $instrument,
                    'google_folder_created' => !empty($folderId)
                ]);
            }
            
            // For regular form submission, redirect with success message
            return redirect()->route('tenant.instruments.index')
                ->with('success', 'Instrument created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating instrument', [
                'error' => $e->getMessage()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create instrument: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create instrument: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Create a folder in Google Drive for the instrument
     *
     * @param string $folderName
     * @return string|null Folder ID if successful, null if failed
     */
    private function createGoogleDriveFolder($folderName)
    {
        try {
            // Get the admin user
            $admin = \App\Models\User::where('role', 'admin')->first();
            
            if (!$admin || empty($admin->google_token)) {
                \Log::warning('Admin user not found or has no Google OAuth tokens');
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
                    try {
                        $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        
                        // Update admin's tokens
                        $admin->update([
                            'google_token' => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in'])
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to refresh Google OAuth token: ' . $e->getMessage());
                        return null;
                    }
                } else {
                    \Log::error('OAuth token is expired and no refresh token available');
                    return null;
                }
            }
            
            // Create Drive service
            $driveService = new \Google\Service\Drive($client);
            
            // Create folder metadata
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            
            // Create the folder
            $folder = $driveService->files->create($folderMetadata, [
                'fields' => 'id, webViewLink'
            ]);
            
            // Add permissions - allow anyone with link to write to folder
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                    'allowFileDiscovery' => false
                ]);
                
                $driveService->permissions->create(
                    $folder->getId(),
                    $permission
                );
                
                \Log::info('Google Drive folder permissions set to allow anyone with the link to edit');
            } catch (\Exception $e) {
                \Log::warning('Failed to set permissions on Google Drive folder: ' . $e->getMessage());
                // Continue anyway, at least the folder was created
            }
            
            \Log::info('Google Drive folder created', [
                'name' => $folderName,
                'id' => $folder->getId()
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
                    \Log::error('Insufficient Google OAuth scopes. Admin should reauthorize with full permissions.', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            \Log::error('Google Drive API error: ' . $errorMessage, [
                'error' => $e->getMessage()
            ]);
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Error creating Google Drive folder', [
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instrument $instrument)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Store the old name for comparison
            $oldName = $instrument->name;
            
            // Update instrument name
            $instrument->name = $request->name;
            $instrument->save();
            
            // Only rename the folder if the name has changed
            if ($oldName !== $request->name && $instrument->google_drive_folder_id) {
                // Get the folder ID (handle both string and array formats)
                $folderId = is_array($instrument->google_drive_folder_id) ? 
                    $instrument->google_drive_folder_id['id'] : 
                    $instrument->google_drive_folder_id;
                    
                // Rename the Google Drive folder
                $renamed = $this->renameGoogleDriveFolder($folderId, $request->name);
                
                if (!$renamed) {
                    \Log::warning('Failed to rename Google Drive folder', [
                        'instrument_id' => $instrument->id,
                        'old_name' => $oldName,
                        'new_name' => $request->name
                    ]);
                } else {
                    \Log::info('Google Drive folder renamed successfully', [
                        'instrument_id' => $instrument->id,
                        'old_name' => $oldName,
                        'new_name' => $request->name
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instrument updated successfully',
                'data' => $instrument,
                'folder_renamed' => ($oldName !== $request->name && $instrument->google_drive_folder_id) ? true : null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating instrument', [
                'id' => $instrument->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update instrument: ' . $e->getMessage()
            ], 500);
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
            \Log::info('Renaming Google Drive folder', ['folderId' => $folderId, 'newName' => $newName]);
            
            // Use the class driveService property for consistency
            // Create new metadata with updated name
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $newName
            ]);
            
            // Update folder
            $this->driveService->files->update($folderId, $folderMetadata);
            
            \Log::info('Google Drive folder renamed successfully');
            return true;
        } catch (\Exception $e) {
            \Log::error('Error renaming Google Drive folder', [
                'folderId' => $folderId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function destroy(Instrument $instrument)
    {
        try {
            // Delete Google Drive folder if it exists
            if ($instrument->google_drive_folder_id) {
                try {
                    // Get the folder ID (handle both string and array formats)
                    $folderId = is_array($instrument->google_drive_folder_id) ? 
                        $instrument->google_drive_folder_id['id'] : 
                        $instrument->google_drive_folder_id;
                    
                    $this->driveService->files->delete($folderId);
                    \Log::info('Google Drive folder deleted', [
                        'instrument_id' => $instrument->id, 
                        'folder_id' => $folderId
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error deleting Google Drive folder', [
                        'instrument_id' => $instrument->id,
                        'folder_id' => $instrument->google_drive_folder_id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with instrument deletion even if folder deletion fails
                }
            }

            $instrument->delete();
            \Log::info('Instrument deleted successfully', ['id' => $instrument->id]);

            return response()->json([
                'success' => true,
                'message' => 'Instrument deleted successfully',
                'folder_deleted' => $instrument->google_drive_folder_id ? true : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting instrument', [
                'id' => $instrument->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting instrument: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for users
     */
    public function searchUsers(Request $request)
    {
        $term = $request->input('term');
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }
        
        $users = User::where('role', '!=', 'admin')
            ->where(function($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();
        
        return response()->json($users);
    }

    /**
     * Display the specified instrument with faculty users.
     *
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function show(Instrument $instrument)
    {
        // Load areas with eager loading
        $instrument->load('areas');
                
        // For each area, load its parameters and indicators
        foreach ($instrument->areas as $area) {
            $area->load(['parameters' => function($query) {
                $query->orderBy('order')->with(['indicators' => function($q) {
                    $q->orderBy('order');
                }]);
            }]);
        }
        
        return view('tenant.instruments.show', compact('instrument'));
    }

    /**
     * Display the specified area within an instrument.
     */
    public function showArea(Instrument $instrument, $areaId)
    {
        // Find the area or fail with a 404 error
        $area = $instrument->areas()->findOrFail($areaId);
        
        // Load parameters with eager loading of their indicators, ordered by 'order'
        $area->load(['parameters' => function($query) {
            $query->orderBy('order')->with(['indicators' => function($q) {
                $q->orderBy('order');
            }]);
        }]);
        
        // Return the view with the instrument and area data
        return view('tenant.instruments.areas.index', compact('instrument', 'area'));
    }
} 