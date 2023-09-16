<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('section');
            $table->foreign('section')->references('id')->on('sections');
            $table->unsignedBigInteger('class_id');
            $table->foreign('class_id')->references('id')->on('classrooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sections_classes');
    }
};
