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
            $table->tinyInteger('order_source')
                  ->nullable()
                  ->comment('0 = Pick Me, 1 = Uber')
                  ->after('wrong_bill');

            $table->decimal('commission_amount', 10, 2)
                  ->default(0)
                  ->after('order_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['order_source', 'commission_amount']);
        });
    }
};
