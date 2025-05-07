<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\User;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParameterController extends Controller
{
    protected $drive;
    protected $adminUser;

    public function __construct()
    {
        try {
            // Get the admin user for Google Drive operations
            $this->adminUser = User::where('role', 'admin')->first();
            
            if (!$this->adminUser) {
                Log::error('ParameterController: No admin user found for Google Drive operations');
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
                    Log::error('ParameterController: Google token expired and no refresh token available for admin user');
                    return;
                }
                
                // Initialize Google Drive service
                $client = new \Google_Client();
                $client->setAccessToken($this->adminUser->google_token);
                $this->drive = new Drive($client);
            } else {
                Log::error('ParameterController: Admin user does not have Google credentials');
            }
        } catch (\Exception $e) {
            Log::error('ParameterController initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the parameters for the specified area.
     */
    public function index(Area $area)
    {
        $parameters = $area->parameters()->orderBy('order', 'asc')->get();
        return response()->json($parameters);
    }

    /**
     * Display the specified parameter.
     */
    public function show(Parameter $parameter)
    {
        return response()->json($parameter->load('indicators'));
    }

    /**
     * Store a newly created parameter.
     */
    public function store(Request $request, Area $area)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($area) {
                        // Check if a parameter with the same name already exists for this area
                        $exists = Parameter::where('area_id', $area->id)
                            ->where('name', $value)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Name already exists');
                        }
                    }
                ],
                'description' => 'nullable|string',
                'order' => 'nullable|integer',
            ]);

            $parameter = $area->parameters()->create($validated);
            
            // Create Google Drive folder for the parameter
            if ($this->drive && $area->google_drive_folder_id) {
                try {
                    $folderId = $this->createGoogleDriveFolder($parameter, $area);
                    if ($folderId) {
                        $parameter->update(['google_drive_folder_id' => $folderId]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create Google Drive folder for parameter: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $parameter
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Name already exists',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error creating parameter', [
                'area_id' => $area->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create parameter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified parameter.
     */
    public function update(Request $request, Parameter $parameter)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($parameter) {
                        // Check if another parameter with the same name already exists for this area
                        $exists = Parameter::where('area_id', $parameter->area_id)
                            ->where('name', $value)
                            ->where('id', '!=', $parameter->id)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Name already exists');
                        }
                    }
                ],
                'description' => 'nullable|string',
                'order' => 'nullable|integer',
            ]);

            // Check if name is changed to update Google Drive folder
            if (isset($validated['name']) && $validated['name'] !== $parameter->name) {
                $oldName = $parameter->name;
                $newName = $validated['name'];
                
                // Rename Google Drive folder if it exists
                if ($this->drive && $parameter->google_drive_folder_id) {
                    try {
                        $this->renameGoogleDriveFolder($parameter, $newName);
                    } catch (\Exception $e) {
                        Log::warning('Failed to rename Google Drive folder for parameter: ' . $e->getMessage());
                    }
                }
            }

            $parameter->update($validated);
            
            return response()->json([
                'success' => true,
                'data' => $parameter
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Name already exists',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating parameter', [
                'id' => $parameter->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parameter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified parameter.
     */
    public function destroy(Parameter $parameter)
    {
        // Check if any indicators within this parameter have uploads
        $hasUploads = false;
        foreach ($parameter->indicators as $indicator) {
            if ($indicator->uploads()->exists()) {
                $hasUploads = true;
                break;
            }
        }

        // Delete Google Drive folder if it exists AND no uploads exist
        if ($this->drive && $parameter->google_drive_folder_id && !$hasUploads) {
            try {
                $this->drive->files->delete($parameter->google_drive_folder_id);
                Log::info('Deleted Google Drive folder for parameter: ' . $parameter->name);
            } catch (\Exception $e) {
                Log::warning('Failed to delete Google Drive folder for parameter: ' . $e->getMessage());
            }
        } else if ($hasUploads && $parameter->google_drive_folder_id) {
            Log::info('Preserved Google Drive folder for parameter because it contains indicators with uploads: ' . $parameter->name);
        }

        $parameter->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Update the order of multiple parameters.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'parameters' => 'required|array',
            'parameters.*.id' => 'required|exists:parameters,id',
            'parameters.*.order' => 'required|integer',
        ]);

        foreach ($validated['parameters'] as $parameterData) {
            Parameter::where('id', $parameterData['id'])->update(['order' => $parameterData['order']]);
        }

        return response()->json(['message' => 'Parameter order updated successfully']);
    }

    /**
     * Create a Google Drive folder for the parameter.
     */
    private function createGoogleDriveFolder(Parameter $parameter, Area $area)
    {
        try {
            if (!$this->adminUser || !$this->adminUser->google_token) {
                Log::warning('Cannot create Google Drive folder: No valid Google token available');
                return null;
            }
            
            // Create folder within the area's folder
            $fileMetadata = new DriveFile([
                'name' => $parameter->name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$area->google_drive_folder_id]
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
            
            Log::info('Created Google Drive folder for parameter: ' . $parameter->name);
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
     * Rename a Google Drive folder for the parameter.
     */
    private function renameGoogleDriveFolder(Parameter $parameter, string $newName)
    {
        try {
            if (!$this->adminUser || !$this->adminUser->google_token) {
                Log::warning('Cannot rename Google Drive folder: No valid Google token available');
                return false;
            }
            
            $fileMetadata = new DriveFile([
                'name' => $newName
            ]);
            
            $this->drive->files->update($parameter->google_drive_folder_id, $fileMetadata);
            Log::info('Renamed Google Drive folder for parameter from: ' . $parameter->name . ' to: ' . $newName);
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