<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SchoolClass::with(['department', 'teacher', 'students'])->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'class_name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'string', 'max:255'],
        ]);

        return response()->json(SchoolClass::create($data), 201);
    }

    public function show(SchoolClass $class): JsonResponse
    {
        return response()->json($class->load(['department', 'teacher', 'students']));
    }

    public function update(Request $request, SchoolClass $class): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['sometimes', 'required', 'exists:departments,id'],
            'teacher_id' => ['sometimes', 'required', 'exists:users,id'],
            'class_name' => ['sometimes', 'required', 'string', 'max:255'],
            'academic_year' => ['sometimes', 'required', 'string', 'max:255'],
            'semester' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $class->update($data);

        return response()->json($class);
    }

    public function destroy(SchoolClass $class): JsonResponse
    {
        $class->delete();

        return response()->json(null, 204);
    }
}
