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
            $table->text('telefon2')->default(null);
            $table->text('email1');
            $table->text('email2')->default(null);
            $table->text('spletna_stran')->default(null);
            $table->text('davcna_stevilka');
            $table->text('zavezanec_za_ddv');
            $table->text('trr1');
            $table->text('trr2')->default(null);
            $table->text('banka1');
            $table->text('banka2')->default(null);
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
