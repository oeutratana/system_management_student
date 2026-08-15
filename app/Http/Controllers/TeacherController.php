<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Teacher::with(['department', 'user'])->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'teacher_code' => ['required', 'string', 'max:255', 'unique:teachers,teacher_code'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:teachers,email'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Teacher::create($data), 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load(['department', 'user']));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['sometimes', 'required', 'exists:departments,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'teacher_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('teachers', 'teacher_code')->ignore($teacher)],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'gender' => ['sometimes', 'required', Rule::in(['Male', 'Female'])],
            'dob' => ['sometimes', 'required', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('teachers', 'email')->ignore($teacher)],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        $teacher->update($data);

        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return response()->json(null, 204);
    }
}
