<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Course::with(['department', 'enrollments'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'course_code' => ['required', 'string', 'max:255', 'unique:courses,course_code'],
            'course_name' => ['required', 'string', 'max:255'],
            'credit' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(Course::create($data), 201);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json($course->load(['department', 'enrollments.student']));
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['sometimes', 'required', 'exists:departments,id'],
            'course_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('courses', 'course_code')->ignore($course)],
            'course_name' => ['sometimes', 'required', 'string', 'max:255'],
            'credit' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $course->update($data);

        return response()->json($course);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(null, 204);
    }
}
