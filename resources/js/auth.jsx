import { createContext, useCallback, useContext, useEffect, useState } from 'react';

import api, { primeCsrfToken } from './api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const fetchCurrentUser = useCallback(async () => {
        try {
            await primeCsrfToken();

            const response = await api.get('/user');

            setUser(response.data);
        } catch (error) {
            setUser(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchCurrentUser();
    }, [fetchCurrentUser]);

    const login = async (credentials) => {
        const response = await api.post('/login', credentials);

        setUser(response.data.data?.user ?? null);

        return response;
    };

    const register = async (form) => {
        const response = await api.post('/register', form);

        setUser(response.data.data?.user ?? null);

        return response;
    };

    const logout = async () => {
        try {
            await api.post('/logout');
        } finally {
            setUser(null);
        }
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }

    return context;
}