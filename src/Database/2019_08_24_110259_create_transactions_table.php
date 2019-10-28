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
        Schema::create('menta_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger("order_id");
            $table->unsignedInteger("from_account_id");
            $table->unsignedInteger("to_account_id");
            $table->bigInteger("amount")->default(0);
            $table->boolean("cashout")->default(false);
            $table->boolean("reverse")->default(false);
            $table->boolean("revoked")->default(false);
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
