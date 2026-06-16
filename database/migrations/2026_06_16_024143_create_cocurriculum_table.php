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
    Schema::create('cocurriculum', function (Blueprint $table) {
        $table->id();
        $table->string('studentID');
        $table->string('subject_code');
        $table->string('subject_name');
        $table->decimal('hours_recorded', 5, 2)->default(0);
        $table->decimal('hours_required', 5, 2)->default(40);
        $table->integer('credits')->default(2);
        $table->enum('status', [
            'In Progress',
            'Pending Review',
            'Credit Awarded',
            'Rejected'
        ])->default('In Progress');
        $table->text('rejection_reason')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cocurriculum');
    }
};
