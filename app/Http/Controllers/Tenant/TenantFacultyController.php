<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        try {
            Log::info('Starting faculty creation process', [
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

            // Create new faculty user with role 'user'
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
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
}