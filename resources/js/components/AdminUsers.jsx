import { useEffect, useState } from 'react';
import api from '../api/client';

function AdminUsers() {
    const [users, setUsers] = useState([]);
    const [pagination, setPagination] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const [search, setSearch] = useState('');
    const [role, setRole] = useState('');
    const [view, setView] = useState('active');

    const [editingUser, setEditingUser] = useState(null);
    const [editName, setEditName] = useState('');
    const [editRole, setEditRole] = useState('');
    const [saving, setSaving] = useState(false);
    const [editError, setEditError] = useState('');

    const [actingId, setActingId] = useState(null);

    const loadUsers = async (params = {}) => {
        setLoading(true);
        setError('');

        try {
            const currentSearch = params.search ?? search;
            const currentRole = params.role ?? role;
            const currentView = params.view ?? view;

            const query = {
                search: currentSearch,
                role: currentRole,
                per_page: 20,
                page: params.page ?? pagination?.current_page ?? 1,
            };

            if (currentView === 'trash') {
                query.trashed = 'only';
            } else if (currentView === 'all') {
                query.trashed = 'with';
            }

            const response = await api.get('/admin/users', {
                params: query,
            });

            setUsers(response.data.data || []);
            setPagination(response.data.meta || null);
        } catch (error) {
            console.error('Failed to load users:', error);

            setError(
                error.response?.data?.message ||
                    'Unable to load users.'
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadUsers({ page: 1 });
    }, []);

    const handleSearch = () => {
        loadUsers({ page: 1 });
    };

    const handleViewChange = (nextView) => {
        setView(nextView);
        loadUsers({ view: nextView, page: 1 });
    };

    const handleRoleChange = (event) => {
        const nextRole = event.target.value;

        setRole(nextRole);
        loadUsers({ role: nextRole, page: 1 });
    };

    const handlePageChange = (page) => {
        if (!pagination) {
            return;
        }

        if (
            page < 1 ||
            page > pagination.last_page ||
            page === pagination.current_page
        ) {
            return;
        }

        loadUsers({ page });
    };

    const openEdit = (selectedUser) => {
        setEditingUser(selectedUser);
        setEditName(selectedUser.name || '');
        setEditRole(selectedUser.role);
        setEditError('');
    };

    const closeEdit = () => {
        setEditingUser(null);
        setEditName('');
        setEditRole('');
        setEditError('');
    };

    const handleUpdateUser = async () => {
        if (
            !editingUser ||
            !editName.trim() ||
            !editRole ||
            saving
        ) {
            return;
        }

        setSaving(true);
        setEditError('');

        try {
            await api.patch('/admin/users', {
                email: editingUser.email,
                name: editName,
                role: editRole,
            });

            closeEdit();

            await loadUsers({
                page: pagination?.current_page || 1,
            });
        } catch (error) {
            console.error('Failed to update user:', error);

            setEditError(
                error.response?.data?.message ||
                    'Unable to update the user.'
            );
        } finally {
            setSaving(false);
        }
    };

    const currentPageFallback = () => {
        const currentPage = pagination?.current_page || 1;

        return users.length === 1 && currentPage > 1
            ? currentPage - 1
            : currentPage;
    };

    const handleStatusChange = async (user) => {
        const nextStatus =
            user.status === 'active' ? 'inactive' : 'active';

        if (
            !window.confirm(
                `Set "${user.name}" status to ${nextStatus}?`
            )
        ) {
            return;
        }

        setActingId(user.id);
        setError('');

        try {
            await api.patch(`/admin/users/${user.id}/status`, {
                status: nextStatus,
            });

            await loadUsers({
                page: pagination?.current_page || 1,
            });
        } catch (error) {
            console.error('Failed to update user status:', error);

            setError(
                error.response?.data?.message ||
                    'Unable to update the user status.'
            );
        } finally {
            setActingId(null);
        }
    };

    const handleDelete = async (user) => {
        if (
            !window.confirm(
                `Delete "${user.name}"? You can restore them later.`
            )
        ) {
            return;
        }

        setActingId(user.id);
        setError('');

        try {
            await api.delete(`/admin/users/${user.id}`);

            await loadUsers({ page: currentPageFallback() });
        } catch (error) {
            console.error('Failed to delete user:', error);

            setError(
                error.response?.data?.message ||
                    'Unable to delete the user.'
            );
        } finally {
            setActingId(null);
        }
    };

    const handleRestore = async (user) => {
        if (!window.confirm(`Restore "${user.name}"?`)) {
            return;
        }

        setActingId(user.id);
        setError('');

        try {
            await api.post(`/admin/users/${user.id}/restore`);

            await loadUsers({ page: currentPageFallback() });
        } catch (error) {
            console.error('Failed to restore user:', error);

            setError(
                error.response?.data?.message ||
                    'Unable to restore the user.'
            );
        } finally {
            setActingId(null);
        }
    };

    const formatDate = (date) => {
        if (!date) {
            return '';
        }

        return new Date(date).toLocaleDateString();
    };

    const viewOptions = [
        { id: 'active', label: 'Active' },
        { id: 'trash', label: 'Trash' },
        { id: 'all', label: 'All' },
    ];

    return (
        <div>
            <div className="mb-8">
                <p className="text-sm font-semibold text-blue-600">
                    Management
                </p>

                <h1 className="mt-1 text-3xl font-bold text-slate-900">
                    Users
                </h1>

                <p className="mt-2 text-sm text-slate-500">
                    Manage registered users and their roles.
                </p>
            </div>

            <div className="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    {viewOptions.map((option) => (
                        <button
                            key={option.id}
                            type="button"
                            onClick={() =>
                                handleViewChange(option.id)
                            }
                            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
                                view === option.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }`}
                        >
                            {option.label}
                        </button>
                    ))}
                </div>

                <div className="grid gap-4 md:grid-cols-[1fr_200px_auto]">
                    <input
                        type="text"
                        value={search}
                        onChange={(event) =>
                            setSearch(event.target.value)
                        }
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') {
                                handleSearch();
                            }
                        }}
                        placeholder="Search by name or email..."
                        className="rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    />

                    <select
                        value={role}
                        onChange={handleRoleChange}
                        className="rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">All roles</option>

                        <option value="user">User</option>

                        <option value="admin">Admin</option>
                    </select>

                    <button
                        type="button"
                        onClick={handleSearch}
                        className="rounded-xl bg-blue-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        Search
                    </button>
                </div>
            </div>

            {error && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            )}

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[900px]">
                        <thead className="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Name
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Email
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Role
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Created
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Deleted
                                </th>

                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {loading ? (
                                <tr>
                                    <td
                                        colSpan="7"
                                        className="px-6 py-12 text-center text-sm text-slate-500"
                                    >
                                        Loading users...
                                    </td>
                                </tr>
                            ) : users.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan="7"
                                        className="px-6 py-12 text-center text-sm text-slate-500"
                                    >
                                        No users found.
                                    </td>
                                </tr>
                            ) : (
                                users.map((item) => {
                                    const isDeleted = Boolean(
                                        item.deleted_at
                                    );

                                    return (
                                        <tr
                                            key={item.id}
                                            className="transition hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4">
                                                <p className="font-medium text-slate-900">
                                                    {item.name}
                                                </p>
                                            </td>

                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {item.email}
                                            </td>

                                            <td className="px-6 py-4">
                                                <span
                                                    className={`rounded-full px-3 py-1 text-xs font-medium ${
                                                        item.role === 'admin'
                                                            ? 'bg-blue-50 text-blue-600'
                                                            : 'bg-slate-100 text-slate-600'
                                                    }`}
                                                >
                                                    {item.role}
                                                </span>
                                            </td>

                                            <td className="px-6 py-4">
                                                <span
                                                    className={`rounded-full px-3 py-1 text-xs font-medium ${
                                                        item.status ===
                                                        'active'
                                                            ? 'bg-green-50 text-green-700'
                                                            : 'bg-slate-100 text-slate-600'
                                                    }`}
                                                >
                                                    {item.status}
                                                </span>
                                            </td>

                                            <td className="px-6 py-4 text-sm text-slate-500">
                                                {item.created_at
                                                    ? formatDate(
                                                          item.created_at
                                                      )
                                                    : '-'}
                                            </td>

                                            <td className="px-6 py-4 text-sm text-slate-500">
                                                {isDeleted
                                                    ? formatDate(
                                                          item.deleted_at
                                                      )
                                                    : '—'}
                                            </td>

                                            <td className="px-6 py-4">
                                                {isDeleted ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            handleRestore(
                                                                item
                                                            )
                                                        }
                                                        disabled={
                                                            actingId ===
                                                            item.id
                                                        }
                                                        className="rounded-lg px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        {actingId === item.id
                                                            ? 'Restoring...'
                                                            : 'Restore'}
                                                    </button>
                                                ) : (
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                openEdit(
                                                                    item
                                                                )
                                                            }
                                                            disabled={
                                                                actingId ===
                                                                item.id
                                                            }
                                                            className="rounded-lg px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            Edit
                                                        </button>

                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                handleStatusChange(
                                                                    item
                                                                )
                                                            }
                                                            disabled={
                                                                actingId ===
                                                                item.id
                                                            }
                                                            className="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            {actingId ===
                                                            item.id
                                                                ? 'Working...'
                                                                : item.status ===
                                                                  'active'
                                                                ? 'Deactivate'
                                                                : 'Activate'}
                                                        </button>

                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                handleDelete(
                                                                    item
                                                                )
                                                            }
                                                            disabled={
                                                                actingId ===
                                                                item.id
                                                            }
                                                            className="rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            {actingId ===
                                                            item.id
                                                                ? 'Working...'
                                                                : 'Delete'}
                                                        </button>
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>

                {pagination && pagination.last_page > 1 && (
                    <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                        <p className="text-sm text-slate-500">
                            Page{' '}
                            <span className="font-medium text-slate-700">
                                {pagination.current_page}
                            </span>{' '}
                            of{' '}
                            <span className="font-medium text-slate-700">
                                {pagination.last_page}
                            </span>
                        </p>

                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    handlePageChange(
                                        pagination.current_page - 1
                                    )
                                }
                                disabled={
                                    pagination.current_page === 1
                                }
                                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                Previous
                            </button>

                            {Array.from(
                                {
                                    length: pagination.last_page,
                                },
                                (_, index) => index + 1
                            ).map((page) => (
                                <button
                                    key={page}
                                    type="button"
                                    onClick={() =>
                                        handlePageChange(page)
                                    }
                                    className={`h-9 min-w-9 rounded-lg px-3 text-sm font-medium transition ${
                                        page ===
                                        pagination.current_page
                                            ? 'bg-blue-600 text-white'
                                            : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600'
                                    }`}
                                >
                                    {page}
                                </button>
                            ))}

                            <button
                                type="button"
                                onClick={() =>
                                    handlePageChange(
                                        pagination.current_page + 1
                                    )
                                }
                                disabled={
                                    pagination.current_page ===
                                    pagination.last_page
                                }
                                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {editingUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        <div className="flex items-start justify-between">
                            <div>
                                <h2 className="text-lg font-bold text-slate-900">
                                    Edit User
                                </h2>

                                <p className="mt-1 text-sm text-slate-500">
                                    Update the user's name and role.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeEdit}
                                className="text-2xl leading-none text-slate-400 transition hover:text-slate-600"
                            >
                                ×
                            </button>
                        </div>

                        {editError && (
                            <div className="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                {editError}
                            </div>
                        )}

                        <div className="mt-6">
                            <label className="block text-sm font-medium text-slate-700">
                                Email
                            </label>

                            <input
                                type="email"
                                value={editingUser.email}
                                disabled
                                className="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                            />
                        </div>

                        <div className="mt-4">
                            <label className="block text-sm font-medium text-slate-700">
                                Name
                            </label>

                            <input
                                type="text"
                                value={editName}
                                onChange={(event) =>
                                    setEditName(event.target.value)
                                }
                                className="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            />
                        </div>

                        <div className="mt-4">
                            <label className="block text-sm font-medium text-slate-700">
                                Role
                            </label>

                            <select
                                value={editRole}
                                onChange={(event) =>
                                    setEditRole(event.target.value)
                                }
                                className="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                                <option value="user">User</option>

                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div className="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={closeEdit}
                                disabled={saving}
                                className="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                onClick={handleUpdateUser}
                                disabled={saving}
                                className="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {saving ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default AdminUsers;