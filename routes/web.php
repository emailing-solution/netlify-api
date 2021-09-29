<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyProcessController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::redirect('', 'netlify');

Route::get('login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'attempt'])->name('login.attempt');
Route::get('logout', [LoginController::class, 'logout'])->name('login.logout');

Route::middleware(['auth'])->group(function () {

    Route::middleware('can:is_admin')->group(function() {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/make/{user?}', [UserController::class, 'load'])->name('users.load');
        Route::post('users/make/{user?}', [UserController::class, 'add'])->name('users.add');
        Route::patch('users/{user}', [UserController::class, 'status'])->name('users.status');
        Route::delete('users/{user}', [UserController::class, 'delete'])->name('users.delete');
    });

    Route::get('netlify', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('netlify/add', [AccountController::class, 'add'])->name('accounts.add');
    Route::get('netlify/check/{account}', [AccountController::class, 'check'])->name('accounts.check');
    Route::patch('netlify/status/{account}', [AccountController::class, 'status'])->name('accounts.status');
    Route::delete('netlify/delete/{account}', [AccountController::class, 'delete'])->name('accounts.delete');

    Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('sites/{account}', [SiteController::class, 'sites'])->name('sites.load');
    Route::get('sites/{account}/{site}/{identity}', [SiteController::class, 'identity'])->name('sites.identity');
    Route::post('sites/{account}/{site}/{identity}', [SiteController::class, 'identityActions'])->name('sites.action');

    Route::get('my-process', [MyProcessController::class, 'index'])->name('process.index');
    Route::get('my-process/{process}/logs', [MyProcessController::class, 'logs'])->name('process.logs');
    Route::delete('my-process/kill/{process}', [MyProcessController::class, 'kill'])->name('process.kill');
    Route::get('my-process/create/{account}/{site}/{identity}', [MyProcessController::class, 'get'])->name('process.get');
    Route::post('my-process/create/{account}/{site}/{identity}', [MyProcessController::class, 'create'])->name('process.create');
});

