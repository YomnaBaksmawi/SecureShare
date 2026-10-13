function Stats({ documentsCount, usersCount }) {
    return (
        <div className="grid gap-5 sm:grid-cols-2">
            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p className="text-sm font-medium text-slate-500">
                    My Documents
                </p>

                <p className="mt-3 text-3xl font-bold text-slate-900">
                    {documentsCount}
                </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p className="text-sm font-medium text-slate-500">
                    Shareable Users
                </p>

                <p className="mt-3 text-3xl font-bold text-slate-900">
                    {usersCount}
                </p>
            </div>
        </div>
    );
}

export default Stats;