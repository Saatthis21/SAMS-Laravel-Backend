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
    $this->call([
        FeeSeeder::class,
        AttendanceSeeder::class,
    ]);
}
}