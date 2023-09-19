<?php

use Illuminate\Support\Facades\Route;

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
Route::post('/receive-nmea', [\App\Http\Controllers\Api\Coordinate::class, 'receive']);

Route::get("/send-event",function (){
    broadcast(new \App\Events\HelloEvent());
});
