<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Announcements - every role that has view_announcements can read;
    // creating/editing requires manage_announcements (admin only).
    Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware('permission:view_announcements');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->middleware('permission:view_announcements');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('permission:manage_announcements');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('permission:manage_announcements');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('permission:manage_announcements');

    // Student portal - students only, always scoped to their own data
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('class', [StudentPortalController::class, 'myClass']);
        Route::get('courses', [StudentPortalController::class, 'myCourses']);
        Route::get('exams', [StudentPortalController::class, 'myExams']);
        Route::get('grades', [StudentPortalController::class, 'myGrades'])->middleware('permission:view_grades');
        Route::get('attendance', [StudentPortalController::class, 'myAttendance'])->middleware('permission:view_attendance');
        Route::get('fees', [StudentPortalController::class, 'myFees'])->middleware('permission:view_fees');
        Route::get('payments', [StudentPortalController::class, 'myPayments'])->middleware('permission:view_payments');
        Route::get('announcements', [StudentPortalController::class, 'myAnnouncements'])->middleware('permission:view_announcements');
    });

    // Admin + Teacher
    Route::middleware('role:admin,teacher')->group(function () {

        // View students, classes and courses (teachers are scoped in controllers)
        Route::get('/students', [StudentController::class, 'index'])->middleware('permission:view_students');
        Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:view_students');
        Route::get('/classes', [SchoolClassController::class, 'index'])->middleware('permission:view_classes');
        Route::get('/classes/{class}', [SchoolClassController::class, 'show'])->middleware('permission:view_classes');
        Route::get('/courses', [CourseController::class, 'index'])->middleware('permission:view_courses');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->middleware('permission:view_courses');

        // Exams
        Route::get('/exams', [ExamController::class, 'index'])->middleware('permission:manage_exams');
        Route::post('/exams', [ExamController::class, 'store'])->middleware('permission:manage_exams');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->middleware('permission:manage_exams');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->middleware('permission:manage_exams');
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->middleware('permission:manage_exams');

        // Grades
        Route::get('/grades', [GradeController::class, 'index'])->middleware('permission:view_grades');
        Route::post('/grades', [GradeController::class, 'store'])->middleware('permission:manage_grades');
        Route::get('/grades/{grade}', [GradeController::class, 'show'])->middleware('permission:view_grades');
        Route::put('/grades/{grade}', [GradeController::class, 'update'])->middleware('permission:manage_grades');
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->middleware('permission:manage_grades');

        // Attendance
        Route::get('/attendances', [AttendanceController::class, 'index'])->middleware('permission:view_attendance');
        Route::post('/attendances', [AttendanceController::class, 'store'])->middleware('permission:manage_attendance');
        Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])->middleware('permission:view_attendance');
        Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->middleware('permission:manage_attendance');
        Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->middleware('permission:manage_attendance');

        // Reports (limited for teachers, full for admins)
        Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:view_reports');
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {

        // User management (registration)
        Route::post('/register', [AuthController::class, 'register'])->middleware('permission:manage_users');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Users
        Route::get('/users', [UserController::class, 'index'])->middleware('permission:manage_users');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:manage_users');
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:manage_users');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:manage_users');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:manage_users');

        // Students (manage)
        Route::post('/students', [StudentController::class, 'store'])->middleware('permission:manage_students');
        Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('permission:manage_students');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:manage_students');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->middleware('permission:manage_teachers');
        Route::post('/teachers', [TeacherController::class, 'store'])->middleware('permission:manage_teachers');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->middleware('permission:manage_teachers');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->middleware('permission:manage_teachers');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:manage_teachers');

        // Parents
        Route::get('/parents', [ParentController::class, 'index'])->middleware('permission:manage_parents');
        Route::post('/parents', [ParentController::class, 'store'])->middleware('permission:manage_parents');
        Route::get('/parents/{guardian}', [ParentController::class, 'show'])->middleware('permission:manage_parents');
        Route::put('/parents/{guardian}', [ParentController::class, 'update'])->middleware('permission:manage_parents');
        Route::delete('/parents/{guardian}', [ParentController::class, 'destroy'])->middleware('permission:manage_parents');

        // Departments - admin only; no permission entity exists for them
        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::get('/departments/{department}', [DepartmentController::class, 'show']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        // Classes (manage)
        Route::post('/classes', [SchoolClassController::class, 'store'])->middleware('permission:manage_classes');
        Route::put('/classes/{class}', [SchoolClassController::class, 'update'])->middleware('permission:manage_classes');
        Route::delete('/classes/{class}', [SchoolClassController::class, 'destroy'])->middleware('permission:manage_classes');

        // Courses (manage)
        Route::post('/courses', [CourseController::class, 'store'])->middleware('permission:manage_courses');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->middleware('permission:manage_courses');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->middleware('permission:manage_courses');

        // Enrollments - admin only; no permission entity exists for them
        Route::get('/enrollments', [EnrollmentController::class, 'index']);
        Route::post('/enrollments', [EnrollmentController::class, 'store']);
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);
        Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
        Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);

        // Fees
        Route::get('/fees', [FeeController::class, 'index'])->middleware('permission:manage_fees');
        Route::post('/fees', [FeeController::class, 'store'])->middleware('permission:manage_fees');
        Route::get('/fees/{fee}', [FeeController::class, 'show'])->middleware('permission:manage_fees');
        Route::put('/fees/{fee}', [FeeController::class, 'update'])->middleware('permission:manage_fees');
        Route::delete('/fees/{fee}', [FeeController::class, 'destroy'])->middleware('permission:manage_fees');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->middleware('permission:manage_payments');
        Route::post('/payments', [PaymentController::class, 'store'])->middleware('permission:manage_payments');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->middleware('permission:manage_payments');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->middleware('permission:manage_payments');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->middleware('permission:manage_payments');
    });

});
