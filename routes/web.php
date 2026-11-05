<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClinicianController;
use App\Http\Controllers\ClinicReportController;
use App\Http\Controllers\ClinicVisitController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DentalConsultationController;
use App\Http\Controllers\EmergencyRecordController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FirstAidRecordController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MedicalConsultationController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineDispensingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PhysicalExaminationController;
use App\Http\Controllers\PhysicalExamScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Models\ClinicVisit;
use App\Models\PhysicalExamScheduleAttendee;
use Illuminate\Support\Facades\Route;

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

    Route::resource('clinicians', ClinicianController::class);

    Route::controller(PatientController::class)->prefix('patients')->name('patients.')->group(function () {
        Route::get('check-duplicate', 'checkDuplicate')->name('check-duplicate');
        Route::get('import', 'importForm')->name('import');
        Route::post('import', 'importProcess')->name('import.process');
    });
    Route::resource('patients', PatientController::class);

    Route::resource('students', StudentController::class)->except('show');
    Route::resource('employees', EmployeeController::class)->except('show');

    Route::controller(MedicalProfileController::class)->prefix('patients/{patient}/medical-profile')->name('medical-profiles.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('edit', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    Route::controller(PhysicalExaminationController::class)->prefix('physical-examinations')->name('physical-examinations.')->group(function () {
        Route::get('{physicalExamination}/print-pe', 'printPe')->name('print-pe');
        Route::get('{physicalExamination}/print-certificate', 'printCertificate')->name('print-certificate');
        Route::get('{physicalExamination}/print-exemption', 'printExemption')->name('print-exemption');
        Route::get('{physicalExamination}/print-referral', 'printReferral')->name('print-referral');
    });
    Route::resource('physical-examinations', PhysicalExaminationController::class);

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
    Route::model('attendee', PhysicalExamScheduleAttendee::class);

    Route::resource('medical-consultations', MedicalConsultationController::class);

    Route::controller(DentalConsultationController::class)->prefix('dental-consultations')->name('dental-consultations.')->group(function () {
        Route::get('{dental_consultation}/print-card', 'printCard')->name('print-card');
    });
    Route::resource('dental-consultations', DentalConsultationController::class);
    Route::resource('emergency-records', EmergencyRecordController::class);
    Route::resource('first-aid-records', FirstAidRecordController::class);

    Route::controller(PhysicalExamScheduleController::class)->prefix('physical-exam-schedules')->name('physical-exam-schedules.')->group(function () {
        Route::get('{physicalExamSchedule}/print-roster', 'printRoster')->name('print-roster');
        Route::post('{physicalExamSchedule}/attendees', 'addAttendee')->name('add-attendee');
        Route::post('{physicalExamSchedule}/populate-attendees', 'populateAttendees')->name('populate-attendees');
        Route::post('{physicalExamSchedule}/attendees/{attendee}/status', 'updateAttendeeStatus')->name('update-attendee-status');
        Route::delete('{physicalExamSchedule}/attendees/{attendee}', 'removeAttendee')->name('remove-attendee');
    });
    Route::resource('physical-exam-schedules', PhysicalExamScheduleController::class);

    Route::resource('medicine-categories', MedicineCategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('medicines', MedicineController::class);

    Route::controller(InventoryController::class)->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('stock-in', 'stockInForm')->name('stock-in');
        Route::post('stock-in', 'stockInProcess')->name('stock-in.process');
        Route::get('stock-out', 'stockOutForm')->name('stock-out');
        Route::post('stock-out', 'stockOutProcess')->name('stock-out.process');
        Route::get('expirations', 'expirations')->name('expirations');
        Route::post('batches/{batch}/dispose', 'disposeBatch')->name('batches.dispose');
        Route::get('transactions', 'transactions')->name('transactions');
        Route::get('reports', 'reports')->name('reports');
    });

    Route::controller(ClinicReportController::class)->prefix('reports/clinic')->name('reports.clinic.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('print-pdf', 'printPdf')->name('print-pdf');
    });

    Route::controller(MedicineDispensingController::class)->prefix('inventory/dispensings')->name('inventory.dispensings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{dispensing}', 'show')->name('show');
        Route::get('{dispensing}/print', 'printSlip')->name('print');
    });

    Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
    Route::resource('academic-years', AcademicYearController::class)->except(['create', 'edit', 'show']);
    Route::resource('colleges', CollegeController::class)->except(['create', 'edit', 'show']);
    Route::resource('programs', ProgramController::class)->except(['create', 'edit', 'show']);

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

    Route::controller(BackupController::class)->prefix('backups')->name('backups.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{backup}/download', 'download')->name('download');
        Route::delete('{backup}', 'destroy')->name('destroy');
    });
});
