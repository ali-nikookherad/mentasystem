<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountTypeTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('menta_account_types', function (Blueprint $table) {
            $table->bigIncrements('id');
//            $table->enum("type", ["wallet", "customer", "merchant", "taxi", "club", "agent", "bank", "treasury", "campaign", "gift"]);
            $table->string("type");
            $table->unsignedInteger('wallet_id');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->enum('balance_type', ["negative", "positive", "ziro"])->nullable();
            $table->integer('min_account_amount')->nullable();
            $table->integer('max_account_amount')->nullable();
            $table->integer('min_transaction_amount')->nullable();
            $table->integer('max_transaction_amount')->nullable();
            $table->boolean('legal')->nullable();
            $table->integer('interest_rate')->nullable();
            $table->enum('interest_period', ["daily", "weekly", "yearly"])->nullable();
            $table->boolean('revoked')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_types');
    }
}
