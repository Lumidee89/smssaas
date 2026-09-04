<?php

use App\Http\Controllers\AcademicSetupController;
use App\Http\Controllers\AcademicStructureController;
use App\Http\Controllers\AdminMessageController;
use App\Http\Controllers\AiCopilotController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CbtAdminController;
use App\Http\Controllers\CampusOperationsController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceAdminController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentCbtController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TranscriptController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');
Route::get('/transcripts/verify/{publicId}', [TranscriptController::class, 'verify'])->name('transcripts.verify');
Route::get('/registration/payment/callback', [AuthController::class, 'paymentCallback'])->name('registration.payment.callback');
Route::get('/health/ready', HealthController::class)->middleware('throttle:30,1')->name('health.ready');
Route::get('/student/cbt/{exam}', [StudentCbtController::class, 'show'])->middleware('throttle:120,1')->name('student.cbt.show');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware(['auth', 'school'])->group(function () {
    Route::middleware('role:super_admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/', [PlatformAdminController::class, 'index'])->name('index');
        Route::get('/schools', [PlatformAdminController::class, 'index'])->name('schools.index');
        Route::get('/users', [PlatformAdminController::class, 'index'])->name('users.index');
        Route::get('/payments', [PlatformAdminController::class, 'index'])->name('payments.index');
        Route::post('/schools', [PlatformAdminController::class, 'store'])->name('schools.store');
        Route::put('/schools/{school}/subscription', [PlatformAdminController::class, 'updateSubscription'])->name('schools.subscription');
        Route::put('/schools/{school}/brand', [PlatformAdminController::class, 'updateBrand'])->name('schools.brand');
        Route::put('/schools/{school}/domain', [PlatformAdminController::class, 'updateDomain'])->name('schools.domain');
        Route::post('/schools/{school}/domain/verify', [PlatformAdminController::class, 'verifyDomain'])->name('schools.domain.verify');
        Route::post('/schools/{school}/payments', [PlatformAdminController::class, 'recordPayment'])->name('schools.payments');
        Route::post('/users', [PlatformAdminController::class, 'storeUser'])->name('users.store');
        Route::post('/students', [PlatformAdminController::class, 'storeStudent'])->name('students.store');
    });
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Student Management
    Route::middleware('role:school_admin,principal,vice_principal,academic_admin')->group(fn () => Route::resource('students', StudentController::class));
    Route::post('students/{student}/account', [StudentController::class, 'createAccount'])->middleware('role:school_admin,principal,vice_principal,academic_admin')->name('students.account');

    // Teacher Management
    Route::middleware('role:school_admin,principal,vice_principal')->group(function () {
        Route::resource('teachers', TeacherController::class);
        Route::post('teachers/{teacher}/reset-password', [TeacherController::class, 'resetPassword'])->name('teachers.reset-password');
    });
    Route::middleware('role:school_admin,principal,vice_principal,academic_admin')->group(function () {
        Route::get('parents', [ParentController::class, 'index'])->name('parents.index');
        Route::post('parents', [ParentController::class, 'store'])->name('parents.store');
        Route::put('parents/{parent}', [ParentController::class, 'update'])->name('parents.update');
        Route::get('academic-setup', [AcademicSetupController::class, 'index'])->name('academic-setup.index');
        Route::post('academic-setup/years', [AcademicSetupController::class, 'storeYear'])->name('academic-setup.years.store');
        Route::post('academic-setup/terms', [AcademicSetupController::class, 'storeTerm'])->name('academic-setup.terms.store');
        Route::get('academic-structure', [AcademicStructureController::class, 'index'])->name('academic-structure.index');
        Route::post('academic-structure/campuses', [AcademicStructureController::class, 'storeCampus'])->name('academic-structure.campuses.store');
        Route::post('academic-structure/faculties', [AcademicStructureController::class, 'storeFaculty'])->name('academic-structure.faculties.store');
        Route::post('academic-structure/departments', [AcademicStructureController::class, 'storeDepartment'])->name('academic-structure.departments.store');
        Route::post('academic-structure/courses', [AcademicStructureController::class, 'storeCourse'])->name('academic-structure.courses.store');
        Route::post('academic-structure/registrations', [AcademicStructureController::class, 'registerCourse'])->name('academic-structure.registrations.store');
    });
    Route::middleware('role:school_admin,principal,vice_principal,academic_admin,teacher,bursar')->group(function () {
        Route::get('communication/messages', [AdminMessageController::class, 'index'])->name('messages.index');
        Route::get('communication/messages/{conversation}', [AdminMessageController::class, 'show'])->name('messages.show');
        Route::post('communication/messages/{conversation}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
    });

    // Class Management
    Route::middleware('role:school_admin,principal,vice_principal,academic_admin,teacher')->group(function () {
        Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
        Route::post('/planning/homework', [PlanningController::class, 'storeHomework'])->name('planning.homework.store');
        Route::post('/planning/events', [PlanningController::class, 'storeEvent'])->name('planning.events.store');
        Route::resource('classes', ClassController::class);
        Route::get('classes/{class}/students', [ClassController::class, 'students'])->name('classes.students');
        Route::post('classes/{class}/assign-teacher', [ClassController::class, 'assignTeacher'])->name('classes.assign-teacher');

    // Subject Management
        Route::resource('subjects', SubjectController::class);

    // Result Management
    Route::get('results/bulk-upload/form', [ResultController::class, 'bulkUploadForm'])->name('results.bulk-upload.form');
    Route::post('results/bulk-upload', [ResultController::class, 'bulkUpload'])->name('results.bulk-upload');
    Route::get('results/export', [ResultController::class, 'export'])->name('results.export');
    Route::get('students/{student}/results', [ResultController::class, 'studentResults'])->name('students.results');
        Route::resource('results', ResultController::class);
    });

    // Payment Management. Parents may only read their own records; all
    // payment creation and offline settlement operations are administrator-only.
    Route::middleware('role:super_admin,school_admin,principal,bursar,parent')->group(function () {
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->whereNumber('payment')->name('payments.show');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->whereNumber('payment')->name('payments.receipt');
    });
    Route::middleware('role:school_admin,principal,bursar')->group(function () {
        Route::get('/campus-operations', [CampusOperationsController::class, 'index'])->name('campus.index');
        Route::post('/campus-operations/hostels', [CampusOperationsController::class, 'storeBuilding'])->name('campus.hostels.store');
        Route::post('/campus-operations/rooms', [CampusOperationsController::class, 'storeRoom'])->name('campus.rooms.store');
        Route::post('/campus-operations/allocations', [CampusOperationsController::class, 'allocateRoom'])->name('campus.allocations.store');
        Route::post('/campus-operations/library/items', [CampusOperationsController::class, 'storeLibraryItem'])->name('campus.library.items.store');
        Route::post('/campus-operations/library/loans', [CampusOperationsController::class, 'checkout'])->name('campus.library.loans.store');
        Route::post('/campus-operations/library/loans/{loan}/return', [CampusOperationsController::class, 'returnLoan'])->name('campus.library.loans.return');
        Route::post('/campus-operations/transport/routes', [CampusOperationsController::class, 'storeRoute'])->name('campus.transport.routes.store');
        Route::post('/campus-operations/transport/vehicles', [CampusOperationsController::class, 'storeVehicle'])->name('campus.transport.vehicles.store');
        Route::post('/campus-operations/transport/assignments', [CampusOperationsController::class, 'assignTransport'])->name('campus.transport.assignments.store');
        Route::post('/campus-operations/wallets/transactions', [CampusOperationsController::class, 'walletTransaction'])->name('campus.wallets.transactions.store');
        Route::post('/campus-operations/marketplace/vendors', [CampusOperationsController::class, 'storeVendor'])->name('campus.marketplace.vendors.store');
        Route::post('/campus-operations/marketplace/products', [CampusOperationsController::class, 'storeProduct'])->name('campus.marketplace.products.store');
        Route::post('/campus-operations/marketplace/orders', [CampusOperationsController::class, 'storeOrder'])->name('campus.marketplace.orders.store');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('payments/{payment}/process', [PaymentController::class, 'processPayment'])->whereNumber('payment')->name('payments.process');
    });

    Route::get('/reports', [ReportController::class, 'index'])->middleware('role:school_admin,principal,vice_principal,academic_admin,bursar')->name('reports.index');
    Route::middleware('role:school_admin,principal,vice_principal,academic_admin,teacher')->group(function () {
        Route::get('/ai', [AiCopilotController::class, 'index'])->middleware('feature:ai')->name('ai.index');
        Route::post('/ai', [AiCopilotController::class, 'store'])->middleware('feature:ai')->name('ai.store');
        Route::get('/ai/{generation}', [AiCopilotController::class, 'show'])->middleware('feature:ai')->name('ai.show');
        Route::get('/cbt', [CbtAdminController::class, 'index'])->middleware('feature:cbt')->name('cbt.index');
        Route::get('/cbt/create', [CbtAdminController::class, 'create'])->middleware('feature:cbt')->name('cbt.create');
        Route::post('/cbt/exams', [CbtAdminController::class, 'storeExam'])->middleware('feature:cbt')->name('cbt.exams.store');
        Route::post('/cbt/questions', [CbtAdminController::class, 'storeQuestion'])->middleware('feature:cbt')->name('cbt.questions.store');
        Route::get('/cbt/{exam}/edit', [CbtAdminController::class, 'edit'])->middleware('feature:cbt')->name('cbt.edit');
        Route::put('/cbt/{exam}', [CbtAdminController::class, 'updateExam'])->middleware('feature:cbt')->name('cbt.update');
        Route::get('/cbt/{exam}', [CbtAdminController::class, 'show'])->middleware('feature:cbt')->name('cbt.show');
        Route::post('/cbt/{exam}/publish', [CbtAdminController::class, 'publish'])->middleware('feature:cbt')->name('cbt.publish');
        Route::post('/cbt/{exam}/attempts/{attempt}/answers/{answer}/review', [CbtAdminController::class, 'reviewAnswer'])->middleware('feature:cbt')->name('cbt.answers.review');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
        Route::put('/assessments/{assessment}/scores', [AssessmentController::class, 'saveScores'])->name('assessments.scores');
        Route::post('/assessments/{assessment}/submit', [AssessmentController::class, 'submit'])->name('assessments.submit');
        Route::post('/assessments/{assessment}/approve', [AssessmentController::class, 'approve'])->name('assessments.approve');
        Route::post('/assessments/{assessment}/return', [AssessmentController::class, 'returnForCorrection'])->name('assessments.return');
    });
    Route::get('/transcripts/{transcript}', [TranscriptController::class, 'show'])->name('transcripts.show');
    Route::middleware('role:school_admin,principal,bursar')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceAdminController::class, 'index'])->name('index');
        Route::get('/invoices/create', [FinanceAdminController::class, 'createInvoice'])->name('invoices.create');
        Route::post('/invoices', [FinanceAdminController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [FinanceAdminController::class, 'showInvoice'])->name('invoices.show');
        Route::post('/expenses', [FinanceAdminController::class, 'storeExpense'])->name('expenses.store');
        Route::post('/expenses/{expense}/approve', [FinanceAdminController::class, 'approveExpense'])->name('expenses.approve');
        Route::post('/scholarships', [FinanceAdminController::class, 'storeScholarship'])->name('scholarships.store');
        Route::post('/scholarships/{scholarship}/assign', [FinanceAdminController::class, 'assignScholarship'])->name('scholarships.assign');
        Route::post('/payroll', [FinanceAdminController::class, 'storePayroll'])->name('payroll.store');
    });
    Route::middleware(['role:school_admin,principal,vice_principal', 'feature:analytics'])->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::post('/recalculate', [AnalyticsController::class, 'recalculate'])->name('recalculate');
        Route::post('/signals', [AnalyticsController::class, 'storeSignal'])->name('signals.store');
        Route::post('/interventions', [AnalyticsController::class, 'storeIntervention'])->name('interventions.store');
        Route::post('/interventions/{intervention}/complete', [AnalyticsController::class, 'completeIntervention'])->name('interventions.complete');
        Route::post('/staff-attendance', [AnalyticsController::class, 'storeStaffAttendance'])->name('staff-attendance.store');
    });

    // Settings Routes - ADD THESES LINES
    Route::middleware('role:school_admin,principal')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'updateSchool'])->name('settings.update');
        Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
        Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    });
});
