<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function myClass(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json($student->load('class.department', 'class.teacher'));
    }

    public function myCourses(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json($student->enrollments()->with('course')->latest()->get());
    }

    public function myExams(Request $request): JsonResponse
    {
        $student = $this->student($request);

        $courseIds = $student->enrollments()->pluck('course_id');

        return response()->json(Exam::with('course')->whereIn('course_id', $courseIds)->latest()->get());
    }

    public function myGrades(Request $request): JsonResponse
    {
        $student = $this->student($request);

        $enrollmentIds = $student->enrollments()->pluck('id');

        return response()->json(Grade::with('enrollment.course')->whereIn('enrollment_id', $enrollmentIds)->latest()->get());
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json($student->attendances()->latest()->get());
    }

    public function myFees(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json($student->fees()->latest()->get());
    }

    public function myPayments(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json($student->payments()->latest()->get());
    }

    public function myAnnouncements(Request $request): JsonResponse
    {
        return response()->json(Announcement::with('author')
            ->whereIn('target_audience', ['All', 'Students'])
            ->latest()
            ->get());
    }

    private function student(Request $request): Student
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        abort_if(! $student, 404, 'No student profile is linked to this account.');

        return $student;
    }
}
