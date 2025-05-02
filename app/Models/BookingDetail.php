<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'booking_detail';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'operator_name', 
        'booking_id', 
        'transaction_id', 
        'group_id', 
        'booking_status', 
        'passenger_booking_status', 
        'last_update_time', 
        'booking_type', 
        'booking_date', 
        'journey', 
        'boarding_date', 
        'time_slot', 
        'return_type', 
        'passenger_name', 
        'gender', 
        'government_id_type', 
        'government_id', 
        'mobile_no', 
        'yatra_reg_id', 
        'booker_mobile_number',
    ];
}
