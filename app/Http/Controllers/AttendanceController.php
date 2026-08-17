<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with('student');

        if ($request->user()->isTeacher()) {
            $query->whereHas('student.class', function ($q) use ($request) {
                $q->where('teacher_id', $request->user()->id);
            });
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'note' => ['nullable', 'string'],
        ]);

        if ($request->user()->isTeacher() && ! $this->teacherOwnsStudent($request->user(), $data['student_id'])) {
            return response()->json([
                'message' => 'You can only mark attendance for students in your classes.',
            ], 403);
        }

        return response()->json(Attendance::create($data), 201);
    }

    public function show(Request $request, Attendance $attendance): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsStudent($request->user(), $attendance->student_id)) {
            return response()->json([
                'message' => 'You do not have permission to access this attendance record.',
            ], 403);
        }

        return response()->json($attendance->load('student'));
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'note' => ['nullable', 'string'],
        ]);

        $studentId = $data['student_id'] ?? $attendance->student_id;

        if ($request->user()->isTeacher() && ! $this->teacherOwnsStudent($request->user(), $studentId)) {
            return response()->json([
                'message' => 'You can only manage attendance for students in your classes.',
            ], 403);
        }

        $attendance->update($data);

        return response()->json($attendance);
    }

    public function destroy(Request $request, Attendance $attendance): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsStudent($request->user(), $attendance->student_id)) {
            return response()->json([
                'message' => 'You can only delete attendance for students in your classes.',
            ], 403);
        }

        $attendance->delete();

        return response()->json(null, 204);
    }

    private function teacherOwnsStudent(User $teacher, int $studentId): bool
    {
        return Student::whereKey($studentId)
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();
    }
}
