<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Grade::with('enrollment.student', 'enrollment.course')->latest()->paginate());
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

        return response()->json(Grade::create($data), 201);
    }

    public function show(Grade $grade): JsonResponse
    {
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

        $grade->update($data);

        return response()->json($grade);
    }

    public function destroy(Grade $grade): JsonResponse
    {
        $grade->delete();

        return response()->json(null, 204);
    }
}
