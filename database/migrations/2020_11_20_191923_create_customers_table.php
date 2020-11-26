<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('naziv_partnerja');
            $table->string('kraj_ulica');
            $table->string('posta');
            $table->string('telefon')->nullable();;
            $table->string('sklic_st')->nullable();
            $table->string('id_ddv')->nullable();
            $table->string('email')->nullable();
            $table->boolean('tujina')->nullable();
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
