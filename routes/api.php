<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::post('/send_reset_password_email',[PasswordResetController::class,'send_reset_password_email']);
Route::post('/reset_password/{token}',[PasswordResetController::class,'reset']);

//Protected Routes (when user is logged in)
Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout',[UserController::class,'logout']);
    Route::get('/logged_user',[UserController::class,'logged_user']);
    Route::post('/change_password',[UserController::class,'change_password']);
});
