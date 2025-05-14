<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use HasFactory , SoftDeletes;

    // Define the table associated with the model
    protected $table = 'ticket_type';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'name',
        'pecentage',
        'type',
        'status', 
        'created_by', 
        'updated_by',
        'ticket_order'
    ];
}
