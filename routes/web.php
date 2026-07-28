<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PanelistController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AssessmentController;

/*
|--------------------------------------------------------------------------
| Web Routes - Play It Forward E-Hub
|--------------------------------------------------------------------------
*/

// Step 2 & 4: Dual-Section Landing Page (Auth & Public Survey CTA)
Route::get('/', [SurveyController::class, 'landing'])->name('landing');

// Public Survey Access Engine & Access Key Verification
Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
Route::post('/surveys/verify', [SurveyController::class, 'verifyKey'])->name('surveys.verify');
Route::get('/surveys/{assessment}/take', [SurveyController::class, 'take'])->name('surveys.take');
Route::post('/surveys/{assessment}/submit', [SurveyController::class, 'submit'])->name('surveys.submit');

// Legacy Baseline/Endline Survey Submission
Route::get('/legacy-survey', [SurveyController::class, 'index'])->name('survey.index');
Route::post('/survey', [SurveyController::class, 'store'])->name('survey.store');

// Admin & Panelist Authentication
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Protected Admin & Evaluator Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/leaderboard', [LeaderboardController::class, 'index'])->name('admin.leaderboard');
    Route::get('/admin/scoresheet', [LeaderboardController::class, 'scoresheetCsv'])->name('admin.scoresheet');
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/admin/survey-export', [AdminController::class, 'exportSurveyCsv'])->name('admin.survey.export');
    Route::get('/admin/literacy', [AdminController::class, 'literacyForm'])->name('admin.literacy');
    Route::post('/admin/literacy', [AdminController::class, 'literacyStore'])->name('admin.literacy.store');
    Route::get('/admin/panel', [PanelistController::class, 'index'])->name('admin.panel');
    Route::post('/admin/panel', [PanelistController::class, 'store'])->name('admin.panel.store');

    // Dynamic Assessment Management System & Evaluator Grading
    Route::resource('admin/assessments', AssessmentController::class);
    Route::get('admin/assessments/{assessment}/evaluate', [AssessmentController::class, 'evaluateForm'])->name('assessments.evaluate');
    Route::post('admin/assessments/{assessment}/evaluate', [AssessmentController::class, 'submitEvaluation'])->name('assessments.submit-evaluation');
});
