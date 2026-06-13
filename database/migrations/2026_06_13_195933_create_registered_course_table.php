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
    Schema::create('registered_course', function (Blueprint $table) {
        // Matches bigint(20) UNSIGNED AUTO_INCREMENT
        $table->id('registeredID'); 
        
        $table->unsignedBigInteger('submissionID'); // Must match submissionID type
        $table->string('course_code', 10);
        $table->integer('labID'); // Matches lab_sections int(11)
        $table->timestamps();

        // 3 Foreign Keys
        $table->foreign('submissionID')->references('submissionID')->on('registration_submissions')->onDelete('cascade');
        $table->foreign('course_code')->references('course_code')->on('courses')->onDelete('cascade');
        $table->foreign('labID')->references('labID')->on('lab_sections')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registered_course');
    }
};
