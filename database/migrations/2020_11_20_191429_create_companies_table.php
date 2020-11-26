<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->text('naziv');
            $table->text('naslov');
            $table->text('posta');
            $table->text('telefon1');
            $table->text('telefon2');
            $table->text('email1');
            $table->text('email2');
            $table->text('spletna_stran');
            $table->text('davcna_stevilka');
            $table->text('zavezanec_za_ddv');
            $table->text('trr1');
            $table->text('trr2');
            $table->text('banka1');
            $table->text('banka2');
            $table->text('logo');
            $table->text('stamp');
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
        Schema::dropIfExists('companies');
    }
}
