<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('account_number');
            $table->string('currency');
            $table->decimal('available_balance', 12, 2)->default(0.00);
            $table->decimal('cleared_balance', 12, 2)->default(0.00);
            $table->decimal('unclear_balance', 12, 2)->default(0.00);
            $table->decimal('hold_balance', 12, 2)->default(0.00);
            $table->decimal('minimum_balance', 12, 2)->default(0.00);
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
        Schema::dropIfExists('customer_balances');
    }
}
