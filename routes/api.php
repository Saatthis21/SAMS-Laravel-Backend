<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;

Route::get('/setup-account', [App\Http\Controllers\AuthController::class, 'setupMyAccount']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 1. Course Registration SAMS routes!
Route::get('/courses', [RegistrationController::class, 'fetchAvailableCourses']);
Route::get('/courses/{course_code}/labs', [RegistrationController::class, 'fetchCourseLabs']);
Route::post('/add-course', [RegistrationController::class, 'addCourseToDraft']);
Route::get('/my-courses/{studentID}', [RegistrationController::class, 'getMyCourses']);
Route::delete('/drop-course/{registeredID}', [RegistrationController::class, 'dropCourse']);
Route::put('/update-lab/{registeredID}', [RegistrationController::class, 'updateLabSection']);
Route::post('/notify-faculty/{studentID}', [RegistrationController::class, 'submitRegistration']);
Route::get('/pending-registrations', [RegistrationController::class, 'fetchPendingSubmissions']);
Route::get('/review-submission/{submissionID}', [RegistrationController::class, 'fetchSubmissionDetails']);
Route::post('/review-decision', [RegistrationController::class, 'processReviewDecision']);
