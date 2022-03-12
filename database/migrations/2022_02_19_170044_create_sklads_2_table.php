<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSklads2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sklads_2', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers_2');
            $table->unsignedBigInteger('final_invoice_id');
            $table->foreign('final_invoice_id')->references('id')->on('final_invoices_2');
            $table->string('item')->nullable();
            $table->integer('status')->default(0);
            $table->date('created')->nullable();
            $table->date('work_date')->nullable();
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
        Schema::dropIfExists('sklads_2');
    }
}
