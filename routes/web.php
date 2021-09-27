<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\SiteController;
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

Route::get('netlify', [AccountController::class, 'index'])->name('accounts.index');
Route::post('netlify/add', [AccountController::class, 'add'])->name('accounts.add');
Route::patch('apis/status/{account}', [AccountController::class, 'status'])->name('accounts.status');
Route::delete('apis/delete/{account}', [AccountController::class, 'delete'])->name('accounts.delete');

Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('sites/{account}', [SiteController::class, 'sites'])->name('sites.load');
Route::get('sites/{account}/{site}/{identity}', [SiteController::class, 'identity'])->name('sites.identity');
Route::post('sites/{account}/{site}/{identity}', [SiteController::class, 'identityActions'])->name('sites.action');
