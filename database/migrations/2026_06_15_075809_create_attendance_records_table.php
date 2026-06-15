<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id('record_id'); // BIGINT Auto-increment PK
            $table->unsignedBigInteger('session_id'); // FK to attendance_sessions
            $table->string('studentID', 20); // FK to students (VARCHAR 20)
            $table->string('submitted_code', 6);

            // Spatial Data for GPS Geofencing
            $table->decimal('gps_latitude', 10, 8); // Max 90.00000000 (Fits in 10,8)
            $table->decimal('gps_longitude', 11, 8); // Max 180.00000000 (Needs 11,8)

            $table->boolean('gps_verified')->default(false);
            $table->boolean('sync_pusat_adab')->default(false);
            $table->timestamps();

            // Define Foreign Key Relationships
            $table->foreign('session_id')->references('session_id')->on('attendance_sessions')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};