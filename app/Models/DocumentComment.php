<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentComment extends Model
{

    protected $fillable = ['document_id', 'user_id', 'comment'];


    public function document()
    {
      
        return $this->belongsTo(Document::class);
    }


    public function user()
    {
     
        return $this->belongsTo(User::class);
    }
}
