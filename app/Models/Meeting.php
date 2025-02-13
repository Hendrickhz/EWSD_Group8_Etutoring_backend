<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
    use HasFactory;

    protected $fillable = [
        'tutor_id', 'student_id', 'title', 'notes', 'type', 'location', 'meeting_link', 
        'date', 'time', 'status'
    ];

    public function tutor(){
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student(){
        return $this->belongsTo(User::class, 'student_id');
    }
}
