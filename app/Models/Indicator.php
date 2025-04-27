<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'parameter_id',
        'name',
        'description',
        'google_drive_folder_id',
        'order',
    ];

    /**
     * Get the parameter that owns the indicator.
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }
    
    /**
     * Get the uploads for the indicator.
     */
    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }
} 