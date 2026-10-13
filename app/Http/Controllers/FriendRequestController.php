<?php

namespace App\Http\Controllers;

use App\Actions\Friendship\SendFriendRequestAction;
use App\Http\Requests\SendFriendRequestRequest;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use App\Models\Friendship;

class FriendRequestController extends Controller
{
    public function store( SendFriendRequestRequest $request,SendFriendRequestAction $action ): JsonResponse {
        try {
            $friendship = $action->execute(
                auth()->id(),
                $request->integer('user_id')
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'FRIEND_REQUEST_INVALID',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Friend request sent successfully.',
            'data' => [
                'id' => $friendship->id,
                'requester_id' => $friendship->requester_id,
                'addressee_id' => $friendship->addressee_id,
                'status' => $friendship->status,
                'created_at' => $friendship->created_at,
            ],
        ], 201);
    }
    public function index(): JsonResponse
    {
        $friendships = Friendship::query()
            ->where('addressee_id', auth()->id())
            ->where('status', 'pending')
            ->with('requester:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Friend requests retrieved successfully.',
            'data' => $friendships,
        ]);
    }

    public function indexFriends(): JsonResponse
    {
        $authId = auth()->id();

        $friendships = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($authId) {
                $query->where('requester_id', $authId)
                    ->orWhere('addressee_id', $authId);
            })
            ->with([
                'requester:id,name,email',
                'addressee:id,name,email',
            ])
            ->latest()
            ->get();

        $data = $friendships->map(function (Friendship $friendship) use ($authId) {
            $friend = $friendship->requester_id === $authId
                ? $friendship->addressee
                : $friendship->requester;

            return [
                'friendship_id' => $friendship->id,
                'id' => $friend?->id,
                'name' => $friend?->name,
                'email' => $friend?->email,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Friends retrieved successfully.',
            'data' => $data,
        ]);
    }

    public function unfriend(Friendship $friendship): JsonResponse
    {
        if (
            $friendship->requester_id !== auth()->id()
            && $friendship->addressee_id !== auth()->id()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot remove this friendship.',
                'error_code' => 'FORBIDDEN',
            ], 403);
        }

        if ($friendship->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'This friendship is no longer active.',
                'error_code' => 'INVALID_FRIEND_REQUEST',
            ], 409);
        }

        $friendship->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully.',
        ]);
    }

    public function accept(int $friendshipId): JsonResponse
{
    $friendship = Friendship::findOrFail($friendshipId);

    if ($friendship->addressee_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'You cannot accept this friend request.',
            'error_code' => 'FORBIDDEN',
        ], 403);
    }

    if ($friendship->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'This friend request is no longer pending.',
            'error_code' => 'INVALID_FRIEND_REQUEST',
        ], 409);
    }

    $friendship->update([
        'status' => 'accepted',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Friend request accepted successfully.',
        'data' => $friendship->fresh(),
    ]);
}
public function reject(int $friendshipId): JsonResponse
{
    $friendship = Friendship::findOrFail($friendshipId);

    if ($friendship->addressee_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'You cannot reject this friend request.',
            'error_code' => 'FORBIDDEN',
        ], 403);
    }

    if ($friendship->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'This friend request is no longer pending.',
            'error_code' => 'INVALID_FRIEND_REQUEST',
        ], 409);
    }

    $friendship->update([
        'status' => 'rejected',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Friend request rejected successfully.',
        'data' => $friendship->fresh(),
    ]);
}
}