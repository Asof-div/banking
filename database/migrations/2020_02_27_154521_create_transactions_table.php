<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('account_number');
            $table->string('amount');
            $table->string('currency');
            $table->string('channel')->nullable();
            $table->string('debit_or_credit');
            $table->string('narration');
            $table->string('reference_id');
            $table->string('a_reference_id')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->string('transaction_type');
            $table->decimal('actual_amount_after_charge', 18, 2)->default(0.00);
            $table->decimal('charge_fee', 18, 2)->default(0.00);
            $table->timestamp('value_date')->nullable();
            $table->string('balance_after')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
