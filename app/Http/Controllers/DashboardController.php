<?php

namespace App\Http\Controllers;
/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;



class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        /* Proyectos donde soy Dueño O Colaborador */
        $projectsQuery = Project::where('user_id', $userId)
            ->orWhereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });

        /* --------- CONTADORES ---------*/
        $totalProjects = $projectsQuery->count();
        
        $activeTasks = Task::where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();


        $teamMembers = User::whereHas('projects', function($q) use ($userId) {
            $q->where('projects.user_id', $userId);
        })->count();

        /* --------- PROYECTOS RECIENTES --------- */
        $recentProjects = $projectsQuery->with('tasks')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($project) {
                // Cálculo de Progreso
                $total = $project->tasks->count();
                $completed = $project->tasks->where('status', 'completed')->count();
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'desc' => $project->description,
                    'progress' => $progress,
                    'status' => $project->status,
                    'is_owner' => $project->user_id === Auth::id(), // Para saber si redirigir a Projects o Collaborations
                    'created_at' => $project->created_at,
                ];
            });

        /* --------- TAREAS CON FECHA LÍMITE PRÓXIMA --------- */
        $deadlines = Task::where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->with('project')
            ->take(4)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project_title' => $task->project ? $task->project->title : 'Sin proyecto',
                    'date' => $task->due_date,
                    'priority' => $task->priority,
                ];
            });

        /* Renderizado con Inertia */
        return Inertia::render('Dashboard/Dashboard', [
            'counts' => [
                'projects' => $totalProjects,
                'tasks' => $activeTasks,
                'team' => $teamMembers
            ],
            'projects' => $recentProjects,
            'deadlines' => $deadlines
        ]);
    }
}