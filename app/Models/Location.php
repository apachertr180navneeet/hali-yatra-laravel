<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory , SoftDeletes;

    // Define the table associated with the model
    protected $table = 'location';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'name', 
        'status', 
        'created_by', 
        'updated_by',
        'location_order'
    ];
}
