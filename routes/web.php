<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PanelistController;
use App\Http\Controllers\LeaderboardController;

// Public Survey Portal
Route::get('/', [SurveyController::class, 'index'])->name('survey.index');
Route::post('/survey', [SurveyController::class, 'store'])->name('survey.store');

// Admin Authentication
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/leaderboard', [LeaderboardController::class, 'index'])->name('admin.leaderboard');
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/admin/literacy', [AdminController::class, 'literacyForm'])->name('admin.literacy');
    Route::post('/admin/literacy', [AdminController::class, 'literacyStore'])->name('admin.literacy.store');
    Route::get('/admin/panel', [PanelistController::class, 'index'])->name('admin.panel');
    Route::post('/admin/panel', [PanelistController::class, 'store'])->name('admin.panel.store');
});
