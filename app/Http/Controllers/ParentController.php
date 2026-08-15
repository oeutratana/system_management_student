<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Guardian::with('student')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', Rule::in(['Father', 'Mother', 'Guardian'])],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:parents,email'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Guardian::create($data), 201);
    }

    public function show(Guardian $guardian): JsonResponse
    {
        return response()->json($guardian->load('student'));
    }

    public function update(Request $request, Guardian $guardian): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'relation' => ['sometimes', 'required', Rule::in(['Father', 'Mother', 'Guardian'])],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('parents', 'email')->ignore($guardian)],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        $guardian->update($data);

        return response()->json($guardian);
    }

    public function destroy(Guardian $guardian): JsonResponse
    {
        $guardian->delete();

        return response()->json(null, 204);
    }
}
