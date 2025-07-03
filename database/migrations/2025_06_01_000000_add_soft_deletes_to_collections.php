<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('orders', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::connection('mongodb')->table('invoices', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::connection('mongodb')->table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::connection('mongodb')->table('medicines', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::connection('mongodb')->table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::connection('mongodb')->table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::connection('mongodb')->table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::connection('mongodb')->table('medicines', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}; 