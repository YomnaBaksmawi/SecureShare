import { useEffect, useRef, useState } from 'react';

function Header({
    user,
    notifications,
    notificationsLoading,
    onMarkAsRead,
    onMarkAllAsRead,
    onAcceptRequest,
    onRejectRequest,
    actingRequestId,
    handledRequestIds,
}) {
    const [open, setOpen] = useState(false);

    const bellRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                bellRef.current &&
                !bellRef.current.contains(event.target)
            ) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const unreadCount =
        (notifications || []).filter(
            (notification) => !notification.read_at
        ).length;

    const formatTime = (date) => {
        if (!date) {
            return '';
        }

        return new Date(date).toLocaleString([], {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const showBell = notifications !== undefined;

    return (
        <header className="border-b border-slate-200 bg-white px-6 py-5 lg:px-10">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Dashboard
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage your documents securely.
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    {showBell && (
                        <div className="relative" ref={bellRef}>
                            <button
                                type="button"
                                onClick={() => setOpen((current) => !current)}
                                aria-label="Notifications"
                                className="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                    />
                                </svg>

                                {unreadCount > 0 && (
                                    <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-semibold text-white">
                                        {unreadCount > 9 ? '9+' : unreadCount}
                                    </span>
                                )}
                            </button>

                            {open && (
                                <div className="absolute right-0 top-full z-20 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                    <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                                        <div>
                                            <p className="text-sm font-semibold text-slate-900">
                                                Notifications
                                            </p>

                                            <p className="text-xs text-slate-500">
                                                {unreadCount} unread
                                            </p>
                                        </div>

                                        {unreadCount > 0 && (
                                            <button
                                                type="button"
                                                onClick={onMarkAllAsRead}
                                                className="rounded-lg px-2 py-1 text-xs font-medium text-blue-600 transition hover:bg-blue-50"
                                            >
                                                Mark all as read
                                            </button>
                                        )}
                                    </div>

                                    {notificationsLoading ? (
                                        <div className="px-5 py-10 text-center text-sm text-slate-500">
                                            Loading notifications...
                                        </div>
                                    ) : (notifications || []).length === 0 ? (
                                        <div className="px-5 py-10 text-center text-sm text-slate-500">
                                            You have no notifications yet.
                                        </div>
                                    ) : (
                                        <ul className="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                                            {notifications.map(
                                                (notification) => {
                                                    const data =
                                                        notification.data ||
                                                        {};
                                                    const isUnread =
                                                        !notification
                                                            .read_at;
                                                    const isRequest =
                                                        data.type ===
                                                            'friend_request' &&
                                                        !handledRequestIds?.has(
                                                            data.friendship_id
                                                        );

                                                    return (
                                                        <li
                                                            key={notification.id}
                                                            className={
                                                                isUnread
                                                                    ? 'bg-blue-50/50'
                                                                    : ''
                                                            }
                                                        >
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    onMarkAsRead?.(
                                                                        notification
                                                                    )
                                                                }
                                                                className="w-full px-5 py-4 text-left transition hover:bg-slate-50"
                                                            >
                                                                <div className="flex items-start gap-3">
                                                                    <span
                                                                        className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${
                                                                            isUnread
                                                                                ? 'bg-blue-500'
                                                                                : 'bg-transparent'
                                                                        }`}
                                                                    />

                                                                    <div className="min-w-0 flex-1">
                                                                        <p
                                                                            className={
                                                                                isUnread
                                                                                    ? 'text-sm font-medium text-slate-900'
                                                                                    : 'text-sm text-slate-700'
                                                                            }
                                                                        >
                                                                            {data.message ||
                                                                                'You have a new notification.'}
                                                                        </p>

                                                                        <p className="mt-1 text-xs text-slate-400">
                                                                            {formatTime(
                                                                                notification.created_at
                                                                            )}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </button>

                                                            {isRequest && (
                                                                <div className="flex items-center gap-2 px-5 pb-4">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            onAcceptRequest?.(
                                                                                data.friendship_id
                                                                            )
                                                                        }
                                                                        disabled={
                                                                            actingRequestId ===
                                                                            data.friendship_id
                                                                        }
                                                                        className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                                                    >
                                                                        {actingRequestId ===
                                                                        data.friendship_id
                                                                            ? 'Working...'
                                                                            : 'Accept'}
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            onRejectRequest?.(
                                                                                data.friendship_id
                                                                            )
                                                                        }
                                                                        disabled={
                                                                            actingRequestId ===
                                                                            data.friendship_id
                                                                        }
                                                                        className="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                                    >
                                                                        Reject
                                                                    </button>
                                                                </div>
                                                            )}
                                                        </li>
                                                    );
                                                }
                                            )}
                                        </ul>
                                    )}
                                </div>
                            )}
                        </div>
                    )}

                    <div className="hidden items-center gap-3 sm:flex">
                        <div className="text-right">
                            <p className="text-sm font-semibold text-slate-900">
                                {user?.name || 'User'}
                            </p>

                            <p className="text-xs text-slate-500">
                                {user?.email || ''}
                            </p>
                        </div>

                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-600">
                            {user?.name?.charAt(0).toUpperCase() || 'U'}
                        </div>
                    </div>
                </div>
            </div>
        </header>
    );
}

export default Header;