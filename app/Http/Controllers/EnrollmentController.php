<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Enrollment::with(['student', 'course', 'grade'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'semester' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Active', 'Completed', 'Dropped'])],
        ]);

        return response()->json(Enrollment::create($data), 201);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        return response()->json($enrollment->load(['student', 'course', 'grade']));
    }

    public function update(Request $request, Enrollment $enrollment): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'semester' => ['sometimes', 'required', 'string', 'max:255'],
            'academic_year' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in(['Active', 'Completed', 'Dropped'])],
        ]);

        $enrollment->update($data);

        return response()->json($enrollment);
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $enrollment->delete();

        return response()->json(null, 204);
    }
}
