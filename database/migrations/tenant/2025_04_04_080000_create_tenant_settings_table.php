<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->default('#3490dc');
            $table->string('secondary_color')->default('#6c757d');
            $table->string('tertiary_color')->default('#1a237e');
            $table->string('logo_url')->nullable();
            $table->string('imglogin_url')->nullable();
            $table->string('header_text')->default('Welcome to Our Platform');
            $table->text('welcome_message')->nullable();
            $table->boolean('show_testimonials')->default(true);
            $table->text('footer_text')->nullable();
            $table->json('custom_css')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
}; 