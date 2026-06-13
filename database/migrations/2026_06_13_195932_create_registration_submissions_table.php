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
    Schema::create('registration_submissions', function (Blueprint $table) {
        // Matches bigint(20) UNSIGNED AUTO_INCREMENT
        $table->id('submissionID'); 
        
        $table->string('studentID', 15);
        $table->dateTime('date');
        $table->string('overall_status', 255)->default('Pending');
        $table->text('rejection_reason')->nullable();
        $table->timestamps();

        // Foreign Key
        $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_submissions');
    }
};
