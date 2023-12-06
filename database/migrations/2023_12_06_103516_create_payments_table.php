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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('allocation_id');
            $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('cascade');
            $table->uuid('transcation_id');
            $table->enum('payment_status', ['success', 'failed', 'pending', 'reject'])->default('pending');
            $table->integer('amount');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE payments MODIFY transcation_id BIGINT AUTO_INCREMENT = 70000000000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
