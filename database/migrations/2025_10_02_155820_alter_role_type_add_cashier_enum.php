<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 


return new class extends Migration
{
    public function up()
    {
        // Add Cashier to enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role_type 
            ENUM('Manager','Admin','User','Owner','Cashier') 
            NOT NULL DEFAULT 'User'");
    }

    public function down()
    {
        // Rollback: remove Cashier from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role_type 
            ENUM('Manager','Admin','User','Owner') 
            NOT NULL DEFAULT 'User'");
    }
};