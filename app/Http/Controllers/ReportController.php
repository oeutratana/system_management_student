<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $request->user()->isAdmin()
            ? $this->adminReport()
            : $this->teacherReport($request->user());
    }

    private function adminReport(): JsonResponse
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
            'exams' => Exam::count(),
            'announcements' => Announcement::count(),
            'attendance' => [
                'total' => Attendance::count(),
                'present' => Attendance::where('status', 'Present')->count(),
                'absent' => Attendance::where('status', 'Absent')->count(),
                'late' => Attendance::where('status', 'Late')->count(),
                'excused' => Attendance::where('status', 'Excused')->count(),
            ],
            'fees' => [
                'total_amount' => (float) Fee::sum('amount'),
                'paid' => Fee::where('status', 'Paid')->count(),
                'unpaid' => Fee::where('status', 'Unpaid')->count(),
                'overdue' => Fee::where('status', 'Overdue')->count(),
            ],
            'payments_total' => (float) Payment::sum('amount'),
            'average_grade' => (float) Grade::avg('total'),
        ]);
    }

    private function teacherReport(User $teacher): JsonResponse
    {
        $teacherProfile = $teacher->teacherProfile;
        $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');

        return response()->json([
            'classes' => $classIds->count(),
            'students' => $studentIds->count(),
            'courses' => Course::where('department_id', $teacherProfile?->department_id)->count(),
            'exams' => Exam::whereHas('course', function ($q) use ($teacherProfile) {
                $q->where('department_id', $teacherProfile?->department_id);
            })->count(),
            'attendance' => [
                'total' => Attendance::whereIn('student_id', $studentIds)->count(),
                'present' => Attendance::whereIn('student_id', $studentIds)->where('status', 'Present')->count(),
                'absent' => Attendance::whereIn('student_id', $studentIds)->where('status', 'Absent')->count(),
            ],
            'fees_collected' => (float) Payment::whereIn('student_id', $studentIds)->sum('amount'),
            'average_grade' => (float) Grade::whereHas('enrollment', function ($q) use ($studentIds) {
                $q->whereIn('student_id', $studentIds);
            })->avg('total'),
        ]);
    }
}
