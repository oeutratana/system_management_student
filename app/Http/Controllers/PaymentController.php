<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Payment::with(['student', 'fee'])->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_id' => ['nullable', 'exists:fees,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['Cash', 'Card', 'Bank Transfer', 'Online'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Payment::create($data), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load(['student', 'fee']));
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'fee_id' => ['nullable', 'exists:fees,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_date' => ['sometimes', 'required', 'date'],
            'payment_method' => ['sometimes', 'required', Rule::in(['Cash', 'Card', 'Bank Transfer', 'Online'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->update($data);

        return response()->json($payment);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(null, 204);
    }
}
