<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model
{
    use HasFactory , SoftDeletes;

    // Define the table associated with the model
    protected $table = 'payment_type';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'type_name', 
        'status', 
        'created_by', 
        'updated_by'
    ];
}
