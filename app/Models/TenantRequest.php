<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'department_name',
        'email',
        'domain',
        'status',
        'password',
        'rejection_reason',
    ];
    
    protected $hidden = [
        'password',
    ];
    
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    public function isApproved()
    {
        return $this->status === 'approved';
    }
    
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Ensure we always store passwords in plain text in the tenant request
     * so they can be included in emails.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value;
    }
    
    /**
     * Get the password from the tenant request
     */
    public function getPasswordAttribute($value)
    {
        return $value;
    }
}
