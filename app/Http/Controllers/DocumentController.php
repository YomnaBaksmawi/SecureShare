<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\DocumentResource;
use App\Http\Requests\UpdateDocumentRequest;
class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate();

        return DocumentResource::collection($documents)
    ->additional([
        'success' => true,
        'message' => 'Documents retrieved successfully.',
    ]);
    }

    public function store(
        StoreDocumentRequest $request,
        AuditLogService $auditLogService
    ) {
        $file = $request->file('file');

        $path = $file->store('documents', 'private');

        $document = Document::create([
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'private',
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $auditLogService->log(
            'DOCUMENT_UPLOADED',
            $document,
            [
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'data' =>new DocumentResource($document),
        ], 201);
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return response()->json([
            'success' => true,
            'data' =>new DocumentResource($document),
        ]);
    }

    public function download(
        Document $document,
        AuditLogService $auditLogService
    ) {
        $this->authorize('download', $document);

        $auditLogService->log(
            'DOCUMENT_DOWNLOADED',
            $document
        );

        return Storage::disk($document->disk)
            ->download(
                $document->storage_path,
                $document->original_name
            );
    }

    public function destroy(
        Document $document,
        AuditLogService $auditLogService
    ) {
        $this->authorize('delete', $document);

        $document->delete();

        $auditLogService->log(
            'DOCUMENT_DELETED',
            $document
        );

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }
   
public function update(
    UpdateDocumentRequest $request,
    Document $document,
    AuditLogService $auditLogService
) {
    $this->authorize('update', $document);

    $oldPath = $document->storage_path;
    $oldName = $document->original_name;

    $file = $request->file('file');

    $path = $file->store('documents', 'private');

    try {
        $document->update([
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    } catch (\Throwable $e) {
        Storage::disk('private')->delete($path);

        throw $e;
    }

    Storage::disk($document->disk)->delete($oldPath);

    $auditLogService->log(
        'DOCUMENT_UPDATED',
        $document,
        [
            'old_original_name' => $oldName,
            'new_original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'size' => $document->size,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Document updated successfully.',
        'data' => new DocumentResource($document),
    ]);
}


public function sharedWithMe()
{
    $documents = Document::query()
        ->with([
            'owner:id,name,email',
            'shares' => function ($query) {
                $query
                    ->where('user_id', auth()->id())
                    ->whereNull('revoked_at')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            },
        ])
        ->whereHas('shares', function ($query) {
            $query
                ->where('user_id', auth()->id())
                ->whereNull('revoked_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });
        })
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Shared documents retrieved successfully.',
        'data' => $documents->map(function ($document) {
            $share = $document->shares->first();

            return [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,

                'shared_by' => [
                    'id' => $document->owner->id,
                    'name' => $document->owner->name,
                    'email' => $document->owner->email,
                ],

                'permission' => $share?->permission,
                'expires_at' => $share?->expires_at,
            ];
        }),
    ]);
}
}

