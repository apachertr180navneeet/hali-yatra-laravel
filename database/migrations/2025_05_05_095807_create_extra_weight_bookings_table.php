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
        Schema::create('extra_weight_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->default(0);
            $table->string('extra_body_weight')->default(0);
            $table->string('extra_luggage')->default(0);
            $table->string('extra_body_weight_amount')->default(0);
            $table->string('extra_luggage_amount')->default(0);
            $table->string('total_amount')->default(0);
            $table->string('discount_amount')->default(0);
            $table->string('payable_amount')->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_weight_bookings');
    }
};
