<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UploadController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
Route::get('/patients/{id}', [App\Http\Controllers\PatientController::class, 'show'])->name('patients.show');
Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

require __DIR__.'/auth.php';