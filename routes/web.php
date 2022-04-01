<?php

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

Route::get('/admin', function () {
    return redirect()->route('admin.home');
});

//Route::get('/quanlytram', [\App\Http\Controllers\Broadcaster\IndexController::class, 'index'])->name('broadcaster.index');

Route::get('/video', 'VideoController@link')->name('playVideo');