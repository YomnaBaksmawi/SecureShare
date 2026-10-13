<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/register',
    operationId: 'register',
    tags: ['Authentication'],
    summary: 'Register a new user',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    maxLength: 255,
                    example: 'Omar'
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    description: 'Must be unique among users.',
                    example: 'omar@example.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    minLength: 8,
                    description: 'Must be at least 8 characters and match password_confirmation.',
                    example: 'password123'
                ),
                new OA\Property(
                    property: 'password_confirmation',
                    type: 'string',
                    format: 'password',
                    description: 'Must match password.',
                    example: 'password123'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Registered successfully',
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
                        example: 'Registered successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: ['user'],
                        properties: [
                            new OA\Property(
                                property: 'user',
                                type: 'object',
                                required: [
                                    'id',
                                    'name',
                                    'email',
                                    'email_verified_at',
                                    'role',
                                    'status',
                                    'created_at',
                                    'updated_at',
                                    'deleted_at',
                                ],
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'integer',
                                        example: 1
                                    ),
                                    new OA\Property(
                                        property: 'name',
                                        type: 'string',
                                        example: 'Omar'
                                    ),
                                    new OA\Property(
                                        property: 'email',
                                        type: 'string',
                                        format: 'email',
                                        example: 'omar@example.com'
                                    ),
                                    new OA\Property(
                                        property: 'email_verified_at',
                                        type: 'string',
                                        format: 'date-time',
                                        nullable: true,
                                        example: null
                                    ),
                                    new OA\Property(
                                        property: 'role',
                                        type: 'string',
                                        enum: ['user', 'admin'],
                                        example: 'user'
                                    ),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['active', 'inactive'],
                                        example: 'active'
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
                                        property: 'deleted_at',
                                        type: 'string',
                                        format: 'date-time',
                                        nullable: true,
                                        example: null
                                    ),
                                ]
                            ),
                        ]
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
                        example: 'The password confirmation does not match.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'password' => [
                                'The password confirmation does not match.',
                            ],
                            'email' => [
                                'The email has already been taken.',
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
            description: 'Too many requests - throttled to 5 per minute'
        ),
    ]
)]

#[OA\Post(
    path: '/login',
    operationId: 'login',
    tags: ['Authentication'],
    summary: 'Login user',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'omar@example.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    example: 'password123'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login successful',
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
                        example: 'Login successful.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: ['user'],
                        properties: [
                            new OA\Property(
                                property: 'user',
                                type: 'object',
                                required: [
                                    'id',
                                    'name',
                                    'email',
                                    'email_verified_at',
                                    'role',
                                    'status',
                                    'created_at',
                                    'updated_at',
                                    'deleted_at',
                                ],
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'integer',
                                        example: 1
                                    ),
                                    new OA\Property(
                                        property: 'name',
                                        type: 'string',
                                        example: 'Omar'
                                    ),
                                    new OA\Property(
                                        property: 'email',
                                        type: 'string',
                                        format: 'email',
                                        example: 'omar@example.com'
                                    ),
                                    new OA\Property(
                                        property: 'email_verified_at',
                                        type: 'string',
                                        format: 'date-time',
                                        nullable: true,
                                        example: null
                                    ),
                                    new OA\Property(
                                        property: 'role',
                                        type: 'string',
                                        enum: ['user', 'admin'],
                                        example: 'user'
                                    ),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['active', 'inactive'],
                                        example: 'active'
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
                                        property: 'deleted_at',
                                        type: 'string',
                                        format: 'date-time',
                                        nullable: true,
                                        example: null
                                    ),
                                ]
                            ),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 422,
            description: 'Invalid credentials',
            content: new OA\JsonContent(
                required: ['message', 'errors'],
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Invalid credentials.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'email' => [
                                'Invalid credentials.',
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
            description: 'Too many requests - throttled to 5 per minute'
        ),
    ]
)]

#[OA\Post(
    path: '/logout',
    operationId: 'logout',
    tags: ['Authentication'],
    summary: 'Logout the authenticated user',
    description: 'Logs out the authenticated user. With a browser session this also invalidates the server-side session and regenerates the CSRF token. It is safe to call without a session, in which case it still logs out the authentication guard.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Logged out successfully',
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
                        example: 'Logged out successfully.'
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

#[OA\Get(
    path: '/user',
    operationId: 'getCurrentUser',
    tags: ['Authentication'],
    summary: 'Get the authenticated user',
    description: 'Returns the authenticated user model (password and remember_token are hidden).',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'The authenticated user',
            content: new OA\JsonContent(
                required: [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'role',
                    'status',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Omar'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'omar@example.com'
                    ),
                    new OA\Property(
                        property: 'email_verified_at',
                        type: 'string',
                        format: 'date-time',
                        nullable: true,
                        example: null
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['user', 'admin'],
                        example: 'user'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'active'
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
                        property: 'deleted_at',
                        type: 'string',
                        format: 'date-time',
                        nullable: true,
                        example: null
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

#[OA\Get(
    path: '/users',
    operationId: 'listUsers',
    tags: ['Authentication'],
    summary: 'List users',
    description: 'Lists active non-admin users excluding the authenticated user and soft-deleted users, with the friendship relationship to the authenticated user. Not paginated.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Users retrieved successfully',
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
                        example: 'Users retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['id', 'name', 'email', 'relationship'],
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 2
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'Ahmed'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    format: 'email',
                                    example: 'ahmed@example.com'
                                ),
                                new OA\Property(
                                    property: 'relationship',
                                    type: 'string',
                                    description: 'Friendship status between the listed user and the authenticated user, or none when no friendship exists.',
                                    enum: ['accepted', 'pending', 'rejected', 'none'],
                                    example: 'none'
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

#[OA\Get(
    path: '/users/shareable',
    operationId: 'getShareableUsers',
    tags: ['Authentication'],
    summary: 'Get users available for document sharing',
    description: 'Returns all non-admin users that can receive shared documents.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Shareable users retrieved successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Shareable users retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 2
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'Ahmed'
                                ),

                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

class Auth {}
