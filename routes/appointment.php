<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MasterData\AppointmentPriorityController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\MasterData\DoctorShiftController;
use App\Http\Controllers\MasterData\DoctorSlotController;
use App\Http\Controllers\MasterData\ShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/appointments/get-doctor-fee', [AppointmentController::class, 'getDoctorFee'])->name('appointments.get-doctor-fee');
    Route::post('/appointments/get-slots', [AppointmentController::class, 'getSlots'])->name('appointments.get-slots');
    Route::post('/appointments/get-doctor-shifts', [AppointmentController::class, 'getDoctorShifts'])->name('appointments.get-doctor-shifts');
    Route::get('/appointments/doctor-wise', [AppointmentController::class, 'doctorWise'])->name('appointments.doctor-wise');
    Route::get('/appointments/patient-queue', [PatientQueueController::class, 'index'])->name('patient-queue.index');
    Route::post('/appointments/patient-queue/slots', [PatientQueueController::class, 'slots'])->name('patient-queue.slots');
    Route::resource('/appointments', AppointmentController::class);

    Route::resource('/appointment-priorities', AppointmentPriorityController::class)->except(['create', 'show', 'edit']);
    Route::resource('/shifts', ShiftController::class)->except(['create', 'show', 'edit']);

    Route::get('/doctor-shifts', [DoctorShiftController::class, 'index'])->name('doctor-shifts.index');
    Route::post('/doctor-shifts/toggle', [DoctorShiftController::class, 'toggle'])->name('doctor-shifts.toggle');

    Route::get('/doctor-slots', [DoctorSlotController::class, 'index'])->name('doctor-slots.index');
    Route::post('/doctor-slots/fetch', [DoctorSlotController::class, 'fetch'])->name('doctor-slots.fetch');
    Route::post('/doctor-slots/save', [DoctorSlotController::class, 'save'])->name('doctor-slots.save');
});
