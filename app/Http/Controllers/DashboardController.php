<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Guardian;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => User::count(),
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'parents' => Guardian::count(),
            'departments' => Department::count(),
            'classes' => SchoolClass::count(),
            'courses' => Course::count(),
            'enrollments' => Enrollment::count(),
            'attendances' => Attendance::count(),
            'exams' => Exam::count(),
            'fees' => Fee::count(),
            'payments' => Payment::count(),
            'announcements' => Announcement::count(),
        ]);
    }
}
