<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['user_id',  'filename','title','description','path'];
    protected $appends = ['full_url']; // Auto-append full_url to JSON responses

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function getFullUrlAttribute()
    {
        return asset( Storage::url($this->path));
    }
}
