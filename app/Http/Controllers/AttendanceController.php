<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Attendance::with('student')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'note' => ['nullable', 'string'],
        ]);

        return response()->json(Attendance::create($data), 201);
    }

    public function show(Attendance $attendance): JsonResponse
    {
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

        $attendance->update($data);

        return response()->json($attendance);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();

        return response()->json(null, 204);
    }
}
