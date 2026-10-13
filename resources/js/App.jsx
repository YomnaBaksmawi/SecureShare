import { useState } from 'react';

import { AuthProvider, useAuth } from './auth';

import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import AdminDashboard from './pages/AdminDashboard';

function AppContent() {
    const { user, loading, logout } = useAuth();

    const [showRegister, setShowRegister] = useState(false);

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-50">
                <p className="text-sm text-slate-500">Loading...</p>
            </div>
        );
    }

    if (!user) {
        if (showRegister) {
            return (
                <Register
                    onLogin={() => setShowRegister(false)}
                />
            );
        }

        return (
            <Login
                onRegister={() => setShowRegister(true)}
            />
        );
    }

    if (user.role === 'admin') {
        return (
            <AdminDashboard
                user={user}
                onLogout={logout}
            />
        );
    }

    return (
        <Dashboard
            user={user}
            onLogout={logout}
        />
    );
}

function App() {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
}

export default App;