<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/device-token',
    operationId: 'storeDeviceToken',
    tags: ['Push Notifications'],
    summary: 'Store a device token',
    description: 'Stores an FCM device token for the authenticated user so they can receive push notifications. Later tokens overwrite earlier ones.',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['token'],
            properties: [
                new OA\Property(
                    property: 'token',
                    type: 'string',
                    description: 'FCM device token.',
                    example: 'fcm-test-token-123'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Device token stored successfully',
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
                        example: 'Device token stored successfully.'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
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
                        example: 'The token field is required.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'token' => [
                                'The token field is required.',
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
    ]
)]

#[OA\Post(
    path: '/test-notification',
    operationId: 'sendTestNotification',
    tags: ['Push Notifications'],
    summary: 'Send a test push notification',
    description: 'Sends a test push notification to the given FCM device token.',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['token'],
            properties: [
                new OA\Property(
                    property: 'token',
                    type: 'string',
                    description: 'FCM device token to send the test notification to.',
                    example: 'fcm-test-token-123'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Test notification sent successfully',
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
                        example: 'Test notification sent successfully.'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
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
                        example: 'The token field is required.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'token' => [
                                'The token field is required.',
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
            response: 500,
            description: 'Failed to send test notification',
            content: new OA\JsonContent(
                required: ['success', 'message'],
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Failed to send test notification.'
                    ),
                ]
            )
        ),
    ]
)]

class Device
{
}