<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('plans', function (Blueprint $table) {
      $table->increments('id')->index();
      $table->integer('level')->nullable();
      $table->string('name')->unique();
      $table->string('description')->nullable();
      $table->integer('devices')->unique();
      $table->integer('days');
      $table->integer('price')->default(100);
      $table->boolean('saveIncognito')->default(true);
      $table->boolean('saveAllHistory')->default(true);
      $table->timestamps();
    });

    Schema::enableForeignKeyConstraints();
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('plans');
  }
}
