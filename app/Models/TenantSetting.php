<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_color',
        'secondary_color',
        'logo_url',
        'header_text',
        'welcome_message',
        'show_testimonials',
        'footer_text',
        'custom_css',
    ];

    protected $casts = [
        'show_testimonials' => 'boolean',
        'custom_css' => 'array',
    ];
}
