<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->json('google_drive_folder_id')->nullable();
            $table->timestamps();
        });

        Schema::create('instrument_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instrument_id');
            $table->unsignedBigInteger('user_id');
            $table->string('google_drive_file_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->string('file_size')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->foreign('instrument_id')
                ->references('id')
                ->on('instruments')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instrument_user');
        Schema::dropIfExists('instruments');
    }
}; 