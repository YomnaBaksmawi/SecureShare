<?php
namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/documents/{document}/shares',
    operationId: 'listDocumentShares',
    tags: ['Document Shares'],
    summary: 'List document shares',
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
            description: 'Document shares retrieved successfully'
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
    path: '/documents/{document}/shares',
    operationId: 'createDocumentShare',
    tags: ['Document Shares'],
    summary: 'Share a document with another user',
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
        content: new OA\JsonContent(
            required: ['user_id', 'permission'],
            properties: [
                new OA\Property(
                    property: 'user_id',
                    type: 'integer',
                    example: 2
                ),
                new OA\Property(
                    property: 'permission',
                    type: 'string',
                    enum: ['read', 'write'],
                    example: 'read'
                ),
                new OA\Property(
                    property: 'expires_at',
                    type: 'string',
                    format: 'date-time',
                    nullable: true,
                    example: '2026-12-31T23:59:59Z'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Document shared successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Only the document owner can share it'
        ),
        new OA\Response(
            response: 404,
            description: 'Document not found'
        ),
        new OA\Response(
            response: 409,
            description: 'Active share already exists'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]

#[OA\Delete(
    path: '/documents/{document}/shares/{share}',
    operationId: 'revokeDocumentShare',
    tags: ['Document Shares'],
    summary: 'Revoke a document share',
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
        new OA\Parameter(
            name: 'share',
            description: 'Document share ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Document share revoked successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Only the document owner can revoke the share'
        ),
        new OA\Response(
            response: 404,
            description: 'Document or share not found'
        ),
    ]
)]

class DocumentShares
{
}

