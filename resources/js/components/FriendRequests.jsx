import { useEffect, useState } from 'react';
import api from '../api/client';

function FriendRequests({
    refreshKey,
    actingId,
    onAccept,
    onReject,
    users,
    currentUserId,
}) {
    const [requests, setRequests] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState('');
    const [sendUserId, setSendUserId] = useState('');
    const [sending, setSending] = useState(false);
    const [sendError, setSendError] = useState('');
    const [sendSuccess, setSendSuccess] = useState('');

    useEffect(() => {
        const loadRequests = async () => {
            setLoading(true);
            setLoadError('');

            try {
                const response = await api.get('/friend-requests');

                setRequests(response.data.data || []);
            } catch (error) {
                console.error(error);

                setLoadError(
                    error.response?.data?.message ||
                        'Unable to load friend requests.'
                );
            } finally {
                setLoading(false);
            }
        };

        loadRequests();
    }, [refreshKey]);

    const candidates = users.filter(
        (candidate) => candidate.id !== currentUserId
    );

    const formatTime = (date) => {
        if (!date) {
            return '';
        }

        return new Date(date).toLocaleString([], {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const handleSend = async (event) => {
        event.preventDefault();

        if (!sendUserId || sending) {
            return;
        }

        setSending(true);
        setSendError('');
        setSendSuccess('');

        try {
            const response = await api.post('/friend-requests', {
                user_id: Number(sendUserId),
            });

            setSendSuccess(
                response.data.message || 'Friend request sent successfully.'
            );

            setSendUserId('');
        } catch (error) {
            console.error(error);

            setSendError(
                error.response?.data?.message ||
                    'Unable to send the friend request.'
            );
        } finally {
            setSending(false);
        }
    };

    return (
        <section>
            <div className="mb-8">
                <p className="text-sm font-medium text-slate-500">Friends</p>

                <h1 className="mt-2 text-2xl font-bold text-slate-900">
                    Friend Requests
                </h1>

                <p className="mt-2 text-sm text-slate-500">
                    Respond to incoming requests or invite other users.
                </p>
            </div>

            <div className="grid gap-6 xl:grid-cols-3">
                <section className="xl:col-span-2">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div className="border-b border-slate-200 px-6 py-5">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h2 className="font-semibold text-slate-900">
                                        Incoming requests
                                    </h2>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Users who want to connect with you.
                                    </p>
                                </div>

                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                    {requests.length}{' '}
                                    {requests.length === 1
                                        ? 'request'
                                        : 'requests'}
                                </span>
                            </div>
                        </div>

                        {loading ? (
                            <div className="px-6 py-12 text-center">
                                <p className="text-sm text-slate-500">
                                    Loading friend requests...
                                </p>
                            </div>
                        ) : loadError ? (
                            <div className="px-6 py-12 text-center">
                                <p className="text-sm text-red-600">
                                    {loadError}
                                </p>
                            </div>
                        ) : requests.length === 0 ? (
                            <div className="px-6 py-12 text-center">
                                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">
                                    🤝
                                </div>

                                <p className="mt-4 text-sm font-medium text-slate-700">
                                    No pending friend requests.
                                </p>

                                <p className="mt-1 text-sm text-slate-500">
                                    Send a friend request to another user on
                                    the right.
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-slate-100">
                                {requests.map((request) => {
                                    const requesterName =
                                        request.requester?.name ||
                                        `User ${request.requester_id}`;

                                    return (
                                        <div
                                            key={request.id}
                                            className="flex items-center justify-between gap-5 px-6 py-5"
                                        >
                                            <div className="flex min-w-0 items-center gap-4">
                                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                                    {requesterName
                                                        ?.charAt(0)
                                                        ?.toUpperCase()}
                                                </div>

                                                <div className="min-w-0">
                                                    <p className="truncate font-medium text-slate-900">
                                                        {requesterName}
                                                    </p>

                                                    {request.requester
                                                        ?.email && (
                                                        <p className="truncate text-sm text-slate-500">
                                                            {
                                                                request
                                                                    .requester
                                                                    .email
                                                            }
                                                        </p>
                                                    )}

                                                    <p className="mt-1 text-xs text-slate-400">
                                                        {formatTime(
                                                            request.created_at
                                                        )}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex shrink-0 items-center gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        onAccept(request.id)
                                                    }
                                                    disabled={
                                                        actingId === request.id
                                                    }
                                                    className="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    {actingId === request.id
                                                        ? 'Working...'
                                                        : 'Accept'}
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        onReject(request.id)
                                                    }
                                                    disabled={
                                                        actingId === request.id
                                                    }
                                                    className="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </section>

                <section>
                    <div className="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 className="font-semibold text-slate-900">
                            Send a friend request
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Invite another user to connect with you.
                        </p>

                        {sendError && (
                            <div className="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                {sendError}
                            </div>
                        )}

                        {sendSuccess && (
                            <div className="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                {sendSuccess}
                            </div>
                        )}

                        <form
                            onSubmit={handleSend}
                            className="mt-6 space-y-5"
                        >
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700">
                                    User
                                </label>

                                <select
                                    value={sendUserId}
                                    onChange={(event) =>
                                        setSendUserId(event.target.value)
                                    }
                                    required
                                    className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                >
                                    <option value="">
                                        Select a user
                                    </option>

                                    {candidates.map((candidate) => (
                                        <option
                                            key={candidate.id}
                                            value={candidate.id}
                                        >
                                            {candidate.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <button
                                type="submit"
                                disabled={
                                    sending || candidates.length === 0
                                }
                                className="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {sending
                                    ? 'Sending...'
                                    : 'Send Friend Request'}
                            </button>
                        </form>

                        {candidates.length === 0 && (
                            <p className="mt-4 text-center text-xs text-slate-400">
                                There are no other users available to invite.
                            </p>
                        )}
                    </div>
                </section>
            </div>
        </section>
    );
}

export default FriendRequests;