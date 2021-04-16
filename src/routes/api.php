<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\VisitTimeController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('change-profile', [RegisterController::class, 'changeProfile']);
Route::post('change-password', [RegisterController::class, 'changePassword']);
Route::post('create-doctor', [DoctorController::class, 'create']);
Route::post('filter-doctor', [RegisterController::class, 'filter']);
Route::post('create-visit-time', [VisitTimeController::class, 'create_visit_time']);
Route::post('show-visit-time', [DoctorController::class, 'show_visit_time']);
Route::post('request-visit-time', [RegisterController::class, 'request_visit_time']);
Route::post('show-user-visit', [RegisterController::class, 'show_request_visit']);
Route::post('create-favourite', [RegisterController::class, 'favourite']);
Route::post('show-favourite', [RegisterController::class, 'show_favourite']);
Route::post('create-comment', [RegisterController::class, 'comment']);
Route::post('show-doctor-comment', [RegisterController::class, 'show_doctor_comment']);
Route::post('show-user-comment', [RegisterController::class, 'show_user_comment']);
