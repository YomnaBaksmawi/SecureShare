<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/friend-requests',
    operationId: 'sendFriendRequest',
    tags: ['Friend Requests'],
    summary: 'Send a friend request',
    description: 'Creates a pending friendship from the authenticated user to the given user. If a previously ended friendship already exists between the two users in either direction, it is re-activated as pending. If the target user is already a friend or a pending request already exists, a 409 is returned.',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user_id'],
            properties: [
                new OA\Property(
                    property: 'user_id',
                    type: 'integer',
                    description: 'ID of the user to send the friend request to. Must be a different user with an active account.',
                    example: 2
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Friend request sent successfully',
            content: new OA\JsonContent(
                required: ['success', 'message', 'data'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Friend request sent successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'requester_id',
                            'addressee_id',
                            'status',
                            'created_at',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'requester_id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'addressee_id',
                                type: 'integer',
                                example: 2
                            ),
                            new OA\Property(
                                property: 'status',
                                type: 'string',
                                enum: ['pending'],
                                example: 'pending'
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-01T10:00:00.000000Z'
                            ),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 409,
            description: 'Conflict - already friends or a pending request already exists',
            content: new OA\JsonContent(
                required: ['success', 'message', 'error_code'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'You are already friends with this user.'
                    ),
                    new OA\Property(
                        property: 'error_code',
                        type: 'string',
                        enum: ['FRIEND_REQUEST_INVALID'],
                        example: 'FRIEND_REQUEST_INVALID'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(
                required: ['message', 'errors'],
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'You cannot send a friend request to this user.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'user_id' => [
                                'You cannot send a friend request to this user.',
                            ],
                        ],
                        additionalProperties: new OA\AdditionalProperties(
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        )
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 429,
            description: 'Too many requests - throttled to 10 per minute'
        ),
    ]
)]

#[OA\Get(
    path: '/friend-requests',
    operationId: 'listFriendRequests',
    tags: ['Friend Requests'],
    summary: 'List received friend requests',
    description: 'Lists pending friend requests received by the authenticated user.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Friend requests retrieved successfully',
            content: new OA\JsonContent(
                required: ['success', 'message', 'data'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Friend requests retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: [
                                'id',
                                'requester_id',
                                'addressee_id',
                                'status',
                                'created_at',
                                'updated_at',
                                'requester',
                            ],
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'requester_id',
                                    type: 'integer',
                                    example: 2
                                ),
                                new OA\Property(
                                    property: 'addressee_id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'status',
                                    type: 'string',
                                    enum: ['pending'],
                                    example: 'pending'
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2026-09-01T10:00:00.000000Z'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2026-09-01T10:00:00.000000Z'
                                ),
                                new OA\Property(
                                    property: 'requester',
                                    type: 'object',
                                    required: ['id', 'name', 'email'],
                                    properties: [
                                        new OA\Property(
                                            property: 'id',
                                            type: 'integer',
                                            example: 2
                                        ),
                                        new OA\Property(
                                            property: 'name',
                                            type: 'string',
                                            example: 'Jane Smith'
                                        ),
                                        new OA\Property(
                                            property: 'email',
                                            type: 'string',
                                            format: 'email',
                                            example: 'jane@example.com'
                                        ),
                                    ]
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

#[OA\Post(
    path: '/friend-requests/{friendship}/accept',
    operationId: 'acceptFriendRequest',
    tags: ['Friend Requests'],
    summary: 'Accept a friend request',
    description: 'Accepts a pending friend request. Only the addressee of the request may accept it.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'friendship',
            description: 'Friendship (friend request) ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Friend request accepted successfully',
            content: new OA\JsonContent(
                required: ['success', 'message', 'data'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Friend request accepted successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'requester_id',
                            'addressee_id',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'requester_id',
                                type: 'integer',
                                example: 2
                            ),
                            new OA\Property(
                                property: 'addressee_id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'status',
                                type: 'string',
                                enum: ['accepted'],
                                example: 'accepted'
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-01T10:00:00.000000Z'
                            ),
                            new OA\Property(
                                property: 'updated_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-01T10:00:00.000000Z'
                            ),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden - only the addressee can accept the request',
            content: new OA\JsonContent(
                required: ['success', 'message', 'error_code'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'You cannot accept this friend request.'
                    ),
                    new OA\Property(
                        property: 'error_code',
                        type: 'string',
                        enum: ['FORBIDDEN'],
                        example: 'FORBIDDEN'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 404,
            description: 'Friendship not found'
        ),
        new OA\Response(
            response: 409,
            description: 'Conflict - the friend request is no longer pending',
            content: new OA\JsonContent(
                required: ['success', 'message', 'error_code'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'This friend request is no longer pending.'
                    ),
                    new OA\Property(
                        property: 'error_code',
                        type: 'string',
                        enum: ['INVALID_FRIEND_REQUEST'],
                        example: 'INVALID_FRIEND_REQUEST'
                    ),
                ]
            )
        ),
    ]
)]

#[OA\Post(
    path: '/friend-requests/{friendship}/reject',
    operationId: 'rejectFriendRequest',
    tags: ['Friend Requests'],
    summary: 'Reject a friend request',
    description: 'Rejects a pending friend request. Only the addressee of the request may reject it.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'friendship',
            description: 'Friendship (friend request) ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Friend request rejected successfully',
            content: new OA\JsonContent(
                required: ['success', 'message', 'data'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Friend request rejected successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'requester_id',
                            'addressee_id',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'requester_id',
                                type: 'integer',
                                example: 2
                            ),
                            new OA\Property(
                                property: 'addressee_id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'status',
                                type: 'string',
                                enum: ['rejected'],
                                example: 'rejected'
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-01T10:00:00.000000Z'
                            ),
                            new OA\Property(
                                property: 'updated_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-01T10:00:00.000000Z'
                            ),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden - only the addressee can reject the request',
            content: new OA\JsonContent(
                required: ['success', 'message', 'error_code'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'You cannot reject this friend request.'
                    ),
                    new OA\Property(
                        property: 'error_code',
                        type: 'string',
                        enum: ['FORBIDDEN'],
                        example: 'FORBIDDEN'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 404,
            description: 'Friendship not found'
        ),
        new OA\Response(
            response: 409,
            description: 'Conflict - the friend request is no longer pending',
            content: new OA\JsonContent(
                required: ['success', 'message', 'error_code'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'This friend request is no longer pending.'
                    ),
                    new OA\Property(
                        property: 'error_code',
                        type: 'string',
                        enum: ['INVALID_FRIEND_REQUEST'],
                        example: 'INVALID_FRIEND_REQUEST'
                    ),
                ]
            )
        ),
    ]
)]

class FriendRequests
{
}