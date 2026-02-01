<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DispenseController;


Route::get('/school-data', [StudentController::class, 'getSchoolData']);

Route::get('/check-lrn/{lrn}', [StudentController::class, 'checkLRN']);

Route::post('/register', [StudentController::class, 'register']);

Route::post('/assess', [AssessmentController::class, 'evaluate']);

Route::post('/dispense', [DispenseController::class, 'dispense']);
