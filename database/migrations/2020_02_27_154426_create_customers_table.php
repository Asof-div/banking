<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('bvn');
            $table->string('currency');
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('account_opening_date')->nullable();
            $table->timestamp('last_transaction_date')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
