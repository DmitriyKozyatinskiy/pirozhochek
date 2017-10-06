<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnneededPlanColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('plans', function ($table) {
        $table->dropColumn('level');
        $table->dropColumn('name');
        $table->dropColumn('description');
        $table->dropColumn('price');
        $table->dropColumn('saveIncognito');
        $table->dropColumn('saveAllHistory');
        $table->integer('braintree_id')->unique();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('plans', function (Blueprint $table) {
        // $table->integer('level')->nullable();
//        $table->string('name')->unique();
//        $table->string('description')->nullable();
//        $table->integer('price')->default(100);
//        $table->boolean('saveIncognito')->default(true);
//        $table->boolean('saveAllHistory')->default(true);
        //$table->dropColumn('braintree_id');
      });
    }
}
