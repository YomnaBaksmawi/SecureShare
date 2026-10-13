<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/notifications',
    operationId: 'listNotifications',
    tags: ['Notifications'],
    summary: 'List notifications',
    description: 'Paginated list of the authenticated user\'s notifications, newest first, 20 per page.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'page',
            description: 'The page number to load',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                minimum: 1
            ),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Notifications retrieved successfully',
            content: new OA\JsonContent(
                required: ['success', 'message', 'data', 'meta'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Notifications retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['id', 'type', 'data', 'read_at', 'created_at'],
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    format: 'uuid',
                                    example: '33771a26-4e2a-4b24-b743-b8c9455c07a3'
                                ),
                                new OA\Property(
                                    property: 'type',
                                    type: 'string',
                                    example: 'App\\Notifications\\FriendRequestReceivedNotification'
                                ),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    description: 'The notification payload stored by the notification class (free-form).',
                                    additionalProperties: new OA\AdditionalProperties(),
                                    example: [
                                        'type' => 'friend_request',
                                        'friendship_id' => 1,
                                        'requester_id' => 2,
                                        'requester_name' => 'Jane Smith',
                                        'message' => 'Jane Smith sent you a friend request.',
                                    ]
                                ),
                                new OA\Property(
                                    property: 'read_at',
                                    type: 'string',
                                    format: 'date-time',
                                    nullable: true,
                                    example: null
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2026-09-01T10:00:00.000000Z'
                                ),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'meta',
                        type: 'object',
                        required: ['current_page', 'last_page', 'per_page', 'total'],
                        properties: [
                            new OA\Property(
                                property: 'current_page',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'last_page',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'per_page',
                                type: 'integer',
                                example: 20
                            ),
                            new OA\Property(
                                property: 'total',
                                type: 'integer',
                                example: 3
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
    ]
)]

#[OA\Patch(
    path: '/notifications/{notification}/read',
    operationId: 'markNotificationAsRead',
    tags: ['Notifications'],
    summary: 'Mark a notification as read',
    description: 'Marks one of the authenticated user\'s notifications as read.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'notification',
            description: 'Notification ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'string',
                format: 'uuid'
            ),
            example: '33771a26-4e2a-4b24-b743-b8c9455c07a3'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Notification marked as read',
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
                        example: 'Notification marked as read.'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 404,
            description: 'Notification not found for the authenticated user'
        ),
    ]
)]

#[OA\Patch(
    path: '/notifications/read-all',
    operationId: 'markAllNotificationsAsRead',
    tags: ['Notifications'],
    summary: 'Mark all notifications as read',
    description: 'Marks all of the authenticated user\'s unread notifications as read.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'All notifications marked as read',
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
                        example: 'All notifications marked as read.'
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

class Notifications
{
}