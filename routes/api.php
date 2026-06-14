<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;

Route::get('/setup-account', [App\Http\Controllers\AuthController::class, 'setupMyAccount']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 2. Your custom SAMS routes!
Route::get('/courses', [RegistrationController::class, 'fetchAvailableCourses']);
Route::get('/courses/{course_code}/labs', [RegistrationController::class, 'fetchCourseLabs']);
Route::post('/add-course', [RegistrationController::class, 'addCourseToDraft']);
Route::get('/my-courses/{studentID}', [RegistrationController::class, 'getMyCourses']);


//Manage Fee Routes
Route::get('/fee/summary/{student_id}', [PaymentController::class, 'getFeeSummary']);
Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']);
Route::get('/payment/history/{student_id}', [PaymentController::class, 'getPaymentHistory']);
Route::get('/block/status/{student_id}', [PaymentController::class, 'getBlockStatus']);

Route::post('/fee/set', [PaymentController::class, 'setFee']);
Route::put('/fee/update/{id}', [PaymentController::class, 'updateFee']);