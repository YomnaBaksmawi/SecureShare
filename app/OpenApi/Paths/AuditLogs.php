<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/admin/audit-logs',
    operationId: 'listAuditLogs',
    tags: ['Audit Logs'],
    summary: 'List audit logs',
    description: 'Retrieve audit logs with optional filtering and pagination.',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'action',
            description: 'Filter by action',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: [
                    'DOCUMENT_UPLOADED',
                    'DOCUMENT_DOWNLOADED',
                    'DOCUMENT_SHARED',
                    'DOCUMENT_UPDATED',
                    'DOCUMENT_SHARE_REVOKED',
                    'DOCUMENT_DELETED',
                ],
                example: 'DOCUMENT_SHARED'
            )
        ),
        new OA\Parameter(
            name: 'user_id',
            description: 'Filter by user ID',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 2
        ),
        new OA\Parameter(
            name: 'auditable_type',
            description: 'Filter by auditable model type',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            example: 'App\\Models\\Document'
        ),
        new OA\Parameter(
            name: 'auditable_id',
            description: 'Filter by auditable model ID',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
        new OA\Parameter(
            name: 'from',
            description: 'Filter logs from this date/time',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                format: 'date-time'
            ),
            example: '2026-09-01T00:00:00Z'
        ),
        new OA\Parameter(
            name: 'to',
            description: 'Filter logs until this date/time',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                format: 'date-time'
            ),
            example: '2026-09-22T23:59:59Z'
        ),
        new OA\Parameter(
            name: 'per_page',
            description: 'Number of logs per page',
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
            description: 'Audit logs retrieved successfully',
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
                        example: 'Audit logs retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: [
                                'id',
                                'action',
                                'metadata',
                                'created_at',
                                'user',
                                'auditable_type',
                                'auditable_id',
                            ],
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'action',
                                    type: 'string',
                                    example: 'DOCUMENT_SHARED'
                                ),
                                new OA\Property(
                                    property: 'metadata',
                                    type: 'object',
                                    nullable: true,
                                    example: []
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2026-09-27T12:00:00Z'
                                ),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    nullable: true,
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
                                ),
                                new OA\Property(
                                    property: 'auditable_type',
                                    type: 'string',
                                    nullable: true,
                                    example: 'App\\Models\\Document'
                                ),
                                new OA\Property(
                                    property: 'auditable_id',
                                    type: 'integer',
                                    nullable: true,
                                    example: 10
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
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(
                required: ['message', 'errors'],
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'The user id must be an integer.'
                    ),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'user_id' => [
                                'The user id must be an integer.',
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

class AuditLogs
{
}

