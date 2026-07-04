<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title', 'start_time', 'end_time', 'location', 'description', 'recognition_threshold', 'welcome_config'
    ];

    // Một cuộc họp (Meeting) có nhiều khách mời (Guests)
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}