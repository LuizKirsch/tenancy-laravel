<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomePrincipalController;
use App\Http\Controllers\AddTenancyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [HomePrincipalController::class, 'index'])->name('home');

Route::get('/add-tenancy', [AddTenancyController::class, 'index'])->name('add-tenancy');
Route::post('/create_action', [AddTenancyController::class, 'create_action'])->name('create_action');

// Rota de login
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login_action'])->name('user.login_action');

// Rota de logout
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
