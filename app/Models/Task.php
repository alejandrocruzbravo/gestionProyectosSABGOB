<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'user_id', 'assigned_to', 'title', 
        'description', 'status', 'priority', 'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Relación: Una tarea pertenece a un Proyecto
    public function project() {
        return $this->belongsTo(Project::class);
    }
    // Relación: Una tarea pertenece a un Usuario (Creador)
    public function assignee() { // El usuario asignado
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relación: Muchas subtareas pertenecen a una tarea
    public function subtasks() {
        return $this->hasMany(Subtask::class);
    }
    
    // Relación: Polimórfica para comentarios
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}