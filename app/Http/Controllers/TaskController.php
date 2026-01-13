<?php

namespace App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Subtask;

/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /* 
    / Método para listar tareas
    / return: Vista con lista de tareas
    */
    public function index()
    {
        $userId = Auth::id();
        
        // Solo tareas donde el mismo usuario es el responsable ('assigned_to')
        $tasks = Task::with(['project', 'subtasks', 'assignee'])
            ->where('assigned_to', $userId)
            ->get();

        // Proyectos donde el usuario es Dueño O Colaborador
        $projects = Project::where('user_id', $userId)
            ->orWhereHas('users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get();
            
        $users = User::all(); 

        return Inertia::render('Task/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
            'users' => $users
        ]);
    }

    /* 
    / Método para crear una tarea
    / return: Redirige a la página de tareas
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $request->user()->tasks()->create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return back();
    }

    /* 
    / Método para actualizar una tarea
    / return: Recarga la misma página con datos nuevos
    */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high', // <--- IMPORTANTE: Validar prioridad
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return back();
    }

    /* 
    / Método para eliminar una tarea 
    / return: Recarga la misma página con datos nuevos
    */
    public function destroy(Task $task)
    {
        $task->delete();
        return back();
    }

}