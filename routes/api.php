<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CocurriculumController;

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
Route::get('/courses/{course_code}/labs', [RegistrationController::class, 'fetchCourseLabs']);
Route::post('/add-course', [RegistrationController::class, 'addCourseToDraft']);
Route::get('/my-courses/{studentID}', [RegistrationController::class, 'getMyCourses']);
Route::delete('/drop-course/{registeredID}', [RegistrationController::class, 'dropCourseFromDraft']);
Route::post('/change-lab/{registeredID}', [RegistrationController::class, 'changeLabSection']);
Route::post('/notify-faculty/{studentID}', [RegistrationController::class, 'submitRegistration']);
Route::get('/pending-registrations', [RegistrationController::class, 'fetchPendingSubmissions']);
Route::get('/review-submission/{submissionID}', [RegistrationController::class, 'fetchSubmissionDetails']);
Route::post('/review-decision', [RegistrationController::class, 'processReviewDecision']);

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


/*
|--------------------------------------------------------------------------
| Manage Student Attendance Routes (SAMS-PACK-3XX)
|--------------------------------------------------------------------------
*/
Route::prefix('attendance')->group(function () {
    // 1. Start a new session (Lecturer)
    Route::post('/start', [AttendanceController::class, 'startSession']);

    // 2. Submit GPS Check-in (Student)
    Route::post('/checkIn', [AttendanceController::class, 'checkIn']);

    // 3. Fetch the Report (Lecturer) -> Double prefix removed!
    Route::post('/getAttendanceReport', [AttendanceController::class, 'getAttendanceReport']);

    // 4. Live Polling Counter (Lecturer) -> Double prefix removed!
    Route::get('/count/{session_id}', [AttendanceController::class, 'getLiveCount']);
});

/*
|--------------------------------------------------------------------------
| Manage Report Module (SAMS-PACK-5XX)
|--------------------------------------------------------------------------
*/
Route::prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generateReport']);
});

// Co-curriculum routes
Route::get('/cocurriculum/{studentID}', [CocurriculumController::class, 'getByStudent']);
Route::post('/cocurriculum/claim/{id}', [CocurriculumController::class, 'claimCredit']);
Route::post('/cocurriculum/approve/{id}', [CocurriculumController::class, 'approveCredit']);
Route::post('/cocurriculum/reject/{id}', [CocurriculumController::class, 'rejectCredit']);
Route::get('/cocurriculum/pending/all', [CocurriculumController::class, 'getPendingClaims']);
`

//pusat adab routes
use App\Http\Controllers\PusatAdabAuthController;

Route::post('/pusatadab/login', [PusatAdabAuthController::class, 'login']);