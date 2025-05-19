<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpgradeRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'status',
        'notes',
        'request_type',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
    
    /**
     * The default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
        'request_type' => 'upgrade',
    ];

    /**
     * Get the tenant that made the upgrade request.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the admin that processed the request.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
} 