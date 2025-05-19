<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\TenantFacultyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TenantInvitationController extends Controller
{
    /**
     * Display a listing of invitations
     */
    public function index()
    {
        $users = User::where('role', 'user')->get();
        return view('tenant.invitations.index', compact('users'));
    }

    /**
     * Send an invitation to a new faculty member
     */
    public function invite(Request $request)
    {
        try {
            Log::info('Starting faculty invitation process', [
                'name' => $request->name,
                'email' => $request->email
            ]);

            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
            ]);

            // Generate a random password
            $password = Str::random(8);

            Log::info('Creating new faculty user from invitation', [
                'email' => $request->email,
                'password_length' => strlen($password)
            ]);

            // Create new faculty user with role 'user'
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role' => 'user',
                'email_verified_at' => null, // User needs to verify email
            ]);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            try {
                Log::info('Attempting to send invitation email', [
                    'to' => $user->email,
                    'password_length' => strlen($password)
                ]);

                // Send invitation email with credentials
                Mail::to($user->email)->send(new TenantFacultyMail($user, $password));
                
                Log::info('Invitation email sent successfully');
                
                // Verify user creation
                $userExists = User::where('email', $user->email)->exists();
                Log::info('Final verification', [
                    'user_exists' => $userExists ? 'Yes' : 'No',
                    'email' => $user->email
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send invitation email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue execution even if email fails
            }

            return redirect()->back()->with('success', 'Invitation sent successfully! Login credentials have been sent to the email.');
            
        } catch (\Exception $e) {
            Log::error('Error sending invitation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Error sending invitation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Resend invitation to an existing user
     */
    public function resend($id)
    {
        try {
            $user = User::findOrFail($id);

            // Generate a new password
            $password = Str::random(8);
            
            // Update user's password
            $user->update([
                'password' => Hash::make($password)
            ]);

            Log::info('Resending invitation', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            try {
                Mail::to($user->email)->send(new TenantFacultyMail($user, $password));
                Log::info('Invitation resent successfully');
            } catch (\Exception $e) {
                Log::error('Failed to resend invitation email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            return redirect()->back()->with('success', 'Invitation resent successfully!');

        } catch (\Exception $e) {
            Log::error('Error resending invitation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error resending invitation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/revoke invitation by deleting the user
     */
    public function cancel($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Only allow canceling invitations for unverified users
            if ($user->email_verified_at) {
                return redirect()->back()->with('error', 'Cannot cancel invitation for verified users.');
            }

            Log::info('Canceling invitation', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            $user->delete();

            return redirect()->back()->with('success', 'Invitation canceled successfully.');

        } catch (\Exception $e) {
            Log::error('Error canceling invitation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error canceling invitation: ' . $e->getMessage());
        }
    }

    /**
     * Show invitation status and history
     */
    public function status()
    {
        $pendingInvitations = User::where('role', 'user')
            ->whereNull('email_verified_at')
            ->get();

        $verifiedUsers = User::where('role', 'user')
            ->whereNotNull('email_verified_at')
            ->get();

        return view('tenant.invitations.status', compact('pendingInvitations', 'verifiedUsers'));
    }
}