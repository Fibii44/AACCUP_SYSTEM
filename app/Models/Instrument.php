<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'google_drive_folder_id',
        'order',
    ];

    protected $casts = [
        'google_drive_folder_id' => 'array'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'google_drive_file_id',
                'file_name',
                'file_type',
                'file_size',
                'uploaded_at'
            ]);
    }

    /**
     * Get the areas for the instrument.
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }
} 