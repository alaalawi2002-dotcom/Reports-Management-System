<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ReportController;
// ============================================
// Public routes (بدون توكن)
// ============================================
Route::post('/register', [AuthController::class, 'register']);


Route::post('/login',    [AuthController::class, 'login']);

// ============================================
// Protected routes (تحتاج توكن)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);




     
    
Route::apiResource('reports', ReportController::class); 
});