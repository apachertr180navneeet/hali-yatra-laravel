<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "bookings";

    protected $fillable = [
        'booking_id',
        'operator_name',
        'flown_by',
        'transaction_id',
        'booking_type',
        'booking_date',
        'boarding_date',
        'return_type',
        'journey',
        'time_slot',
        'no_of_passengers',
        'total_amount',
        'booking_base_fare',
        'booking_base_fare_tax',
        'booking_convenience_fee',
        'booking_convenience_fee_tax',
        'status',
        'pg_name',
        'cancellation_date',
        'remarks',
        'refund_amount',
        'booking_from',
        'booking_counter_id',
    ];


        // Booking model mein relation define hona chahiye
        public function bookingDetails() {
            return $this->hasMany(BookingDetail::class, 'booking_id', 'booking_id');
        }
}
