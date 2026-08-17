<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['sometimes', Rule::in(['admin', 'teacher', 'student'])],
        ]);

        $data['role'] ??= 'student';

        $user = User::create($data);

        return response()->json([
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $this->userPayload($user),
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isTeacher()) {
            $user->load('teacherProfile.department');
        }

        if ($user->isStudent()) {
            $user->load('studentProfile.class');
        }

        return response()->json($this->userPayload($user));
    }

    /**
     * A serializable representation of the authenticated user that includes
     * the role name and its permissions, but never the password.
     */
    private function userPayload(User $user): array
    {
        $payload = $user->toArray();

        $payload['permissions'] = $user->permissions()
            ->orderBy('permissions.name')
            ->pluck('name')
            ->values();

        return $payload;
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
