<?php

namespace App\Http\Controllers;
/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Project;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /* 
    / Método para listar proyectos
    / return: Vista con lista de proyectos
    */
    public function index()
    {
        $projects = Project::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects
        ]);
    }

    /* 
    / Método para crear proyectos
    / return: Redirige a la lista de proyectos
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $request->user()->projects()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'in_progress'
        ]);
        return redirect()->route('projects.index');
    }

    /* 
    / Método para mostrar proyectos
    / return: Vista con detalles del proyecto
    */
    public function show(Project $project)
        {
            /* Cargamos:
            // 1. 'user': El dueño/líder del proyecto.
            // 2. 'users': Los colaboradores (tabla pivote project_user).
            // 3. 'tasks.subtasks': Las tareas y sus subtareas.
            // 4. 'tasks.assignee': Quién está asignado a cada tarea.
            // 5. 'comments.user': Comentarios del proyecto con sus autores.
            // 6. 'tasks.comments.user': Comentarios de las tareas con sus autores.
            */
            $project->load(['user', 'users', 'tasks.subtasks', 'tasks.assignee', 'comments.user','tasks.comments.user']);

            /* Cálculo de Progreso */
            $totalTasks = $project->tasks->count();
            $completedTasks = $project->tasks->where('status', 'completed')->count();
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            return Inertia::render('Projects/Show', [
                'project' => $project,
                'owner' => $project->user, 
                'collaborators' => $project->users,
                'tasks' => $project->tasks,
                'progress' => $progress
            ]);
        }

    /* 
    / Método para actualizar proyectos
    / return: Recarga la misma página con datos nuevos
    */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:pending,in_progress,completed'
        ]);
        $project->update($validated);
        return back();
    }

    /* 
    / Método para eliminar proyectos
    / return: Redirige a la lista de proyectos
    */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index');
    }

    /* 
    / Método para buscar usuarios
    / return: JSON con el usuario encontrado o null
    */
    public function searchUsers(Request $request)
    {
        $search = $request->input('email');
        $projectId = $request->input('project_id');
        $project = Project::find($projectId);
        
        /* Parametros de busqueda del usuario:
        // 1. Tenga ese email exacto.
        // 2. NO sea el dueño del proyecto actual.
        // 3. NO sea ya colaborador del proyecto.
        */
        $user = User::where('email', $search)
            ->where('id', '!=', $project->user_id)
            ->whereDoesntHave('projects', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->first();

        return response()->json($user); 
    }

    /*
    / Método para agregar colaboradores
    / return: Recarga la misma página con datos nuevos
    */
    public function addCollaborator(Request $request, Project $project)
        {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);
            // syncWithoutDetaching para evitar duplicados en la tabla pivote
            $project->users()->syncWithoutDetaching([$request->user_id]);
            return back();
        }
        
    /*
    / Método para eliminar colaboradores
    / return: Recarga la misma página con datos nuevos
    */
    public function removeCollaborator(Project $project, User $user)
    {
        $project->users()->detach($user->id);
        return back();
    }
}