<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['chat_id', 'content', 'role'];

    // Relationship with chat
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}