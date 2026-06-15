<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeeSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('students')->insert([
            'studentID' => 'CB23074',
            'student_name' => 'Jaclina',
            'student_year' => 2,
            'student_course' => 'BCS',
            'password' => bcrypt('123456'),
        ]);



        DB::table('fees')->insert([
            'fees_id' => 'FEE001',
            'student_id' => 'CB23074',
            'program' => 'BCS',
            'semester' => 'SEM2',
            'total_fee' => 5000,
            'paid_amount' => 0,
            'balance' => 5000,
            'status' => 'UNPAID',
            'deadline_week' => 5,
        ]);

    }
}