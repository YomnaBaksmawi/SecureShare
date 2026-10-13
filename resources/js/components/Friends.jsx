import { useEffect, useState } from 'react';
import api from '../api/client';

function Friends({ onShowRequests }) {
    const [friends, setFriends] = useState([]);
    const [people, setPeople] = useState([]);
    const [loadingFriends, setLoadingFriends] = useState(true);
    const [loadingPeople, setLoadingPeople] = useState(true);
    const [loadError, setLoadError] = useState('');
    const [actionError, setActionError] = useState('');
    const [sendingUserId, setSendingUserId] = useState(null);
    const [unfriendingId, setUnfriendingId] = useState(null);

    useEffect(() => {
        const loadFriendsData = async () => {
            try {
                const [friendsResponse, peopleResponse] =
                    await Promise.all([
                        api.get('/friends'),
                        api.get('/users'),
                    ]);

                setFriends(friendsResponse.data.data || []);
                setPeople(peopleResponse.data.data || []);
            } catch (error) {
                console.error(error);

                setLoadError(
                    error.response?.data?.message ||
                        'Unable to load friends.'
                );
            } finally {
                setLoadingFriends(false);
                setLoadingPeople(false);
            }
        };

        loadFriendsData();
    }, []);

    const handleAddFriend = async (user) => {
        if (sendingUserId) {
            return;
        }

        setSendingUserId(user.id);
        setActionError('');

        try {
            await api.post('/friend-requests', {
                user_id: user.id,
            });

            setPeople((currentPeople) =>
                currentPeople.map((person) =>
                    person.id === user.id
                        ? { ...person, relationship: 'pending' }
                        : person
                )
            );
        } catch (error) {
            console.error(error);

            setActionError(
                error.response?.data?.message ||
                    'Unable to send the friend request.'
            );
        } finally {
            setSendingUserId(null);
        }
    };

    const handleUnfriend = async (friend) => {
        if (unfriendingId) {
            return;
        }

        setUnfriendingId(friend.friendship_id);
        setActionError('');

        try {
            await api.delete(`/friends/${friend.friendship_id}`);

            setFriends((currentFriends) =>
                currentFriends.filter(
                    (currentFriend) =>
                        currentFriend.friendship_id !==
                        friend.friendship_id
                )
            );

            setPeople((currentPeople) => {
                const alreadyPresent = currentPeople.some(
                    (person) => person.id === friend.id
                );

                if (alreadyPresent) {
                    return currentPeople.map((person) =>
                        person.id === friend.id
                            ? {
                                  ...person,
                                  name: friend.name,
                                  email: friend.email,
                                  relationship: 'rejected',
                              }
                            : person
                    );
                }

                return [
                    {
                        id: friend.id,
                        name: friend.name,
                        email: friend.email,
                        relationship: 'rejected',
                    },
                    ...currentPeople,
                ];
            });
        } catch (error) {
            console.error(error);

            setActionError(
                error.response?.data?.message ||
                    'Unable to remove the friend.'
            );
        } finally {
            setUnfriendingId(null);
        }
    };

    const relationshipLabel = (relationship) => {
        switch (relationship) {
            case 'pending':
                return 'Pending';
            case 'accepted':
                return 'Already Friends';
            default:
                return 'Add Friend';
        }
    };

    return (
        <section>
            <div className="mb-8">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p className="text-sm font-medium text-slate-500">
                            Friends
                        </p>

                        <h1 className="mt-2 text-2xl font-bold text-slate-900">
                            My Friends
                        </h1>

                        <p className="mt-2 text-sm text-slate-500">
                            Your connections and people you can reach out to.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={onShowRequests}
                        className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Manage Friend Requests
                    </button>
                </div>
            </div>

            {loadError && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {loadError}
                </div>
            )}

            {actionError && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {actionError}
                </div>
            )}

            <div className="grid gap-6 xl:grid-cols-2">
                <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div className="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                        <div>
                            <h2 className="font-semibold text-slate-900">
                                My Friends
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                People you are connected with.
                            </p>
                        </div>

                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {friends.length}{' '}
                            {friends.length === 1 ? 'friend' : 'friends'}
                        </span>
                    </div>

                    {loadingFriends ? (
                        <div className="px-6 py-12 text-center">
                            <p className="text-sm text-slate-500">
                                Loading friends...
                            </p>
                        </div>
                    ) : friends.length === 0 ? (
                        <div className="px-6 py-12 text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">
                                👥
                            </div>

                            <p className="mt-4 text-sm font-medium text-slate-700">
                                You have no friends yet.
                            </p>

                            <p className="mt-1 text-sm text-slate-500">
                                People who accept your friend request will
                                show up here.
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-slate-100">
                            {friends.map((friend) => (
                                <div
                                    key={friend.friendship_id}
                                    className="flex items-center justify-between gap-5 px-6 py-5"
                                >
                                    <div className="flex min-w-0 items-center gap-4">
                                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                            {friend.name?.charAt(0)?.toUpperCase()}
                                        </div>

                                        <div className="min-w-0">
                                            <p className="truncate font-medium text-slate-900">
                                                {friend.name}
                                            </p>

                                            <p className="truncate text-sm text-slate-500">
                                                {friend.email}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => handleUnfriend(friend)}
                                        disabled={
                                            unfriendingId ===
                                            friend.friendship_id
                                        }
                                        className="shrink-0 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {unfriendingId ===
                                        friend.friendship_id
                                            ? 'Removing...'
                                            : 'Unfriend'}
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div className="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                        <div>
                            <h2 className="font-semibold text-slate-900">
                                People
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                Other users you can connect with.
                            </p>
                        </div>

                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {people.length}{' '}
                            {people.length === 1 ? 'person' : 'people'}
                        </span>
                    </div>

                    {loadingPeople ? (
                        <div className="px-6 py-12 text-center">
                            <p className="text-sm text-slate-500">
                                Loading people...
                            </p>
                        </div>
                    ) : people.length === 0 ? (
                        <div className="px-6 py-12 text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">
                                🌍
                            </div>

                            <p className="mt-4 text-sm font-medium text-slate-700">
                                No other users found.
                            </p>

                            <p className="mt-1 text-sm text-slate-500">
                                There are no other users available right now.
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-slate-100">
                            {people.map((person) => {
                                const isPending =
                                    person.relationship === 'pending';
                                const isAccepted =
                                    person.relationship === 'accepted';

                                return (
                                    <div
                                        key={person.id}
                                        className="flex items-center justify-between gap-5 px-6 py-5"
                                    >
                                        <div className="flex min-w-0 items-center gap-4">
                                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                                                {person.name?.charAt(0)?.toUpperCase()}
                                            </div>

                                            <div className="min-w-0">
                                                <p className="truncate font-medium text-slate-900">
                                                    {person.name}
                                                </p>

                                                <p className="truncate text-sm text-slate-500">
                                                    {person.email}
                                                </p>
                                            </div>
                                        </div>

                                        {isAccepted ? (
                                            <span className="shrink-0 rounded-full bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700">
                                                Already Friends
                                            </span>
                                        ) : isPending ? (
                                            <span className="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                                                Pending
                                            </span>
                                        ) : (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    handleAddFriend(person)
                                                }
                                                disabled={
                                                    sendingUserId ===
                                                    person.id
                                                }
                                                className="shrink-0 rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                {sendingUserId === person.id
                                                    ? 'Sending...'
                                                    : relationshipLabel(
                                                          person.relationship
                                                      )}
                                            </button>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </section>
            </div>
        </section>
    );
}

export default Friends;