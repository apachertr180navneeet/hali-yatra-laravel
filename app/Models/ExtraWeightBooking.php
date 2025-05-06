<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraWeightBooking extends Model
{
    use HasFactory , SoftDeletes;

        // Define the table associated with the model
        protected $table = 'extra_weight_bookings';

        // Define the fields that can be mass assigned
        protected $fillable = [
            'booking_id', 
            'extra_body_weight', 
            'extra_luggage',
            'extra_body_weight_amount',
            'extra_luggage_amount',
            'total_amount',
            'discount_amount',
            'payable_amount',
            'created_by', 
            'updated_by'
        ];
}
