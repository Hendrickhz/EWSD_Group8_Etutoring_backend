<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTutor extends Model
{
    /** @use HasFactory<\Database\Factories\StudentTutorFactory> */
    use HasFactory;

    protected $fillable = ['student_id','tutor_id'];

    public function student(){
        return $this->belongsTo(User::class,'student_id');
    }

    public function tutor(){
        return $this->belongsTo(User::class,'tutor_id');
    }
}
