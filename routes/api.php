<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Roles
Route::prefix('/roles')->middleware(['auth:sanctum', 'auth.admin'])->group(function () {
    Route::get('/all', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/create-role', [RoleController::class, 'store'])->name('roles.create');
    Route::patch('/update-role/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/delete-role/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/{id}', [RoleController::class, 'show'])->name('roles.show');
});


//Users
Route::prefix('/users')->group(function () {
    Route::post('/log-in', [AuthController::class, 'login'])->name('users.login');
    Route::post('/register', [AuthController::class, 'register'])->name('users.register');
    Route::patch('/forgot-password', [AuthController::class, 'forgotPassword'])->name('users.forgotPassword');
    Route::patch('/confirm-email/{token}', [AuthController::class, 'confirmMail'])->name('users.confirmMail');
    Route::patch('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('users.resetPassword');
    Route::middleware(['auth:sanctum', 'auth.email'])->group(function () {
        Route::get('/me', [UserController::class, 'showMe'])->name('users.showMe');
        Route::patch('/activate-me', [UserController::class, 'activateMe'])->name('users.activateMe');
        Route::patch('/update-me', [UserController::class, 'updateMe'])->name('users.updateMe');
        Route::middleware('auth.isactive')->group(function () {
            Route::patch('/deactivate-me', [UserController::class, 'deactivateMe'])->name('users.deactivateMe');
            Route::patch('/change-password', [AuthController::class, 'updatePassword'])->name('users.updatePassword');
            Route::post('/log-out', [AuthController::class, 'logOut'])->name('users.logOut');
        });
    });
});


//Categories
Route::prefix('/categories')->group(function () {
    Route::middleware(['auth:sanctum', 'auth.isactive', 'auth.email'])->group(function () {
        Route::get('/all', [CategoriesController::class, 'index'])->name('categories.index');
        Route::get('/category/{id}', [CategoriesController::class, 'show'])->name('categories.show');
        Route::middleware('auth.admin')->group(function () {
            Route::post('/new-category', [CategoriesController::class, 'store'])->name('categories.store');
            Route::patch('/edit-category/{id}', [CategoriesController::class, 'update'])->name('categories.update');
            Route::delete('/delete-category/{id}', [CategoriesController::class, 'destroy'])->name('categories.destroy');
        });
    });
});


//Quizzes
Route::prefix('/quizzes')->group(function () {
    Route::middleware(['auth:sanctum', 'auth.isactive', 'auth.email'])->group(function () {
        Route::get('/all', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quiz/{id}', [QuizController::class, 'showSingleQuiz'])->name('quizzes.show');
        Route::post('/quiz/save-quiz/{id}', [QuizController::class, 'saveOtherQuiz'])->name('quizzes.saveOtherQuiz');
        Route::delete('/quiz/delete-quiz/{id}', [QuizController::class, 'deleteOtherQuiz'])->name('quizzes.deleteOtherQuiz');
        Route::get('/saved-quizzes', [QuizController::class, 'getOtherQuizzes'])->name('quizzes.getOtherQuizzes');
        Route::get('/my-quizzes', [UserController::class, 'myQuizzes'])->name('quizzes.myQuizzes');
        Route::get('/my-quizzes/{id}', [UserController::class, 'myQuiz'])->name('quizzes.myQuiz');
        Route::post('/my-quizzes/create-new-quiz', [UserController::class, 'storeQuiz'])->name('quizzes.storeQuiz');
        Route::patch('/my-quizzes/update-quiz/{id}', [UserController::class, 'updateMyQuiz'])->name('quizzes.updateMyQuiz');
        Route::delete('/my-quizzes/delete-quiz/{id}', [UserController::class, 'deleteMyQuiz'])->name('quizzes.deleteMyQuiz');
    });
});


//Teams
Route::prefix('/teams')->group(function () {
    Route::middleware(['auth:sanctum', 'auth.isactive', 'auth.email'])->group(function () {
        Route::get('/all', [TeamController::class, 'getAllTeams'])->name('teams.getAllTeams');
        Route::middleware('isInTeam')->group(function () {
            Route::post('/create-team', [TeamController::class, 'createNewTeam'])->name('teams.createNewTeam');
            Route::patch('/join-team/{id}', [TeamController::class, 'joinTeam'])->name('teams.joinName');
        });
        Route::get('/members/{id}', [TeamController::class, 'displayTeamMembers'])->name('teams.displayTeamMembers');
        Route::patch('/leave-team/{id}', [TeamController::class, 'leaveTeam'])->name('teams.leaveTeam');
        Route::get('/my-teams', [TeamController::class, 'getMyTeam'])->name('teams.getMyTeam');
        Route::patch('/update-team/{id}', [TeamController::class, 'updateTeam'])->name('teams.updateTeam');
        Route::delete('/delete-team/{id}', [TeamController::class, 'deleteTeam'])->name('teams.deleteTeam');
        Route::get('/{id}', [TeamController::class, 'getSingleTeam'])->name('teams.getSingleTeam');
    });
});
