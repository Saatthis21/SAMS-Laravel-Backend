<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CocurriculumController;
use App\Http\Controllers\PusatAdabAuthController;
use App\Http\Controllers\ReportController;

// ===========================
// AUTH ROUTES
// ===========================
Route::post('/login', [AuthController::class, 'login']);
Route::get('/setup', [AuthController::class, 'setupMyAccount']);

// ===========================
// CO-CURRICULUM ROUTES
// ===========================
Route::get('/cocurriculum/available', [CocurriculumController::class, 'getAvailableSubjects']);
Route::get('/cocurriculum/pending/all', [CocurriculumController::class, 'getPendingClaims']);
Route::get('/cocurriculum/{studentID}', [CocurriculumController::class, 'getByStudent']);
Route::post('/cocurriculum/register', [CocurriculumController::class, 'register']);
Route::post('/cocurriculum/claim/{id}', [CocurriculumController::class, 'claimCredit']);
Route::post('/cocurriculum/approve/{id}', [CocurriculumController::class, 'approveCredit']);
Route::post('/cocurriculum/reject/{id}', [CocurriculumController::class, 'rejectCredit']);

// ===========================
// PUSAT ADAB ROUTES
// ===========================
Route::post('/pusatadab/login', [PusatAdabAuthController::class, 'login']);

// ===========================
// REPORTS ROUTES
// ===========================
Route::prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generateReport']);
});