<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\TenantFacultyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Add this import

class TenantFacultyController extends Controller
{
    /**
     * Display a listing of the faculty members.
     */
    public function index()
    {
        $faculty = User::where('role', 'user')->get();
        return view('tenant.userTable', compact('faculty'));
    }

    /**
     * Store a newly created faculty member.
     */
    public function store(StoreFacultyRequest $request)
    {
        try {
            Log::info('Starting faculty creation process', [
                'name' => $request->name,
                'email' => $request->email
            ]);

            // The request is already validated by StoreFacultyRequest
            $validated = $request->validated();

            // Generate a random password
            $password = Str::random(12);

            // Create new faculty user with role 'user'
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'user',
            ]);

            // Send welcome email with credentials
            Mail::to($user->email)->send(new TenantFacultyMail($user, $password));

            return redirect()->back()->with('success', 'User added successfully! Login credentials have been sent to their email.');
            
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(UpdateFacultyRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Only allow editing faculty users
            if ($user->role !== 'user') {
                return redirect()->back()->with('error', 'You can only edit faculty users.');
            }

            // The request is already validated by UpdateFacultyRequest
            $validated = $request->validated();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            Log::info('User updated successfully', [
                'user_id' => $id,
                'updated_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'User updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage(), [
                'user_id' => $id,
                'updated_by' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error updating user. Please try again.')
                ->withInput();
        }
    }
    

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Only allow deleting faculty users
            if ($user->role !== 'user') {
                return redirect()->back()->with('error', 'You can only delete faculty users.');
            }

            // Store user info for logging
            $userInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            // Delete the user
            $user->delete();

            Log::info('Faculty member deleted successfully', [
                'deleted_user' => $userInfo,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Faculty member has been deleted successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error deleting faculty member: ' . $e->getMessage(), [
                'user_id' => $id,
                'deleted_by' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error deleting faculty member. Please try again.');
        }
    }


}