<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    /** @use HasFactory<\Database\Factories\BlogFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'student_id', 'title', 'content'];

    // Blog author (student or tutor)
    public function author(){
        return $this->belongsTo(User::class,'user_id');
    }

    // If the blog is targeted at a specific student
    public function targetedStudent(){
        return $this->belongsTo(User::class,'student_id');
    }

    public function comments(){
        return $this->hasMany(BlogComment::class);
    }
}
