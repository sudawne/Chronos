<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\MeetingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/check-in', [CheckInController::class, 'submitCheckIn']);
Route::post('/meetings/{meeting}/checkin', [MeetingController::class, 'checkIn']);