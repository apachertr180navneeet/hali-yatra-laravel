<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory , SoftDeletes;

        // Define the table associated with the model
        protected $table = 'settings';

        // Define the fields that can be mass assigned
        protected $fillable = [
            'minimum_body_weight', 
            'minimum_luggage_weight', 
            'minimum_body_weight_amount', 
            'minimum_luggage_weight_amount',
            'created_by', 
            'updated_by'
        ];
}
