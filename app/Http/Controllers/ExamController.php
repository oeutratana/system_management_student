<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Exam::with('course')->latest()->get());
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

        return response()->json(Exam::create($data), 201);
    }

    public function show(Exam $exam): JsonResponse
    {
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

        $exam->update($data);

        return response()->json($exam);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();

        return response()->json(null, 204);
    }
}
