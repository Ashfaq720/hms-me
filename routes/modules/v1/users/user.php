<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\HomeController;
use App\Http\Controllers\Settings\UserController;

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
Route::resource('users', UserController::class);