<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     

 public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
           

    $table->string('owner_id')->nullable();
            $table->decimal('owner_discount_value', 10, 2);


           
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'owner_id',
                'owner_discount_value', 
            ]);
        });
    }




};
