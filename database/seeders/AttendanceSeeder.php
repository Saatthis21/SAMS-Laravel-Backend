<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Make sure this is imported!
use App\Models\AttendanceSession;
use App\Models\StudentAttendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------
        // 0. CREATE MISSING PARENT DATA (Students & Labs)
        // ---------------------------------------------------

        // Seed Courses
        DB::table('courses')->insertOrIgnore([
            ['course_code' => 'BCS2173', 'course_name' => 'HUMAN COMPUTER INTERACTION', 'credit_hours' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Seed Lab Sections
        DB::table('lab_sections')->insertOrIgnore([
            ['labID' => 1, 'course_code' => 'BCS2173', 'lab_num' => '02A', 'date' => 'Monday', 'time' => '14:00:00', 'max_capacity' => 30, 'current_capacity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['labID' => 2, 'course_code' => 'BCS2173', 'lab_num' => '02B', 'date' => 'Tuesday', 'time' => '10:00:00', 'max_capacity' => 30, 'current_capacity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Seed Students
        DB::table('students')->insertOrIgnore([
            ['studentID' => 'CB23102', 'student_name' => 'Muhammad Yasrin', 'student_year' => 2, 'student_course' => 'BCS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['studentID' => 'CB23150', 'student_name' => 'Mohamad Nuaiman', 'student_year' => 2, 'student_course' => 'BCS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['studentID' => 'CB23085', 'student_name' => 'Nik Amir Imran', 'student_year' => 2, 'student_course' => 'BCS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['studentID' => 'CB23011', 'student_name' => 'Ahmad Sayuti', 'student_year' => 2, 'student_course' => 'BCS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // ---------------------------------------------------
        // 1. Create Dummy Attendance Sessions
        // ---------------------------------------------------

        $activeSession = AttendanceSession::create([
            'labID' => 1,
            'session_code' => 'A7X29P',
            'duration_minutes' => 15,
            'expires_at' => Carbon::now()->addMinutes(15),
            'status' => 'Active',
        ]);

        $closedSession = AttendanceSession::create([
            'labID' => 2,
            'session_code' => 'B9W12Q',
            'duration_minutes' => 60,
            'expires_at' => Carbon::now()->subDays(1),
            'status' => 'Closed',
        ]);

        // ---------------------------------------------------
        // 2. Create Dummy Attendance Records
        // ---------------------------------------------------

        StudentAttendance::create([
            'session_id' => $activeSession->session_id,
            'studentID' => 'CB23102', // Muhammad Yasrin
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 3.54370000,
            'gps_longitude' => 103.42890000,
            'gps_verified' => true,
            'sync_pusat_adab' => false,
            'created_at' => Carbon::now()->subMinutes(2),
        ]);

        StudentAttendance::create([
            'session_id' => $activeSession->session_id,
            'studentID' => 'CB23150', // Mohamad Nuaiman
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 3.54380000,
            'gps_longitude' => 103.42880000,
            'gps_verified' => true,
            'sync_pusat_adab' => false,
            'created_at' => Carbon::now()->subMinutes(1),
        ]);

        StudentAttendance::create([
            'session_id' => $activeSession->session_id,
            'studentID' => 'CB23085', // Nik Amir Imran
            'submitted_code' => 'A7X29P',
            'gps_latitude' => 3.13900000, // KL Coordinates (Fails GPS check)
            'gps_longitude' => 101.68690000,
            'gps_verified' => false,
            'sync_pusat_adab' => false,
            'created_at' => Carbon::now(),
        ]);

        StudentAttendance::create([
            'session_id' => $closedSession->session_id,
            'studentID' => 'CB23011', // Ahmad Sayuti
            'submitted_code' => 'B9W12Q',
            'gps_latitude' => 3.54375000,
            'gps_longitude' => 103.42895000,
            'gps_verified' => true,
            'sync_pusat_adab' => true,
            'created_at' => Carbon::now()->subDays(1)->addMinutes(10),
        ]);
    }
}
