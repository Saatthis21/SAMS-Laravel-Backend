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
    Schema::create('fee_structures', function (Blueprint $table) {
        $table->string('id', 100)->primary(); 
        $table->string('program', 100);       
        $table->string('semester', 100);      
        $table->decimal('fee_amount', 10, 2); 
        $table->integer('deadline_week')->default(5); 
        $table->timestamps();                 
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
