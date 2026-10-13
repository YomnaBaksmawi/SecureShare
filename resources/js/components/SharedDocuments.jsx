
function SharedDocuments({
    documents,
    onDownload,
    onReplace,
    replacing,
    replaceFileInputRef,
    onReplaceFile,
}) {
    const formatFileSize = (bytes) => {
        if (bytes < 1024) {
            return `${bytes} B`;
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString();
    };

    return (
        <section className="mt-8">
            <div className="mb-4">
                <h2 className="text-xl font-semibold text-slate-900">
                    Shared with me
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Documents that other users have shared with you.
                </p>
            </div>

            <input
                ref={replaceFileInputRef}
                type="file"
                className="hidden"
                onChange={onReplaceFile}
            />

            {documents.length === 0 ? (
                <div className="rounded-xl border border-slate-200 bg-white p-8 text-center">
                    <p className="text-slate-500">
                        No documents have been shared with you yet.
                    </p>
                </div>
            ) : (
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    {documents.map((document) => (
                        <div
                            key={document.id}
                            className="border-b border-slate-100 p-5 last:border-b-0"
                        >
                            <div className="flex items-center justify-between gap-6">
                                <div className="flex min-w-0 items-center gap-4">
                                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xl">
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

                                    {document.permission === 'write' && (
                                        <button
                                            onClick={() => onReplace(document)}
                                            disabled={
                                                replacing === document.id
                                            }
                                            className="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {replacing === document.id
                                                ? 'Replacing...'
                                                : 'Replace'}
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
                                <div>
                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        Shared by
                                    </p>

                                    <p className="mt-1 text-sm font-medium text-slate-700">
                                        {document.shared_by?.name}
                                    </p>

                                    <p className="text-xs text-slate-500">
                                        {document.shared_by?.email}
                                    </p>
                                </div>

                                <div>
                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        Permission
                                    </p>

                                    <span
                                        className={`mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                            document.permission === 'write'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-slate-100 text-slate-600'
                                        }`}
                                    >
                                        {document.permission}
                                    </span>
                                </div>

                                <div>
                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        Expires
                                    </p>

                                    <p className="mt-1 text-sm text-slate-700">
                                        {document.expires_at
                                            ? formatDate(document.expires_at)
                                            : 'Never'}
                                    </p>

                                    <p className="text-xs text-slate-500">
                                        {formatFileSize(document.size)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </section>
    );
}

export default SharedDocuments;
