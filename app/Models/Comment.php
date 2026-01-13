<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    protected $fillable = ['body', 'user_id', 'commentable_id', 'commentable_type'];

    // Relación: Polimórfica inversa (se conecta a Project, Task o Subtask)
    public function commentable()
    {
        return $this->morphTo();
    }

    // Relación: El comentario lo escribió un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
