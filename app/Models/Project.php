<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $fillable = ['title', 'description', 'status', 'user_id'];

    // Relación: Un proyecto pertenece a un Dueño (User)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Un proyecto tiene muchas Tareas
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Relación: Un proyecto pertenece a un Usuario (Dueño)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Relación: Un proyecto puede tener muchos Colaboradores (Muchos a Muchos)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relación: Polimórfica para comentarios
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
