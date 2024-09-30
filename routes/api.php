<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckAuthenticated;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum','active']], function () {
        Route::get('user-profile', [AuthController::class, 'userProfile']);

        Route::post('logout', [AuthController::class, 'logout']);
    
});

Route::get('users', [AuthController::class, 'allUsers']);

Route::resource('categorias', CategoriasController::class)
->missing(function (Request $request) {
        return Redirect::route('categorias.index');
    }
)->only('index','store','show','update','destroy');
