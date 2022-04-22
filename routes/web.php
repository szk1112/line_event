<?php

use App\Http\Controllers\Auth\LineOAuthController;
use App\Http\Controllers\Auth\LoginController;
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
Route::group(['middleware' => 'web'], function() {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');
    Route::get('/privacy', function () {
        return view('welcome');
    })->name('privacy');
    // LINEの認証画面に遷移
    Route::get('auth/line', [LineOAuthController::class, 'redirectToProvider'])->name('line.login');

    Route::get('user/home', function () {
        return view('user/home');
    })->name('user.home');

    // 認証後にリダイレクトされるURL(コールバックURL)
    Route::get('auth/line/callback', [LineOAuthController::class, 'callback']);
    // 認証後にリダイレクトされるURL(コールバックURL)
//    Route::get('line_bot/link', [LineBotLinkController::class, 'link']);

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('my.home');

    // 認証
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('logout', [LoginController::class, 'logout']);

    //LINE連携Done
    Route::get('/line/link/done', function () {
        return view('line/connect');
    })->name('line.connect');

});
