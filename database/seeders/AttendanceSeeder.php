<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceSession;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema; // <-- 1. ADD THIS IMPORT!

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 2. TURN OFF STRICT RULES TEMPORARILY
        Schema::disableForeignKeyConstraints();

        // Create the dummy session
        $session = AttendanceSession::create([
            'lecturer_id' => 'STF001',
            'subject_code' => 'BCS2173',
            'labID' => 1,
            'session_code' => 'A7X29P',
            'duration_minutes' => 15,
            'expires_at' => Carbon::now()->addMinutes(15),
            'status' => 'Active',
        ]);

        // Re-seed your dummy students
        StudentAttendance::create([
            'session_id' => $session->id ?? $session->session_id,
            'studentID' => 'CB23150',
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 3.54380000,
            'gps_longitude' => 103.42890000,
            'gps_verified' => true,
        ]);

        StudentAttendance::create([
            'session_id' => $session->id ?? $session->session_id,
            'studentID' => 'CB23085',
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 3.13900000,
            'gps_longitude' => 101.68690000,
            'gps_verified' => true,
        ]);

        StudentAttendance::create([
            'session_id' => $session->id ?? $session->session_id,
            'studentID' => 'CB23102',
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 37.42199830,
            'gps_longitude' => -122.08400000,
            'gps_verified' => true,
        ]);

        // 3. TURN THE STRICT RULES BACK ON!
        Schema::enableForeignKeyConstraints();
    }
}