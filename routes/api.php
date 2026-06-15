<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/setup-account', [AuthController::class, 'setupMyAccount']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Subject Registration Module
|--------------------------------------------------------------------------
*/

Route::get('/courses', [RegistrationController::class, 'fetchAvailableCourses']);
Route::get('/courses/{course_code}/labs',[RegistrationController::class, 'fetchCourseLabs']);
Route::post('/add-course',[RegistrationController::class, 'addCourseToDraft']);
Route::get('/my-courses/{studentID}',[RegistrationController::class, 'getMyCourses']);

/*
|--------------------------------------------------------------------------
| Manage Fee - Student APIs
|--------------------------------------------------------------------------
*/

// View Fee Summary
Route::get('/fee/summary/{student_id}',[PaymentController::class, 'getFeeSummary']);
// Make Payment
Route::post('/payment/initiate',[PaymentController::class, 'initiatePayment']);
// Payment History
Route::get('/payment/history/{student_id}',[PaymentController::class, 'getPaymentHistory']);
// Receipt
Route::get('/receipt/{receipt_id}',[PaymentController::class, 'getReceipt']);
// Block Status
Route::get('/block/status/{student_id}',[PaymentController::class, 'getBlockStatus']);

/*
|--------------------------------------------------------------------------
| Manage Fee - Treasury APIs
|--------------------------------------------------------------------------
*/

// Treasury Dashboard
Route::get('/treasury/dashboard',[PaymentController::class, 'getDashboard']);
// Set Fee Structure
Route::post('/fee/set',[PaymentController::class, 'setFee']);
// Update Fee Structure
Route::put('/fee/update/{id}',[PaymentController::class, 'updateFee']);
// View All Payment Records
Route::get('/treasury/payments',[PaymentController::class, 'getAllPayments']);
// View Outstanding Students
Route::get('/treasury/outstanding',[PaymentController::class, 'getOutstandingStudents']);
Route::get('/fee/list',[PaymentController::class, 'getFeeList']);
// 1. Course Registration SAMS routes!
Route::get('/courses', [RegistrationController::class, 'fetchAvailableCourses']);
Route::get('/courses/{course_code}/labs', [RegistrationController::class, 'fetchCourseLabs']);
Route::post('/add-course', [RegistrationController::class, 'addCourseToDraft']);
Route::get('/my-courses/{studentID}', [RegistrationController::class, 'getMyCourses']);
Route::delete('/drop-course/{registeredID}', [RegistrationController::class, 'dropCourseFromDraft']);
Route::post('/change-lab/{registeredID}', [RegistrationController::class, 'changeLabSection']);
Route::post('/notify-faculty/{studentID}', [RegistrationController::class, 'submitRegistration']);
Route::get('/pending-registrations', [RegistrationController::class, 'fetchPendingSubmissions']);
Route::get('/review-submission/{submissionID}', [RegistrationController::class, 'fetchSubmissionDetails']);
Route::post('/review-decision', [RegistrationController::class, 'processReviewDecision']);

// Manage Student Attendance Routes (SAMS-PACK-3XX)
Route::prefix('attendance')->group(function () {
    Route::post('/initiateSession', [AttendanceController::class, 'initiateSession']);
    Route::post('/checkIn', [AttendanceController::class, 'checkIn']);
    Route::get('/getAttendanceReport', [AttendanceController::class, 'getAttendanceReport']);
    Route::post('/exportSessionData', [AttendanceController::class, 'exportSessionData']);
});

// Manage Report Module (SAMS-PACK-5XX)
Route::prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generateReport']);
});
