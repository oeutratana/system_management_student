<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Student::with(['class', 'enrollments.course', 'enrollments.grade'])->latest()->get());
    }

    public function indexOrStore(Request $request): JsonResponse
    {
        if ($request->has('student_code')) {
            return $this->store($request);
        }

        return $this->index();
    }

    public function store(Request $request): JsonResponse
    {
        $this->prepareDateOfBirth($request);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'student_code' => ['required', 'string', 'max:255', 'unique:students,student_code'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Student::create($data), 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load(['class', 'enrollments.course', 'enrollments.grade']));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $this->prepareDateOfBirth($request);

        $data = $request->validate([
            'class_id' => ['sometimes', 'required', 'exists:classes,id'],
            'student_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('students', 'student_code')->ignore($student)],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'gender' => ['sometimes', 'required', Rule::in(['Male', 'Female'])],
            'dob' => ['sometimes', 'required', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student)],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        $student->update($data);

        return response()->json($student);
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json(null, 204);
    }

    private function prepareDateOfBirth(Request $request): void
    {
        if (! $request->has('dob') && $request->has('date_of_birth')) {
            $request->merge([
                'dob' => $request->input('date_of_birth'),
            ]);
        }
    }
}
