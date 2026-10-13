<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/documents',
    operationId: 'listDocuments',
    tags: ['Documents'],
    summary: 'List user documents',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Documents retrieved successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

#[OA\Post(
    path: '/documents',
    operationId: 'uploadDocument',
    tags: ['Documents'],
    summary: 'Upload a document',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['file'],
                properties: [
                    new OA\Property(
                        property: 'file',
                        type: 'string',
                        format: 'binary',
                        description: 'Document file. Maximum size: 10 MB.'
                    ),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Document uploaded successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]

#[OA\Get(
    path: '/documents/{document}',
    operationId: 'showDocument',
    tags: ['Documents'],
    summary: 'Get a document',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'document',
            description: 'Document ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Document retrieved successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden'
        ),
        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
    ]
)]

#[OA\Get(
    path: '/documents/{document}/download',
    operationId: 'downloadDocument',
    tags: ['Documents'],
    summary: 'Download a document',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'document',
            description: 'Document ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Document downloaded successfully',
            content: new OA\MediaType(
                mediaType: 'application/octet-stream'
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden'
        ),
        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
    ]
)]

#[OA\Delete(
    path: '/documents/{document}',
    operationId: 'deleteDocument',
    tags: ['Documents'],
    summary: 'Delete a document',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'document',
            description: 'Document ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Document deleted successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden'
        ),
        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
    ]
)]

#[OA\Post(
    path: '/documents/{document}',
    operationId: 'updateDocument',
    tags: ['Documents'],
    summary: 'Update document file',
    description: 'Replace document file. Uses Laravel method spoofing (_method=PUT). Available for owner or users with write permission.',
    security: [['sanctum' => []]],

    parameters: [
        new OA\Parameter(
            name: 'document',
            description: 'Document ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],

    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['_method', 'file'],
                properties: [

                    new OA\Property(
                        property: '_method',
                        type: 'string',
                        example: 'PUT',
                        description: 'Laravel HTTP method spoofing'
                    ),

                    new OA\Property(
                        property: 'file',
                        type: 'string',
                        format: 'binary',
                        description: 'New document file. Maximum size: 10 MB.'
                    ),
                ]
            )
        )
    ),

    responses: [

        new OA\Response(
            response: 200,
            description: 'Document updated successfully'
        ),

        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),

        new OA\Response(
            response: 403,
            description: 'Forbidden. User does not have permission.'
        ),

        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),

        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
    ]
)]

#[OA\Put(
    path: '/documents/{document}',
    operationId: 'putUpdateDocument',
    tags: ['Documents'],
    summary: 'Update document file (PUT)',
    description: 'Replace document file. Available for owner or users with write permission.',
    security: [['sanctum' => []]],

    parameters: [
        new OA\Parameter(
            name: 'document',
            description: 'Document ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],

    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['file'],
                properties: [
                    new OA\Property(
                        property: 'file',
                        type: 'string',
                        format: 'binary',
                        description: 'New document file. Maximum size: 10 MB.'
                    ),
                ]
            )
        )
    ),

    responses: [
        new OA\Response(
            response: 200,
            description: 'Document updated successfully',
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
                        example: 'Document updated successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        required: [
                            'id',
                            'name',
                            'original_name',
                            'mime_type',
                            'size',
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
                                property: 'name',
                                type: 'string',
                                nullable: true,
                                example: null
                            ),
                            new OA\Property(
                                property: 'original_name',
                                type: 'string',
                                example: 'report.pdf'
                            ),
                            new OA\Property(
                                property: 'mime_type',
                                type: 'string',
                                example: 'application/pdf'
                            ),
                            new OA\Property(
                                property: 'size',
                                type: 'integer',
                                example: 204800
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
                                example: '2026-09-02T10:00:00.000000Z'
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
            description: 'Forbidden. User does not have permission.'
        ),

        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),

        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
    ]
)]

#[OA\Get(
    path: '/documents/shared-with-me',
    operationId: 'listSharedDocuments',
    tags: ['Documents'],
    summary: 'List documents shared with the authenticated user',
    description: 'Returns active documents shared with the authenticated user. Revoked and expired shares are excluded.',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Shared documents retrieved successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

class Documents
{
}
