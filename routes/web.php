<?php

use App\Http\Controllers\Auth\LineOAuthController;
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

Route::get('/', function () {
    return view('welcome');
})->name('home');
// LINEの認証画面に遷移
Route::get('auth/line', [LineOAuthController::class, 'redirectToProvider'])->name('line.login');

Route::get('user/home', function () {
    return view('user/home');
})->name('user.home');

// 認証後にリダイレクトされるURL(コールバックURL)
Route::get('auth/line/callback', [LineOAuthController::class, 'handleProviderCallback']);
