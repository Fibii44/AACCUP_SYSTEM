<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'instrument_id',
        'name',
        'description',
        'google_drive_folder_id',
        'order',
    ];

    /**
     * Get the instrument that owns the area.
     */
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    /**
     * Get the parameters for the area.
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(Parameter::class);
    }
} 