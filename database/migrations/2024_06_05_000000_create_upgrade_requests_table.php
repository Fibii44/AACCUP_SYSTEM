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
        Schema::create('upgrade_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('request_type', ['upgrade', 'downgrade'])->default('upgrade');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add foreign keys if tables exist (safer approach)
            if (Schema::hasTable('tenants')) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }
            
            if (Schema::hasTable('users')) {
                $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upgrade_requests');
    }
}; 