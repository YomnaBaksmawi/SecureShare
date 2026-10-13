import { useEffect, useState } from 'react';
import api from '../api/client';
import { useAuth } from '../auth';

import Sidebar from '../components/Sidebar';
import Header from '../components/Header';

function DocumentSharing({
    document,
    users,
    onBack,
    onLogout,
    onSectionChange,
    activeSection,
}) {
    const [shares, setShares] = useState([]);
    const [loadingShares, setLoadingShares] = useState(true);

    const [shareUserId, setShareUserId] = useState('');
    const [permission, setPermission] = useState('read');
    const [expiresAt, setExpiresAt] = useState('');

    const [sharing, setSharing] = useState(false);
    const [shareError, setShareError] = useState('');
    const [shareSuccess, setShareSuccess] = useState('');

    const [revokingShareId, setRevokingShareId] = useState(null);
    const [revokeError, setRevokeError] = useState('');

    const { user } = useAuth();

    const shareableUsers = users.filter(
        (shareUser) => shareUser.id !== user?.id
    );

    useEffect(() => {
        const loadShares = async () => {
            setLoadingShares(true);
            setRevokeError('');

            try {
                const response = await api.get(
                    `/documents/${document.id}/shares`
                );

                setShares(response.data.data || []);
            } catch (error) {
                console.error(error);

                setRevokeError(
                    error.response?.data?.message ||
                        'Unable to load document shares.'
                );
            } finally {
                setLoadingShares(false);
            }
        };

        loadShares();
    }, [document.id]);

    const handleShare = async (event) => {
        event.preventDefault();

        setSharing(true);
        setShareError('');
        setShareSuccess('');

        try {
            const response = await api.post(
                `/documents/${document.id}/shares`,
                {
                    user_id: Number(shareUserId),
                    permission,
                    expires_at: expiresAt || null,
                }
            );

            setShares((currentShares) => [
                ...currentShares,
                response.data.data,
            ]);

            setShareSuccess('Document shared successfully.');

            setShareUserId('');
            setPermission('read');
            setExpiresAt('');
        } catch (error) {
            console.error(error);

            setShareError(
                error.response?.data?.message ||
                    'Unable to share this document.'
            );
        } finally {
            setSharing(false);
        }
    };

    const handleRevoke = async (share) => {
        const confirmed = window.confirm(
            `Revoke access for ${share.user?.name || 'this user'}?`
        );

        if (!confirmed) {
            return;
        }

        setRevokingShareId(share.id);
        setRevokeError('');

        try {
            await api.delete(
                `/documents/${document.id}/shares/${share.id}`
            );

            setShares((currentShares) =>
                currentShares.filter(
                    (currentShare) => currentShare.id !== share.id
                )
            );
        } catch (error) {
            console.error(error);

            setRevokeError(
                error.response?.data?.message ||
                    'Unable to revoke this share.'
            );
        } finally {
            setRevokingShareId(null);
        }
    };

    const formatDate = (date) => {
        if (!date) {
            return 'Never expires';
        }

        const expirationDate = new Date(date);

        if (expirationDate < new Date()) {
            return 'Expired';
        }

        return expirationDate.toLocaleString([], {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const isExpired = (date) => {
        return date && new Date(date) < new Date();
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <Sidebar
                onLogout={onLogout}
                onSectionChange={onSectionChange}
                activeSection={activeSection}
            />

            <main className="lg:ml-64">
                <Header user={user} />

                <div className="p-6 lg:p-10">
                    <button
                        onClick={onBack}
                        className="mb-6 text-sm font-medium text-slate-600 transition hover:text-slate-900"
                    >
                        ← Back to documents
                    </button>

                    <div className="mb-8">
                        <p className="text-sm font-medium text-slate-500">
                            Document Sharing
                        </p>

                        <h1 className="mt-2 text-2xl font-bold text-slate-900">
                            {document.original_name}
                        </h1>

                        <p className="mt-2 text-sm text-slate-500">
                            Manage who can access this document.
                        </p>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-3">
                        {/* People with access */}
                        <section className="xl:col-span-2">
                            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <div className="border-b border-slate-200 px-6 py-5">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="font-semibold text-slate-900">
                                                People with access
                                            </h2>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Users who currently have access
                                                to this document.
                                            </p>
                                        </div>

                                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            {shares.length}{' '}
                                            {shares.length === 1
                                                ? 'person'
                                                : 'people'}
                                        </span>
                                    </div>
                                </div>

                                {revokeError && (
                                    <div className="mx-6 mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {revokeError}
                                    </div>
                                )}

                                {loadingShares ? (
                                    <div className="px-6 py-12 text-center">
                                        <p className="text-sm text-slate-500">
                                            Loading shares...
                                        </p>
                                    </div>
                                ) : shares.length === 0 ? (
                                    <div className="px-6 py-12 text-center">
                                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">
                                            👤
                                        </div>

                                        <p className="mt-4 text-sm font-medium text-slate-700">
                                            No one has access yet.
                                        </p>

                                        <p className="mt-1 text-sm text-slate-500">
                                            Share this document with another
                                            user using the form.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="divide-y divide-slate-100">
                                        {shares.map((share) => (
                                            <div
                                                key={share.id}
                                                className="flex items-center justify-between gap-5 px-6 py-5"
                                            >
                                                <div className="flex min-w-0 items-center gap-4">
                                                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                                                        {share.user?.name
                                                            ?.charAt(0)
                                                            ?.toUpperCase()}
                                                    </div>

                                                    <div className="min-w-0">
                                                        <p className="truncate font-medium text-slate-900">
                                                            {share.user?.name}
                                                        </p>

                                                        <p className="truncate text-sm text-slate-500">
                                                            {share.user?.email}
                                                        </p>

                                                        <div className="mt-2 flex flex-wrap items-center gap-2">
                                                            <span
                                                                className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                                                                    share.permission ===
                                                                    'write'
                                                                        ? 'bg-blue-100 text-blue-700'
                                                                        : 'bg-slate-100 text-slate-600'
                                                                }`}
                                                            >
                                                                {share.permission ===
                                                                'write'
                                                                    ? 'Can edit'
                                                                    : 'Can view'}
                                                            </span>

                                                            <span
                                                                className={`text-xs ${
                                                                    isExpired(
                                                                        share.expires_at
                                                                    )
                                                                        ? 'font-medium text-red-600'
                                                                        : 'text-slate-400'
                                                                }`}
                                                            >
                                                                {isExpired(
                                                                    share.expires_at
                                                                )
                                                                    ? 'Expired'
                                                                    : `Expires: ${formatDate(
                                                                          share.expires_at
                                                                      )}`}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleRevoke(share)
                                                    }
                                                    disabled={
                                                        revokingShareId ===
                                                        share.id
                                                    }
                                                    className="shrink-0 rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    {revokingShareId ===
                                                    share.id
                                                        ? 'Revoking...'
                                                        : 'Revoke'}
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </section>

                        {/* Share form */}
                        <section>
                            <div className="rounded-2xl border border-slate-200 bg-white p-6">
                                <h2 className="font-semibold text-slate-900">
                                    Share document
                                </h2>

                                <p className="mt-1 text-sm text-slate-500">
                                    Give another user access to this document.
                                </p>

                                {shareError && (
                                    <div className="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {shareError}
                                    </div>
                                )}

                                {shareSuccess && (
                                    <div className="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                        {shareSuccess}
                                    </div>
                                )}

                                <form
                                    onSubmit={handleShare}
                                    className="mt-6 space-y-5"
                                >
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700">
                                            User
                                        </label>

                                        <select
                                            value={shareUserId}
                                            onChange={(event) =>
                                                setShareUserId(
                                                    event.target.value
                                                )
                                            }
                                            required
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                        >
                                            <option value="">
                                                Select a user
                                            </option>

                                            {shareableUsers.map(
                                                (shareUser) => (
                                                    <option
                                                        key={shareUser.id}
                                                        value={shareUser.id}
                                                    >
                                                        {shareUser.name}
                                                    </option>
                                                )
                                            )}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700">
                                            Permission
                                        </label>

                                        <select
                                            value={permission}
                                            onChange={(event) =>
                                                setPermission(
                                                    event.target.value
                                                )
                                            }
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                        >
                                            <option value="read">
                                                Read — Can view and download
                                            </option>

                                            <option value="write">
                                                Write — Can edit and download
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700">
                                            Expires at
                                        </label>

                                        <input
                                            type="datetime-local"
                                            value={expiresAt}
                                            onChange={(event) =>
                                                setExpiresAt(
                                                    event.target.value
                                                )
                                            }
                                            className="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                        />

                                        <p className="mt-1.5 text-xs text-slate-400">
                                            Leave empty for permanent access.
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={
                                            sharing ||
                                            shareableUsers.length === 0
                                        }
                                        className="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {sharing
                                            ? 'Sharing...'
                                            : 'Share document'}
                                    </button>
                                </form>

                                {shareableUsers.length === 0 && (
                                    <p className="mt-4 text-center text-xs text-slate-400">
                                        There are no other users available to
                                        share this document with.
                                    </p>
                                )}
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    );
}

export default DocumentSharing;