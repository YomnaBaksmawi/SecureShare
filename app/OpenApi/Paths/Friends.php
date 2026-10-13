<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/friends',
    operationId: 'listFriends',
    tags: ['Friends'],
    summary: 'List friends',
    description: 'Lists accepted friendships involving the authenticated user. A friendship accepted in either direction (the authenticated user as requester or addressee) is returned, with the other side reported as the friend. The friend id, name and email may be null if the related user is unavailable.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Friends retrieved successfully',
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
                        example: 'Friends retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: [
                                'friendship_id',
                                'id',
                                'name',
                                'email',
                            ],
                            properties: [
                                new OA\Property(
                                    property: 'friendship_id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    nullable: true,
                                    example: 2
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    nullable: true,
                                    example: 'Jane Smith'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    format: 'email',
                                    nullable: true,
                                    example: 'jane@example.com'
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

#[OA\Delete(
    path: '/friends/{friendship}',
    operationId: 'unfriend',
    tags: ['Friends'],
    summary: 'Remove a friend',
    description: 'Ends an accepted friendship. Only the requester or the addressee of the friendship may remove it. The friendship is marked as rejected.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'friendship',
            description: 'Friendship ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Friend removed successfully',
            content: new OA\JsonContent(
                required: ['success', 'message'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Friend removed successfully.'
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
            description: 'Forbidden - only the requester or addressee can remove the friendship',
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
                        example: 'You cannot remove this friendship.'
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
            description: 'Conflict - the friendship is no longer active',
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
                        example: 'This friendship is no longer active.'
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

class Friends
{
}