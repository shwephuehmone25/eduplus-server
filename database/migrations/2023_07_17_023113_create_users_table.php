<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('phone_id');
            $table->foreign('phone_id')->references('id')->on('phones')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->dateTime('dob');
            $table->string('password');
            $table->boolean('status')->default(1)->comment('0 for restricted, 1 for active');
            $table->string('image_url')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('region');
            $table->string('address');
            $table->string('role')->default('student');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE users AUTO_INCREMENT = 70000000000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
