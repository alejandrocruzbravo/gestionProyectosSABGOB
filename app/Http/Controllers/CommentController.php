<?php

namespace App\Http\Controllers;
/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /* 
    / Método para crear comentarios
    / return: Redirige a la página anterior
    */
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|in:project,task,subtask' // Validación de seguridad
        ]);

        // Mapeo simple de string a Clase Real
        $modelClass = match ($request->commentable_type) {
            'project' => Project::class,
            'task' => Task::class,
            'subtask' => Subtask::class,
        };

        // Crear el comentario usando la relación polimórfica
        Comment::create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'commentable_type' => $modelClass,
            'commentable_id' => $request->commentable_id,
        ]);

        return back();
    }

    /* 
    / Método para eliminar comentarios
    / return: Redirige a la página anterior
    */
    public function destroy(Comment $comment)
    {
        // Solo el dueño del comentario puede borrarlo
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }
        
        $comment->delete();
        return back();
    }
}