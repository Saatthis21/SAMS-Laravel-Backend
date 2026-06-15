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
    Schema::create('notifications', function (Blueprint $table) {
        $table->string('notification_id', 100)->primary();
        $table->string('student_id', 15);
        $table->string('message',255);
        $table->string('type', 50); // reminder, block alert, payment failure, payment confirm
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();

        $table->foreign('student_id')->references('studentID')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
