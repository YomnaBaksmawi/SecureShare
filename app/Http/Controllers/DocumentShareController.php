<?php

namespace App\Http\Controllers;

use App\Actions\DocumentShare\CreateDocumentShareAction;
use App\Http\Requests\CreateDocumentShareRequest;
use App\Http\Resources\DocumentShareResource;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Services\AuditLogService;
use Illuminate\Database\QueryException;

class DocumentShareController extends Controller
{
    public function index(Document $document)
    {
        $this->authorize('share', $document);

        $shares = $document
            ->shares()
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => DocumentShareResource::collection($shares),
        ]);
    }


    public function store(
        CreateDocumentShareRequest $request,
        Document $document,
        CreateDocumentShareAction $action,
        AuditLogService $auditLogService
    ) {
        $this->authorize('share', $document);

        try {
            $share = $action->execute(
                document: $document,
                userId: $request->integer('user_id'),
                permission: $request->string('permission')->toString(),
                expiresAt: $request->date('expires_at'),
            );

        } catch (\RuntimeException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'DUPLICATE_SHARE',
            ], 409);

        } catch (QueryException $e) {

            // حماية إضافية في حالة سباق insert مع unique constraint
            return response()->json([
                'success' => false,
                'message' => 'An active share already exists.',
                'error_code' => 'DUPLICATE_SHARE',
            ], 409);
        }


        $auditLogService->log(
            'DOCUMENT_SHARED',
            $share,
            [
                'document_id' => $document->id,
                'shared_with_user_id' => $share->user_id,
                'permission' => $share->permission,
                'expires_at' => $share->expires_at?->toISOString(),
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Document shared successfully.',
            'data' => new DocumentShareResource($share),
        ], 201);
    }


    public function destroy(
        Document $document,
        DocumentShare $share,
        AuditLogService $auditLogService
    ) {
        $this->authorize('share', $document);

        abort_unless(
            $share->document_id === $document->id,
            404
        );


        $share->update([
            'revoked_at' => now(),
        ]);


        $auditLogService->log(
            'DOCUMENT_SHARE_REVOKED',
            $share,
            [
                'document_id' => $document->id,
                'shared_with_user_id' => $share->user_id,
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Document share revoked successfully.',
        ]);
    }
}