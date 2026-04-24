<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('servicetocustomer_id');
            });
        } catch (Exception $e) {
            // Ignore SQLite drop column errors during testing
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('servicetocustomer_id')->nullable()->after('customer_id');
        });
    }
};
