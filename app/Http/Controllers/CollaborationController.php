<?php

namespace App\Http\Controllers;
/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Project;
/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CollaborationController extends Controller
{
    /* 
    / Método para listar colaboraciones
    / return: Vista con lista de proyectos colaborados
    */
    public function index()
    {
        // Obtenemos los proyectos donde el usuario es COLABORADOR
        $projects = Auth::user()->projects()->with('user')->get();

        return Inertia::render('Collaborations/Index', [
            'projects' => $projects
        ]);
    }
    /* 
    / Método para mostrar detalles de una colaboración
    / return: Vista con detalles del proyecto colaborado
    */
    public function show($id)
    {
        $project = Auth::user()->projects()->where('project_id', $id)->firstOrFail();

        // Cargamos las relaciones necesarias para la vista
        $project->load(['user', 'users', 'tasks.subtasks', 'tasks.assignee','tasks.comments.user','comments.user']);

        // Cálculo de progreso
        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'completed')->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return Inertia::render('Collaborations/Show', [
            'project' => $project,
            'owner' => $project->user,
            'collaborators' => $project->users,
            'tasks' => $project->tasks,
            'progress' => $progress
        ]);
    }
}