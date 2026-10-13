import { useEffect, useState } from 'react';

import api from '../api/client';
import AdminSidebar from '../components/AdminSidebar';
import AdminUsers from '../components/AdminUsers';
import Header from '../components/Header';

function AdminDashboard({ user, onLogout }) {
    const [activeSection, setActiveSection] = useState('dashboard');

    const [users, setUsers] = useState([]);
    const [auditLogs, setAuditLogs] = useState([]);

    const [loadingUsers, setLoadingUsers] = useState(false);
    const [loadingLogs, setLoadingLogs] = useState(false);

    const [search, setSearch] = useState('');
    const [role, setRole] = useState('');

    const [auditAction, setAuditAction] = useState('');
    const [auditUserId, setAuditUserId] = useState('');

    const [userPagination, setUserPagination] = useState(null);

    const [auditUserOptions, setAuditUserOptions] = useState([]);
    const [logPagination, setLogPagination] = useState(null);
    const [logError, setLogError] = useState('');

   useEffect(() => {
    if (
        activeSection === 'dashboard'
    ) {
        loadUsers();
        loadAuditLogs();
    }

    if (
        activeSection === 'audit-logs'
    ) {
        loadAuditLogs();
        loadAuditUserOptions();
    }
}, [activeSection]);
    const loadUsers = async (params = {}) => {
        try {
            setLoadingUsers(true);

            const response = await api.get('/admin/users', {
                params: {
                    search: params.search ?? search,
                    role: params.role ?? role,
                    per_page: 20,
                    page: params.page ?? 1,
                },
            });

            setUsers(response.data.data || []);
            setUserPagination(response.data.meta || null);
        } catch (error) {
            console.error('Failed to load users:', error);
        } finally {
            setLoadingUsers(false);
        }
    };

    const loadAuditLogs = async (params = {}) => {
        setLoadingLogs(true);
        setLogError('');

        try {
            const response = await api.get('/admin/audit-logs', {
                params: {
                    action: params.action ?? auditAction,
                    user_id: params.user_id ?? auditUserId,
                    per_page: 20,
                    page: params.page ?? 1,
                },
            });
            setAuditLogs(response.data.data || []);
            setLogPagination(response.data.meta || null);
        } catch (error) {
            console.error('Failed to load audit logs:', error);

            setLogError(
                error.response?.data?.message ||
                    'Unable to load audit logs.'
            );
        } finally {
            setLoadingLogs(false);
        }
    };

    const loadAuditUserOptions = async () => {
        try {
            const response = await api.get('/admin/users/options');

            setAuditUserOptions(response.data.data || []);
        } catch (error) {
            console.error('Failed to load user options:', error);
        }
    };

    const handleAuditFilter = () => {
        loadAuditLogs({
            action: auditAction,
            user_id: auditUserId,
            page: 1,
        });
    };

    const handleLogPageChange = (page) => {
        if (
            !logPagination ||
            page < 1 ||
            page > logPagination.last_page ||
            page === logPagination.current_page
        ) {
            return;
        }

        loadAuditLogs({ page });
    };

    const handleLogout = () => {
        onLogout?.();
    };

    const totalUsers = userPagination?.total || users.length;

    const adminUsers = users.filter(
        (item) => item.role === 'admin'
    ).length;

    const regularUsers = users.filter(
        (item) => item.role === 'user'
    ).length;

    return (
        <div className="min-h-screen bg-slate-50">
            <AdminSidebar
                activeSection={activeSection}
                onSectionChange={setActiveSection}
                onLogout={handleLogout}
            />

            <div className="lg:pl-64">
                <Header user={user} />

                <main className="p-6 lg:p-8">
                    {activeSection === 'dashboard' && (
                        <div>
                            <div className="mb-8">
                                <p className="text-sm font-semibold text-blue-600">
                                    Admin Panel
                                </p>

                                <h1 className="mt-1 text-3xl font-bold text-slate-900">
                                    Dashboard
                                </h1>

                                <p className="mt-2 text-sm text-slate-500">
                                    Manage users and monitor system
                                    activity.
                                </p>
                            </div>

                            <div className="grid gap-5 md:grid-cols-3">
                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <p className="text-sm font-medium text-slate-500">
                                        Total Users
                                    </p>

                                    <p className="mt-3 text-3xl font-bold text-slate-900">
                                        {totalUsers}
                                    </p>
                                </div>

                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <p className="text-sm font-medium text-slate-500">
                                        Admins
                                    </p>

                                    <p className="mt-3 text-3xl font-bold text-blue-600">
                                        {adminUsers}
                                    </p>
                                </div>

                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <p className="text-sm font-medium text-slate-500">
                                        Regular Users
                                    </p>

                                    <p className="mt-3 text-3xl font-bold text-slate-900">
                                        {regularUsers}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-900">
                                                Recent Users
                                            </h2>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Recently registered users.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={() =>
                                                setActiveSection('users')
                                            }
                                            className="text-sm font-medium text-blue-600 hover:text-blue-700"
                                        >
                                            View all
                                        </button>
                                    </div>

                                    <div className="mt-5 space-y-4">
                                        {users.slice(0, 5).map((item) => (
                                            <div
                                                key={item.id}
                                                className="flex items-center justify-between border-b border-slate-100 pb-4 last:border-0 last:pb-0"
                                            >
                                                <div>
                                                    <p className="font-medium text-slate-900">
                                                        {item.name}
                                                    </p>

                                                    <p className="text-sm text-slate-500">
                                                        {item.email}
                                                    </p>
                                                </div>

                                                <span
                                                    className={`rounded-full px-3 py-1 text-xs font-medium ${
                                                        item.role === 'admin'
                                                            ? 'bg-blue-50 text-blue-600'
                                                            : 'bg-slate-100 text-slate-600'
                                                    }`}
                                                >
                                                    {item.role}
                                                </span>
                                            </div>
                                        ))}

                                        {!loadingUsers &&
                                            users.length === 0 && (
                                                <p className="py-6 text-center text-sm text-slate-500">
                                                    No users found.
                                                </p>
                                            )}
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-900">
                                                Recent Activity
                                            </h2>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Latest audit events.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={() =>
                                                setActiveSection(
                                                    'audit-logs'
                                                )
                                            }
                                            className="text-sm font-medium text-blue-600 hover:text-blue-700"
                                        >
                                            View all
                                        </button>
                                    </div>

                                    <div className="mt-5 space-y-4">
                                        {auditLogs
                                            .slice(0, 5)
                                            .map((log) => (
                                                <div
                                                    key={log.id}
                                                    className="flex items-center justify-between border-b border-slate-100 pb-4 last:border-0 last:pb-0"
                                                >
                                                    <div>
                                                        <p className="font-medium text-slate-900">
                                                            {log.action}
                                                        </p>

                                                        <p className="text-sm text-slate-500">
                                                            {log.user?.name ||
                                                                'Unknown user'}
                                                        </p>
                                                    </div>

                                                    <span className="text-xs text-slate-400">
                                                        {log.created_at
                                                            ? new Date(
                                                                  log.created_at
                                                              ).toLocaleDateString()
                                                            : '-'}
                                                    </span>
                                                </div>
                                            ))}

                                        {!loadingLogs &&
                                            auditLogs.length === 0 && (
                                                <p className="py-6 text-center text-sm text-slate-500">
                                                    No audit activity found.
                                                </p>
                                            )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {activeSection === 'users' && (
                        <AdminUsers />
                    )}

                    {activeSection === 'audit-logs' && (
                        <div>
                            <div className="mb-8">
                                <p className="text-sm font-semibold text-blue-600">
                                    Security
                                </p>

                                <h1 className="mt-1 text-3xl font-bold text-slate-900">
                                    Audit Logs
                                </h1>

                                <p className="mt-2 text-sm text-slate-500">
                                    Monitor sensitive activity across
                                    the system.
                                </p>
                            </div>

                            <div className="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div className="grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                                    <select
                                        value={auditAction}
                                        onChange={(event) =>
                                            setAuditAction(
                                                event.target.value
                                            )
                                        }
                                        className="rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    >
                                        <option value="">
                                            All actions
                                        </option>

                                        <option value="DOCUMENT_UPLOADED">
                                            Document Uploaded
                                        </option>

                                        <option value="DOCUMENT_DOWNLOADED">
                                            Document Downloaded
                                        </option>

                                        <option value="DOCUMENT_SHARED">
                                            Document Shared
                                        </option>

                                        <option value="DOCUMENT_UPDATED">
                                            Document Updated
                                        </option>

                                        <option value="DOCUMENT_SHARE_REVOKED">
                                            Document Revoked
                                        </option>

                                        <option value="DOCUMENT_DELETED">
                                            Document Deleted
                                        </option>
                                    </select>

                                    <select
                                        value={auditUserId}
                                        onChange={(event) =>
                                            setAuditUserId(
                                                event.target.value
                                            )
                                        }
                                        className="rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    >
                                        <option value="">
                                            All users
                                        </option>

                                        {auditUserOptions.map((item) => (
                                            <option
                                                key={item.id}
                                                value={item.id}
                                            >
                                                {item.name} — {item.email}
                                            </option>
                                        ))}
                                    </select>

                                    <button
                                        type="button"
                                        onClick={handleAuditFilter}
                                        className="rounded-xl bg-blue-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-blue-700"
                                    >
                                        Filter
                                    </button>
                                </div>
                            </div>

                            {logError && (
                                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    {logError}
                                </div>
                            )}

                            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[800px]">
                                        <thead className="border-b border-slate-200 bg-slate-50">
                                            <tr>
                                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    Action
                                                </th>

                                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    User
                                                </th>

                                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    Target
                                                </th>

                                                <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody className="divide-y divide-slate-100">
                                            {loadingLogs ? (
                                                <tr>
                                                    <td
                                                        colSpan="4"
                                                        className="px-6 py-12 text-center text-sm text-slate-500"
                                                    >
                                                        Loading audit logs...
                                                    </td>
                                                </tr>
                                            ) : auditLogs.length === 0 ? (
                                                <tr>
                                                    <td
                                                        colSpan="4"
                                                        className="px-6 py-12 text-center text-sm text-slate-500"
                                                    >
                                                        No audit logs found.
                                                    </td>
                                                </tr>
                                            ) : (
                                                auditLogs.map((log) => (
                                                    <tr
                                                        key={log.id}
                                                        className="transition hover:bg-slate-50"
                                                    >
                                                        <td className="px-6 py-4">
                                                            <span className="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">
                                                                {log.action}
                                                            </span>
                                                        </td>

                                                        <td className="px-6 py-4">
                                                            <p className="font-medium text-slate-900">
                                                                {log.user?.name ||
                                                                    'Unknown'}
                                                            </p>

                                                            <p className="text-sm text-slate-500">
                                                                {log.user?.email ||
                                                                    '-'}
                                                            </p>
                                                        </td>

                                                        <td className="px-6 py-4 text-sm text-slate-600">
                                                            {log.auditable_type
                                                                ? log.auditable_type
                                                                      .split(
                                                                          '\\'
                                                                      )
                                                                      .pop()
                                                                : '-'}
                                                            {log.auditable_id
                                                                ? ` #${log.auditable_id}`
                                                                : ''}
                                                        </td>

                                                        <td className="px-6 py-4 text-sm text-slate-500">
                                                            {log.created_at
                                                                ? new Date(
                                                                      log.created_at
                                                                  ).toLocaleString()
                                                                : '-'}
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                {logPagination &&
                                    logPagination.last_page > 1 && (
                                        <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                                            <p className="text-sm text-slate-500">
                                                Page{' '}
                                                <span className="font-medium text-slate-700">
                                                    {
                                                        logPagination.current_page
                                                    }
                                                </span>{' '}
                                                of{' '}
                                                <span className="font-medium text-slate-700">
                                                    {
                                                        logPagination.last_page
                                                    }
                                                </span>
                                            </p>

                                            <div className="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleLogPageChange(
                                                            logPagination.current_page -
                                                                1
                                                        )
                                                    }
                                                    disabled={
                                                        logPagination.current_page ===
                                                        1
                                                    }
                                                    className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    Previous
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleLogPageChange(
                                                            logPagination.current_page +
                                                                1
                                                        )
                                                    }
                                                    disabled={
                                                        logPagination.current_page ===
                                                        logPagination.last_page
                                                    }
                                                    className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    Next
                                                </button>
                                            </div>
                                        </div>
                                    )}
                            </div>
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
}

export default AdminDashboard;
