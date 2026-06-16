<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id('session_id'); // BIGINT Auto-increment PK

            // --- NEW COLUMNS ADDED TO MATCH FLUTTER ---
            $table->string('lecturer_id');
            $table->string('subject_code');

            // Kept your original labID, made it nullable just in case you need it later
            $table->unsignedBigInteger('labID')->nullable();

            $table->string('session_code', 6)->unique(); // 6-character OTP
            $table->integer('duration_minutes');
            $table->dateTime('expires_at');
            $table->string('status', 20)->default('Active');
            $table->timestamps(); // Automatically adds created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
