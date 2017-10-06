<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSubscriptionColumnConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('plans', function (Blueprint $table) {
        $table->dropUnique(['devices']);
        $table->string('description')->nullable(false)->change();
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
        // $table->unique(['devices']);
        $table->string('description')->nullable(true)->change();
      });
    }
}
