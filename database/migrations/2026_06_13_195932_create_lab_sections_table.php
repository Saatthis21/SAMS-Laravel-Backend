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
    Schema::create('lab_sections', function (Blueprint $table) {
        // Matches int(11) AUTO_INCREMENT
        $table->integer('labID', true); 
        
        $table->string('course_code', 10);
        $table->string('lab_num', 20);
        $table->string('date', 20);
        $table->string('date_2', 20)->nullable(); // Allowed to be NULL
        $table->time('time');
        $table->time('time_2')->nullable();       // Allowed to be NULL
        $table->integer('max_capacity');
        $table->integer('current_capacity')->default(0);
        $table->timestamps();

        // Foreign Key
        $table->foreign('course_code')->references('course_code')->on('courses')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_sections');
    }
};
