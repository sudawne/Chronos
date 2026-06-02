<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'event_id', 
        'guest_id', 
        'check_in_time'
    ];

    public function guest() {
        return $this->belongsTo(Guest::class);
    }
    public function event() {
        return $this->belongsTo(Event::class);
    }
}
