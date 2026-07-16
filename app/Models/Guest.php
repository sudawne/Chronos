<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id', 
        'full_name', 
        'email',
        'position', 
        'seat_location', 
        'image_filename', 
        'face_vector', 
        'is_attended'
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}