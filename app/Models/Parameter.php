<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'name',
        'description',
        'google_drive_folder_id',
        'order',
    ];

    /**
     * Get the area that owns the parameter.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the indicators for the parameter.
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }
} 