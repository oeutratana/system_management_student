<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Announcement::with('author')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'target_audience' => ['required', Rule::in(['All', 'Students', 'Teachers', 'Parents'])],
            'publish_date' => ['required', 'date'],
            'author_id' => ['nullable', 'exists:users,id'],
        ]);

        return response()->json(Announcement::create($data), 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json($announcement->load('author'));
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
            'target_audience' => ['sometimes', 'required', Rule::in(['All', 'Students', 'Teachers', 'Parents'])],
            'publish_date' => ['sometimes', 'required', 'date'],
            'author_id' => ['nullable', 'exists:users,id'],
        ]);

        $announcement->update($data);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}
