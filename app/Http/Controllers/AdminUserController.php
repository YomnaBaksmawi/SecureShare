<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserIndexRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index(AdminUserIndexRequest $request)
    {
        $users = User::query()
            ->when(
                $request->search,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->role,
                fn ($query, $role) =>
                    $query->where('role', $role)
            )
            ->when(
                $request->trashed,
                function ($query, $trashed) {
                    if ($trashed === 'only') {
                        $query->onlyTrashed();
                    } elseif ($trashed === 'with') {
                        $query->withTrashed();
                    }
                }
            )
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => AdminUserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function options()
    {
        $users = User::withTrashed()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User options retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function update(UpdateAdminUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::where(
            'email',
            $validated['email']
        )->firstOrFail();

        $attributes = [
            'role' => $validated['role'],
        ];

        if (isset($validated['name'])) {
            $attributes['name'] = $validated['name'];
        }

        $user->update($attributes);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully.',
            'data' => new AdminUserResource($user->fresh()),
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function restore(int $user)
    {
        $userModel = User::withTrashed()->findOrFail($user);

        $userModel->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully.',
            'data' => new AdminUserResource($userModel->fresh()),
        ]);
    }

    public function status(
        UpdateUserStatusRequest $request,
        User $user
    ) {
        $user->update([
            'status' => $request->validated('status'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'data' => new AdminUserResource($user->fresh()),
        ]);
    }
}