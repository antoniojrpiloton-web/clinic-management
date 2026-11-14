<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ClinicLogbookController;
use App\Http\Controllers\ClinicVisitController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DentalConsultationController;
use App\Http\Controllers\DiseaseCategoryController;
use App\Http\Controllers\EmergencyRecordController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FirstAidRecordController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\MedicalConsultationController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\MilestoneStubController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Models\ClinicVisit;
use Illuminate\Support\Facades\Route;

Route::controller(ClinicLogbookController::class)->prefix('logbook')->name('logbook.')->group(function () {
    Route::get('/', 'kiosk')->name('kiosk');
    Route::get('lookup', 'lookupPatient')->name('lookup');
    Route::post('/', 'kioskStore')->name('kiosk.store');
    Route::post('{clinicLogbook}/time-out', 'kioskTimeOut')->name('kiosk.time-out');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');
    Route::get('global-search', GlobalSearchController::class)->name('global-search');

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::post('read-all', 'markAllAsRead')->name('read-all');
        Route::get('{notification}', 'show')->name('show');
    });

    Route::controller(Select2Controller::class)->prefix('select2')->name('select2.')->group(function () {
        Route::get('patients', 'patients')->name('patients');
        Route::get('clinicians', 'clinicians')->name('clinicians');
        Route::get('colleges', 'colleges')->name('colleges');
        Route::get('programs', 'programs')->name('programs');
        Route::get('academic-years', 'academicYears')->name('academic-years');
        Route::get('users', 'users')->name('users');
        Route::get('roles', 'roles')->name('roles');
        Route::get('medicines', 'medicines')->name('medicines');
        Route::get('medicine-batches', 'medicineBatches')->name('medicine-batches');
    });

    Route::get('clinicians', [MilestoneStubController::class, 'phase3'])->name('clinicians.index');

    Route::controller(PatientController::class)->prefix('patients')->name('patients.')->group(function () {
        Route::get('check-duplicate', 'checkDuplicate')->name('check-duplicate');
        Route::get('import', 'importForm')->name('import');
        Route::post('import', 'importProcess')->name('import.process');
    });
    Route::resource('patients', PatientController::class);

    Route::resource('students', StudentController::class)->except('show');
    Route::resource('employees', EmployeeController::class)->except('show');

    Route::controller(ClinicLogbookController::class)->prefix('clinic-logbooks')->name('clinic-logbooks.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('{clinicLogbook}/time-out', 'timeOut')->name('time-out');
        Route::delete('{clinicLogbook}', 'destroy')->name('destroy');
    });

    Route::controller(AppointmentController::class)->prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/calendar', 'calendar')->name('calendar');
        Route::post('/', 'store')->name('store');
        Route::post('{appointment}/check-in', 'checkIn')->name('check-in');
        Route::post('{appointment}/start-consultation', 'startConsultation')->name('start-consultation');
        Route::post('{appointment}/cancel', 'cancel')->name('cancel');
        Route::delete('{appointment}', 'destroy')->name('destroy');
    });

    Route::controller(MedicalProfileController::class)->prefix('patients/{patient}/medical-profile')->name('medical-profiles.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('edit', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    // Milestone 3 Planned: Physical Examinations (Scheduled Phase 3 Release)
    Route::get('physical-examinations', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.index');
    Route::get('physical-examinations/create', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.create');
    Route::get('physical-examinations/{any}/edit', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.edit');
    Route::get('physical-examinations/{any}/print-pe', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.print-pe');
    Route::get('physical-examinations/{any}/print-certificate', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.print-certificate');
    Route::get('physical-examinations/{any}/print-referral', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.print-referral');
    Route::get('physical-examinations/{any}/print-exemption', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.print-exemption');
    Route::get('physical-examinations/{any}', [MilestoneStubController::class, 'phase3'])->name('physical-examinations.show');

    Route::controller(ClinicVisitController::class)->prefix('clinic-visits')->name('clinic-visits.')->group(function () {
        Route::get('{clinicVisit}/print', 'printSlip')->name('print');
        Route::get('{clinicVisit}/print-certificate', 'printCertificate')->name('print-certificate');
        Route::get('{clinicVisit}/print-referral', 'printReferral')->name('print-referral');
    });
    Route::resource('clinic-visits', ClinicVisitController::class);

    Route::model('medical_consultation', ClinicVisit::class);
    Route::model('dental_consultation', ClinicVisit::class);
    Route::model('emergency_record', ClinicVisit::class);
    Route::model('first_aid_record', ClinicVisit::class);

    Route::resource('medical-consultations', MedicalConsultationController::class);

    Route::controller(DentalConsultationController::class)->prefix('dental-consultations')->name('dental-consultations.')->group(function () {
        Route::get('{dental_consultation}/print-card', 'printCard')->name('print-card');
    });
    Route::resource('dental-consultations', DentalConsultationController::class);
    Route::resource('emergency-records', EmergencyRecordController::class);
    Route::resource('first-aid-records', FirstAidRecordController::class);

    // Milestone 3 Planned: Schedules & Pharmacy Operations (Scheduled Phase 3 Release)
    Route::get('physical-exam-schedules', [MilestoneStubController::class, 'phase3'])->name('physical-exam-schedules.index');
    Route::get('physical-exam-schedules/{any}', [MilestoneStubController::class, 'phase3'])->name('physical-exam-schedules.show');

    Route::get('medicine-categories', [MilestoneStubController::class, 'phase3'])->name('medicine-categories.index');
    Route::get('medicines', [MilestoneStubController::class, 'phase3'])->name('medicines.index');
    Route::get('medicines/{any}', [MilestoneStubController::class, 'phase3'])->name('medicines.show');

    Route::get('inventory/stock-in', [MilestoneStubController::class, 'phase3'])->name('inventory.stock-in');
    Route::get('inventory/stock-out', [MilestoneStubController::class, 'phase3'])->name('inventory.stock-out');
    Route::get('inventory/expirations', [MilestoneStubController::class, 'phase3'])->name('inventory.expirations');
    Route::get('inventory/transactions', [MilestoneStubController::class, 'phase3'])->name('inventory.transactions');
    Route::get('inventory/reports', [MilestoneStubController::class, 'phase3'])->name('inventory.reports');
    Route::get('inventory/dispensings', [MilestoneStubController::class, 'phase3'])->name('inventory.dispensings.index');
    Route::get('inventory/dispensings/create', [MilestoneStubController::class, 'phase3'])->name('inventory.dispensings.create');
    Route::get('inventory/dispensings/{any}/print', [MilestoneStubController::class, 'phase3'])->name('inventory.dispensings.print');
    Route::get('inventory/dispensings/{any}', [MilestoneStubController::class, 'phase3'])->name('inventory.dispensings.show');

    // Milestone 4 Planned: Reports, Statistics & Official Records (Scheduled Phase 4 Release)
    Route::get('reports/clinic', [MilestoneStubController::class, 'phase4'])->name('reports.clinic.index');
    Route::get('reports/clinic/medical-dental-records', [MilestoneStubController::class, 'phase4'])->name('reports.clinic.medical-dental');
    Route::get('reports/clinic/login-records', [MilestoneStubController::class, 'phase4'])->name('reports.clinic.login-records');
    Route::get('reports/clinic/communicable-diseases', [MilestoneStubController::class, 'phase4'])->name('reports.clinic.communicable');
    Route::get('reports/clinic/non-communicable-diseases', [MilestoneStubController::class, 'phase4'])->name('reports.clinic.non-communicable');

    Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
    Route::resource('academic-years', AcademicYearController::class)->except(['create', 'edit', 'show']);
    Route::resource('colleges', CollegeController::class)->except(['create', 'edit', 'show']);
    Route::resource('programs', ProgramController::class)->except(['create', 'edit', 'show']);
    Route::resource('disease-categories', DiseaseCategoryController::class)->except(['create', 'edit', 'show']);

    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('permissions', PermissionController::class)->except('show');

    Route::controller(AuditLogController::class)->prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{auditLog}', 'show')->name('show');
    });

    Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    // Milestone 4 Planned: Database Backups
    Route::get('backups', [MilestoneStubController::class, 'phase4'])->name('backups.index');
});
