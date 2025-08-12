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
         Schema::create('owner_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->decimal('discount_value', 8, 2)->default(0);  
            $table->decimal('current_discount', 8, 2)->default(0); 
            $table->date('month')->nullable(); // Optional: if you want to track the month of the discount
            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();

            // Optional: prevent duplicate "current" rows per owner (DB-level if your MySQL supports it)
            // $table->unique(['owner_id', 'current_discount'], 'owner_current_unique')->where('current_discount', 1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_items');
    }
};
