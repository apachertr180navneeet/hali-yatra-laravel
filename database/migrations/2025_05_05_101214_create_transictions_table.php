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
        Schema::create('transictions', function (Blueprint $table) {
            $table->id();
            $table->string('transiction_type')->nullable();
            $table->string('booking_id')->default(0);
            $table->string('amount')->default(0);
            $table->string('remark')->nullable();
            $table->string('trasiction_id')->nullable();
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
        Schema::dropIfExists('transictions');
    }
};
