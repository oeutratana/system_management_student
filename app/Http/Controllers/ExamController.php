<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with('course');

        if ($request->user()->isTeacher()) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('department_id', $this->teacherDepartmentId($request->user()));
            });
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'exam_name' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'total_marks' => ['required', 'numeric', 'between:0,999.99'],
            'weight' => ['nullable', 'numeric', 'between:0,999.99'],
        ]);

        if ($request->user()->isTeacher() && ! $this->teacherOwnsCourse($request->user(), $data['course_id'])) {
            return response()->json([
                'message' => 'You can only create exams for courses in your department.',
            ], 403);
        }

        return response()->json(Exam::create($data), 201);
    }

    public function show(Request $request, Exam $exam): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsCourse($request->user(), $exam->course_id)) {
            return response()->json([
                'message' => 'You do not have permission to access this exam.',
            ], 403);
        }

        return response()->json($exam->load('course'));
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'exam_name' => ['sometimes', 'required', 'string', 'max:255'],
            'exam_date' => ['sometimes', 'required', 'date'],
            'total_marks' => ['sometimes', 'required', 'numeric', 'between:0,999.99'],
            'weight' => ['nullable', 'numeric', 'between:0,999.99'],
        ]);

        $courseId = $data['course_id'] ?? $exam->course_id;

        if ($request->user()->isTeacher() && ! $this->teacherOwnsCourse($request->user(), $courseId)) {
            return response()->json([
                'message' => 'You can only manage exams for courses in your department.',
            ], 403);
        }

        $exam->update($data);

        return response()->json($exam);
    }

    public function destroy(Request $request, Exam $exam): JsonResponse
    {
        if ($request->user()->isTeacher() && ! $this->teacherOwnsCourse($request->user(), $exam->course_id)) {
            return response()->json([
                'message' => 'You can only delete exams for courses in your department.',
            ], 403);
        }

        $exam->delete();

        return response()->json(null, 204);
    }

    private function teacherDepartmentId(User $teacher): ?int
    {
        return $teacher->teacherProfile?->department_id;
    }

    private function teacherOwnsCourse(User $teacher, int $courseId): bool
    {
        $departmentId = $this->teacherDepartmentId($teacher);

        return $departmentId !== null && Course::whereKey($courseId)->where('department_id', $departmentId)->exists();
    }
}
