<?php
/*
|--------------------------------------------------------------------------
| Controladores
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Librerías
|--------------------------------------------------------------------------
*/
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
|Ruta principal
|--------------------------------------------------------------------------

*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
|Ruta protegida
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    /* Rutas del dashboard */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Rutas de los proyectos */
    Route::resource('projects', ProjectController::class);
    Route::post('/users/search', [ProjectController::class, 'searchUsers'])->name('users.search');
    Route::post('/projects/{project}/users', [ProjectController::class, 'addCollaborator'])->name('projects.users.add');
    Route::delete('/projects/{project}/users/{user}', [ProjectController::class, 'removeCollaborator'])->name('projects.users.remove');
    
     /* Rutas de las tareas */
    Route::resource('task', TaskController::class);

    /* Rutas de los subtareas */
    Route::resource('tasks.subtasks', SubtaskController::class)
        ->shallow()
        ->only(['store', 'update', 'destroy']);

    Route::patch('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggle'])
        ->name('subtasks.toggle');

    /* Rutas de las colaboraciones */
    Route::resource('collaborations', CollaborationController::class)
        ->only(['index', 'show']);

    /* Rutas de los comentarios */
    Route::post('/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
});

require __DIR__.'/auth.php';