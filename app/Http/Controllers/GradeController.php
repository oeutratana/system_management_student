<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Grade::with('enrollment.student', 'enrollment.course');

        if ($request->user()->isTeacher()) {
            $query->whereHas('enrollment.student', function ($q) use ($request) {
                $q->whereHas('class', function ($c) use ($request) {
                    $c->where('teacher_id', $request->user()->id);
                });
            });
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'assignment' => ['required', 'numeric', 'between:0,999.99'],
            'midterm' => ['required', 'numeric', 'between:0,999.99'],
            'final' => ['required', 'numeric', 'between:0,999.99'],
            'total' => ['required', 'numeric', 'between:0,999.99'],
            'grade' => ['required', 'string', 'max:255'],
        ]);

        if ($request->user()->isTeacher() && ! $this->teacherOwnsEnrollment($request->user(), $data['enrollment_id'])) {
            return response()->json([
                'message' => 'You can only enter grades for students in your classes.',
            ], 403);
        }

        return response()->json(Grade::create($data), 201);
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsEnrollment($request->user(), $grade->enrollment_id)) {
            return response()->json([
                'message' => 'You do not have permission to access this grade.',
            ], 403);
        }

        return response()->json($grade->load('enrollment.student', 'enrollment.course'));
    }

    public function update(Request $request, Grade $grade): JsonResponse
    {
        $data = $request->validate([
            'enrollment_id' => ['sometimes', 'required', 'exists:enrollments,id'],
            'assignment' => ['sometimes', 'required', 'numeric', 'between:0,999.99'],
            'midterm' => ['sometimes', 'required', 'numeric', 'between:0,999.99'],
            'final' => ['sometimes', 'required', 'numeric', 'between:0,999.99'],
            'total' => ['sometimes', 'required', 'numeric', 'between:0,999.99'],
            'grade' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $enrollmentId = $data['enrollment_id'] ?? $grade->enrollment_id;

        if ($request->user()->isTeacher() && ! $this->teacherOwnsEnrollment($request->user(), $enrollmentId)) {
            return response()->json([
                'message' => 'You can only manage grades for students in your classes.',
            ], 403);
        }

        $grade->update($data);

        return response()->json($grade);
    }

    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsEnrollment($request->user(), $grade->enrollment_id)) {
            return response()->json([
                'message' => 'You can only delete grades for students in your classes.',
            ], 403);
        }

        $grade->delete();

        return response()->json(null, 204);
    }

    private function teacherOwnsEnrollment(User $teacher, int $enrollmentId): bool
    {
        return Enrollment::whereKey($enrollmentId)
            ->whereHas('student.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();
    }
}
