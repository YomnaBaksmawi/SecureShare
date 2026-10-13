
function DocumentList({
    documents,
    loading,
    uploadError,
    uploading,
    fileInputRef,
    replaceFileInputRef,
    onUpload,
    onUploadClick,
    onDownload,
    onShare,
    onReplace,
    onReplaceFile,
    replacing,
}) {
    return (
        <section>
            <div className="mb-4 flex items-center justify-between">
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        My Documents
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage your private documents.
                    </p>
                </div>

                <button
                    onClick={onUploadClick}
                    disabled={uploading}
                    className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {uploading ? 'Uploading...' : 'Upload Document'}
                </button>
            </div>

            {uploadError && (
                <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {uploadError}
                </div>
            )}

            <input
                ref={fileInputRef}
                type="file"
                className="hidden"
                onChange={onUpload}
            />

            <input
                ref={replaceFileInputRef}
                type="file"
                className="hidden"
                onChange={onReplaceFile}
            />

            {loading ? (
                <div className="rounded-xl border border-slate-200 bg-white p-8 text-center">
                    <p className="text-slate-500">
                        Loading documents...
                    </p>
                </div>
            ) : documents.length === 0 ? (
                <div className="rounded-xl border border-slate-200 bg-white p-8 text-center">
                    <p className="text-slate-500">
                        No documents uploaded yet.
                    </p>
                </div>
            ) : (
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    {documents.map((document) => (
                        <div
                            key={document.id}
                            className="flex items-center justify-between gap-4 border-b border-slate-100 p-5 last:border-b-0"
                        >
                            <div className="flex min-w-0 items-center gap-4">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-lg">
                                    📄
                                </div>

                                <div className="min-w-0">
                                    <p className="truncate font-medium text-slate-900">
                                        {document.original_name}
                                    </p>

                                    <p className="mt-1 text-sm text-slate-500">
                                        {document.mime_type}
                                    </p>
                                </div>
                            </div>

                            <div className="flex shrink-0 items-center gap-2">
                                <button
                                    onClick={() => onDownload(document)}
                                    className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Download
                                </button>

                                <button
                                    onClick={() => onShare(document)}
                                    className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Share
                                </button>

                                <button
                                    onClick={() => onReplace(document)}
                                    disabled={replacing === document.id}
                                    className="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {replacing === document.id
                                        ? 'Replacing...'
                                        : 'Replace'}
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </section>
    );
}

export default DocumentList;

