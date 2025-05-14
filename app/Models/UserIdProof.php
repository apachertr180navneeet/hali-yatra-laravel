<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserIdProof extends Model
{
    use HasFactory , SoftDeletes;

    // Define the table associated with the model
    protected $table = 'user_id_proof';

    // Define the fields that can be mass assigned
    protected $fillable = [
        'name',
        'status',
        'user_id_proof_order', 
        'created_by', 
        'updated_by',
        'ticket_order'
    ];
}
