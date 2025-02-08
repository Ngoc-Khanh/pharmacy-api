<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection('mongodb')->create('invoices', function (Blueprint $collection) {
            $collection->id();
            $collection->string('order_id');
            $collection->string('user_id');
            $collection->string('invoice_number');
            $collection->integer('total_amount');
            $collection->string('payment_method');
            $collection->string('payment_status');
            $collection->timestamp('issued_date');
            $collection->timestamp('due_date');
            $collection->string('note')->nullable();
            $collection->timestamp('created_at');
            $collection->array('details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('invoices');
    }
};
