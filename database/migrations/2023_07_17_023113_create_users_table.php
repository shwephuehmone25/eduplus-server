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
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number')->unique();
            $table->boolean('isVerified')->default(false);
            $table->date('dob')->nullable();
            $table->string('password')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            //$table->enum('region', ['Kachin State', 'Kayah State', 'Karen State', 'Chin State', 'Mon State', 'Rakhine State', 'Shan State', 'Ayeyarwady Division', 'Bago Division', 'Magway Division', 'Mandalay Division', 'Yangon Division', 'Tanintharyi Division', 'Sagaing Division']);
            $table->string('region')->nullable();
            $table->string('address')->nullable();
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
