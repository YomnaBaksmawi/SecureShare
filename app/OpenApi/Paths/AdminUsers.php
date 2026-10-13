<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/admin/users',
    operationId: 'listAdminUsers',
    tags: ['Admin Users'],
    summary: 'List admin users',
    description: 'Paginated list of users with optional filtering by search, role and trashed state. Excludes soft-deleted users unless trashed is used.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'search',
            description: 'Search by name or email',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                maxLength: 255
            ),
            example: 'john'
        ),
        new OA\Parameter(
            name: 'role',
            description: 'Filter by user role',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['user', 'admin']
            ),
            example: 'admin'
        ),
        new OA\Parameter(
            name: 'trashed',
            description: 'Include or only show soft-deleted users',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['only', 'with']
            ),
            example: 'with'
        ),
        new OA\Parameter(
            name: 'per_page',
            description: 'Number of users per page',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                minimum: 1,
                maximum: 100
            ),
            example: 20
        ),
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
            description: 'Users retrieved successfully',
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
                        example: 'Users retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            allOf: [
                                new OA\Schema(
                                    type: 'object',
                                    required: [
                                        'id',
                                        'name',
                                        'email',
                                        'role',
                                        'status',
                                        'email_verified_at',
                                        'created_at',
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
                                            example: 'John'
                                        ),
                                        new OA\Property(
                                            property: 'email',
                                            type: 'string',
                                            format: 'email',
                                            example: 'john@example.com'
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
                                            property: 'email_verified_at',
                                            type: 'string',
                                            format: 'date-time',
                                            nullable: true,
                                            example: '2026-09-01T10:00:00Z'
                                        ),
                                        new OA\Property(
                                            property: 'created_at',
                                            type: 'string',
                                            format: 'date-time',
                                            example: '2026-09-01T10:00:00Z'
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
                        )
                    ),
                    new OA\Property(
                        property: 'meta',
                        type: 'object',
                        required: [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'current_page',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'last_page',
                                type: 'integer',
                                example: 5
                            ),
                            new OA\Property(
                                property: 'per_page',
                                type: 'integer',
                                example: 20
                            ),
                            new OA\Property(
                                property: 'total',
                                type: 'integer',
                                example: 100
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
            description: 'Forbidden - Admin access required'
        ),
    ]
)]

#[OA\Get(
    path: '/admin/users/options',
    operationId: 'listAdminUserOptions',
    tags: ['Admin Users'],
    summary: 'List admin user options',
    description: 'Lightweight list of all users (including soft-deleted users) for use as filter options.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User options retrieved successfully',
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
                        example: 'User options retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['id', 'name', 'email'],
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'John'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    format: 'email',
                                    example: 'john@example.com'
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
        new OA\Response(
            response: 403,
            description: 'Forbidden - Admin access required'
        ),
    ]
)]

#[OA\Patch(
    path: '/admin/users',
    operationId: 'updateAdminUser',
    tags: ['Admin Users'],
    summary: 'Update a user',
    description: 'Updates the role (and optionally the name) of the user identified by email.',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'role'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    description: 'Email of the user to update. Must exist.',
                    example: 'john@example.com'
                ),
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    maxLength: 255,
                    nullable: true,
                    description: 'Optional new name. Only applied when present.',
                    example: 'Updated Name'
                ),
                new OA\Property(
                    property: 'role',
                    type: 'string',
                    enum: ['user', 'admin'],
                    example: 'user'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'User role updated successfully',
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
                        example: 'User role updated successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'name',
                            'email',
                            'role',
                            'status',
                            'email_verified_at',
                            'created_at',
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
                                example: 'John'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email',
                                example: 'john@example.com'
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
                                property: 'email_verified_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'deleted_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
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
            description: 'Forbidden - Admin access required'
        ),
        new OA\Response(
            response: 404,
            description: 'User with the given email not found'
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
                        example: 'The email must be a valid email address.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'email' => [
                                'The email must be a valid email address.',
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

#[OA\Delete(
    path: '/admin/users/{user}',
    operationId: 'deleteAdminUser',
    tags: ['Admin Users'],
    summary: 'Soft-delete a user',
    description: 'Soft-deletes the given user.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'User ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User deleted successfully',
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
                        example: 'User deleted successfully.'
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
            description: 'Forbidden - Admin access required'
        ),
        new OA\Response(
            response: 404,
            description: 'User not found'
        ),
    ]
)]

#[OA\Post(
    path: '/admin/users/{user}/restore',
    operationId: 'restoreAdminUser',
    tags: ['Admin Users'],
    summary: 'Restore a soft-deleted user',
    description: 'Restores a soft-deleted user.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'User ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User restored successfully',
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
                        example: 'User restored successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'name',
                            'email',
                            'role',
                            'status',
                            'email_verified_at',
                            'created_at',
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
                                example: 'John'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email',
                                example: 'john@example.com'
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
                                property: 'email_verified_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'deleted_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
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
            description: 'Forbidden - Admin access required'
        ),
        new OA\Response(
            response: 404,
            description: 'User not found'
        ),
    ]
)]

#[OA\Patch(
    path: '/admin/users/{user}/status',
    operationId: 'updateAdminUserStatus',
    tags: ['Admin Users'],
    summary: 'Update a user status',
    description: 'Activates or deactivates the given user.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'User ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    enum: ['active', 'inactive'],
                    example: 'inactive'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'User status updated successfully',
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
                        example: 'User status updated successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'name',
                            'email',
                            'role',
                            'status',
                            'email_verified_at',
                            'created_at',
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
                                example: 'John'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email',
                                example: 'john@example.com'
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
                                property: 'email_verified_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'deleted_at',
                                type: 'string',
                                format: 'date-time',
                                nullable: true
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
            description: 'Forbidden - Admin access required'
        ),
        new OA\Response(
            response: 404,
            description: 'User not found'
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
                        example: 'The selected status is invalid.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'status' => [
                                'The selected status is invalid.',
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

class AdminUsers
{
}