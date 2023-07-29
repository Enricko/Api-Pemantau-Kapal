<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/kapal',[\App\Http\Controllers\Api\Kapal::class,'index']);
Route::get('/get_kapal',[\App\Http\Controllers\Api\Kapal::class,'getKapal']);
Route::post('/insert_kapal',[\App\Http\Controllers\Api\Kapal::class,'insertKapal']);

Route::get('/get_all_coor',[\App\Http\Controllers\Api\Coordinate::class,'getKapalAllCoor']);
Route::get('/insert_coor_GGA',[\App\Http\Controllers\Api\Coordinate::class,'insertCoor']);