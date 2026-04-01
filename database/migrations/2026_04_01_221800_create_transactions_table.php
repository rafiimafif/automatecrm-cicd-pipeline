<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('sales_number')->unique();
            $table->string('bill_number')->nullable();
            $table->dateTime('sales_date_in')->nullable();
            $table->dateTime('sales_date_out')->nullable();
            $table->string('brand')->nullable();
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('branch')->nullable();
            $table->string('visit_purpose')->nullable();
            
            $table->string('reguler_member_code')->nullable();
            $table->string('reguler_member_name')->nullable();
            $table->string('loyalty_member_code')->nullable();
            $table->string('loyalty_member_name')->nullable();
            $table->string('loyalty_member_type')->nullable();
            
            $table->string('employee_code')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('external_employee_code')->nullable();
            $table->string('external_employee_name')->nullable();
            
            $table->string('payment_method')->nullable();
            $table->string('parent_payment_method')->nullable();
            $table->string('trace_number')->nullable();
            $table->string('approval_code')->nullable();
            $table->string('edc_terminal_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('card_number')->nullable();
            
            $table->text('additional_info')->nullable();
            $table->text('notes')->nullable();
            
            $table->decimal('mdr', 15, 2)->default(0);
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->decimal('nett_after_mdr', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
