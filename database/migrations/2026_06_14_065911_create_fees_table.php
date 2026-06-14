<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('fees', function (Blueprint $table) {
        $table->string('fees_id', 50)->primary();
        $table->string('student_id', 15);
        $table->string('program', 20);
        $table->string('semester', 20);
        $table->decimal('total_fee', 10, 2);
        $table->decimal('paid_amount', 10, 2)->default(0.00);
        $table->decimal('balance', 10, 2);
        $table->string('status', 50); // unpaid, partial, paid
        $table->integer('deadline_week');
        $table->timestamps();

        // Foreign key referencing your teammate's students table
        $table->foreign('student_id')->references('studentID')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
