<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('students', function (Blueprint $table) {
        $table->string('studentID', 15)->primary();
        $table->string('student_name', 100);
        $table->integer('student_year');
        $table->string('student_course', 100);
        $table->string('password', 255);
        $table->timestamps(); // Automatically handles created_at and updated_at
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
