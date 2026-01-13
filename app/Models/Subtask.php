<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $fillable = ['title', 'status', 'task_id'];

    // Relación: Pertenece a una Tarea
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Relación: Polimórfica para comentarios
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}