<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoices2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_2', function (Blueprint $table) {
            $table->id();
            $table->string('sifra_predracuna');
            $table->string('ime_priimek');
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers_2')->onDelete('cascade');
            $table->date('timestamp');
            $table->date('expiration');
            $table->string('klavzula');
            $table->date('work_date')->nullable();
            $table->string('iid');
            $table->float('total');
            $table->float('quantity');
            $table->float('vat');
            $table->text('remark')->default(null);
            $table->boolean('avans')->default(null);
            $table->float('avans_sum')->default(null);
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
        Schema::dropIfExists('invoices_2');
    }
}
