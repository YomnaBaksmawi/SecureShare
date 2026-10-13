<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function shareable(): JsonResponse
    {
        $users = User::query()
            ->where('role', '!=', 'admin')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('receivedFriendRequests', function ($query) {
                    $query->where('requester_id', auth()->id())
                        ->where('status', 'accepted');
                })->orWhereHas('sentFriendRequests', function ($query) {
                    $query->where('addressee_id', auth()->id())
                        ->where('status', 'accepted');
                });
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Shareable users retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function index(): JsonResponse
    {
        $authId = auth()->id();

        $statuses = Friendship::query()
            ->where('requester_id', $authId)
            ->orWhere('addressee_id', $authId)
            ->get()
            ->mapWithKeys(function (Friendship $friendship) use ($authId) {
                $otherId = $friendship->requester_id === $authId
                    ? $friendship->addressee_id
                    : $friendship->requester_id;

                return [$otherId => $friendship->status];
            });

        $users = User::query()
            ->where('role', '!=', 'admin')
            ->where('status', 'active')
            ->where('id', '!=', $authId)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($statuses) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'relationship' => $statuses[$user->id] ?? 'none',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => $users,
        ]);
    }
}
