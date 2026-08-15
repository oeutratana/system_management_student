<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Fee::with(['student', 'payments'])->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['Unpaid', 'Paid', 'Overdue'])],
        ]);

        return response()->json(Fee::create($data), 201);
    }

    public function show(Fee $fee): JsonResponse
    {
        return response()->json($fee->load(['student', 'payments']));
    }

    public function update(Request $request, Fee $fee): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'fee_type' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['Unpaid', 'Paid', 'Overdue'])],
        ]);

        $fee->update($data);

        return response()->json($fee);
    }

    public function destroy(Fee $fee): JsonResponse
    {
        $fee->delete();

        return response()->json(null, 204);
    }
}
