<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menta_credits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger("account_id");
            $table->unsignedInteger("club_id")->nullable();
            $table->unsignedInteger("treasury_id")->comment("account_id");
            $table->bigInteger("amount")->default(0);
            $table->timestamp("usable_at")->nullable();
            $table->timestamp("expired_at")->nullable();
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
        Schema::dropIfExists('credits');
    }
}
