<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_detail', function (Blueprint $table) {
            $table->id();
            $table->string('operator_name');
            $table->string('booking_id');
            $table->string('transaction_id');
            $table->string('group_id');
            $table->string('booking_status');
            $table->string('passenger_booking_status');
            $table->timestamp('last_update_time')->nullable(); // Allow NULL
            $table->string('booking_type');
            $table->timestamp('booking_date')->nullable(); // Allow NULL
            $table->string('journey');
            $table->timestamp('boarding_date')->nullable(); // Allow NULL
            $table->string('time_slot');
            $table->string('return_type');
            $table->string('passenger_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('government_id_type');
            $table->string('government_id');
            $table->string('mobile_no');
            $table->string('yatra_reg_id');
            $table->string('booker_mobile_number');
            $table->timestamps();
            $table->softDeletes(); // Soft delete column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_detail');
    }
}


