<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\Tenant\TenantFacultyController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'google_id',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Get the user's avatar URL
     * 
     * @return string
     */
    public function getAvatarAttribute()
    {
        if (!empty($this->profile_picture)) {
            return $this->profile_picture;
        }
        
        // Generate a default avatar based on initials or use a placeholder
        $initials = $this->initials();
        $colors = ['#1abc9c', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#e74c3c', '#bdc3c7'];
        $colorIndex = ord($initials[0] ?? 'A') % count($colors);
        $backgroundColor = $colors[$colorIndex];
        
        // Return a placeholder image or URL to a service like ui-avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . 
               '&background=' . str_replace('#', '', $backgroundColor) . 
               '&color=ffffff&size=256';
    }
}
