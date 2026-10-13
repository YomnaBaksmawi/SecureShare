
function Sidebar({ activeSection, onSectionChange, onLogout }) {
    const navigationItems = [
        {
            id: 'dashboard',
            label: 'Dashboard',
        },
        {
            id: 'documents',
            label: 'My Documents',
        },
        {
            id: 'shared',
            label: 'Shared with me',
        },
        {
            id: 'friends',
            label: 'My Friends',
        },
        {
            id: 'friend-requests',
            label: 'Friend Requests',
        },
    ];

    return (
        <aside className="fixed left-0 top-0 hidden h-screen w-64 border-r border-slate-200 bg-white p-6 lg:block">
            <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600">
                    <svg
                        className="h-5 w-5 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 11c0 3.314-1.343 6-3 6s-3-2.686-3-6 1.343-6 3-6 3 2.686 3 6zm0 0c0-3.314 1.343-6 3-6s3 2.686 3 6-1.343 6-3 6-3-2.686-3-6z"
                        />
                    </svg>
                </div>

                <span className="text-lg font-bold text-slate-900">
                    SecureShare
                </span>
            </div>

            <nav className="mt-10 space-y-2">
                {navigationItems.map((item) => {
                    const isActive = activeSection === item.id;

                    return (
                        <button
                            key={item.id}
                            type="button"
                            onClick={() => onSectionChange(item.id)}
                            className={`flex w-full rounded-xl px-4 py-3 text-left text-sm font-medium transition ${
                                isActive
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-slate-600 hover:bg-slate-50'
                            }`}
                        >
                            {item.label}
                        </button>
                    );
                })}
            </nav>

            <button
                type="button"
                onClick={onLogout}
                className="absolute bottom-6 left-6 right-6 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600"
            >
                Logout
            </button>
        </aside>
    );
}

export default Sidebar;
