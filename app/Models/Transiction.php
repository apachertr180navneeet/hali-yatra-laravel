<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transiction extends Model
{
    use HasFactory , SoftDeletes;

    // Define the table associated with the model
    protected $table = 'transictions';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'booking_id', 
        'transiction_type', 
        'amount',
        'remark',
        'trasiction_id',
        'created_by', 
        'updated_by'
    ];
}
