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
    Schema::create('receipts', function (Blueprint $table) {
       $table->string('receipt_id',20)->primary();
       $table->string('receipt_number',30)->unique();
       $table->string('payment_id',50);
       $table->string('student_id',15);
       $table->decimal('amount_paid',10,2);
       $table->decimal('balance',10,2);
       $table->string('payment_method',100);
       $table->string('note',255)->nullable();
       $table->timestamp('paid_at')->nullable();
       $table->timestamps();

        $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
        $table->foreign('student_id')->references('studentID')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
