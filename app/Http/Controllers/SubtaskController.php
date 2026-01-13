<?php

namespace App\Http\Controllers;
/*
|--------------------------------------------------------------------------
| Modelos
|--------------------------------------------------------------------------
*/
use App\Models\Subtask;
use App\Models\Task;
/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    /* 
    / Método para crear subtareas
    / return: Recarga la misma página con datos nuevos
    */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $task->subtasks()->create([
            'title' => $validated['title'],
            'status' => 'pending'
        ]);

        return back();
    }

    /* 
    / Método para actualizar subtareas
    / return: Recarga la misma página con datos nuevos
    */
    public function update(Request $request, Subtask $subtask)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask->update($validated);

        return back();
    }

    /* 
    / Método para eliminar subtareas
    / return: Recarga la misma página con datos nuevos
    */
    public function destroy(Subtask $subtask)
    {
        $subtask->delete();
        return back();
    }

    /* 
    / Método para alternar estado de subtareas
    / return: Recarga la misma página con datos nuevos
    */
    public function toggle(Subtask $subtask)
    {
        $newStatus = $subtask->status === 'pending' ? 'completed' : 'pending';
        $subtask->update(['status' => $newStatus]);
        return back();
    }
}