<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->unique();
            $table->string('operator_name')->nullable();
            $table->string('flown_by')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('booking_type')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('boarding_date')->nullable();
            $table->string('return_type')->nullable();
            $table->string('journey')->nullable();
            $table->string('time_slot')->nullable();
            $table->integer('no_of_passengers')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('booking_base_fare', 10, 2)->default(0);
            $table->decimal('booking_base_fare_tax', 10, 2)->default(0);
            $table->decimal('booking_convenience_fee', 10, 2)->default(0);
            $table->decimal('booking_convenience_fee_tax', 10, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('pg_name')->nullable();
            $table->date('cancellation_date')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
