<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menta_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("type")->default("request");
            $table->unsignedInteger("goods_id")->nullable();
            $table->unsignedInteger("from_account_id");
            $table->unsignedInteger("to_account_id");
            $table->bigInteger("amount");
            $table->timestamp("paid_at")->nullable();
            $table->boolean("refund")->default(false);
            $table->boolean("cashout")->default(false);
            $table->unsignedInteger("treasury_account_id")->nullable();
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
        Schema::dropIfExists('orders');
    }
}
