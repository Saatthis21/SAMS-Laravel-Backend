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
    Schema::create('payments', function (Blueprint $table) {
        $table->string('payment_id', 50)->primary();
        $table->string('receipt_id', 50)->nullable(); 
        $table->string('fee_id', 50);
        $table->string('student_id', 15);
        $table->decimal('amount', 10, 2);
        $table->string('payment_method', 100); 
        $table->string('status',50)->default('PENDING');
        $table->string('transaction_ref', 100)->unique();
        $table->string('failure_reason', 200)->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();

        // Safely references the fees table created right before it
        $table->foreign('fee_id')->references('fees_id')->on('fees')->onDelete('cascade');
        $table->foreign('student_id')->references('studentID')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
