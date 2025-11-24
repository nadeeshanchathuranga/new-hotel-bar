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
               Schema::table('sales', function (Blueprint $table) {
            $table->boolean('wrong_bill')->default(0)->comment('0 = correct, 1 = wrong bill');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('wrong_bill');
        });
    }
};
