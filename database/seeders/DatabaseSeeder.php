<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with testing data.
     */
    public function run(): void
    {
        // 1. Insert a mock student record first with a secure dummy password
        DB::table('students')->insert([
            'studentID'      => 'CB23074', 
            'student_name'    => 'Jaclina Jacob',
            'student_year'    => 3,
            'student_course'  => 'Software Engineering',
            'password'        => bcrypt('password123'), // Added to satisfy the database constraint
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),
        ]);

        // 2. Insert the matching mock fee record for this student
        DB::table('fees')->insert([
            'fees_id'        => 'FEE-2026-001',
            'student_id'     => 'CB23074',
            'program'        => 'BCS',
            'semester'       => 'SEM 1 25/26',
            'total_fee'      => 5000.00,
            'paid_amount'    => 2000.00,
            'balance'        => 3000.00,
            'status'         => 'partial', 
            'deadline_week'  => 5,
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
    }
}