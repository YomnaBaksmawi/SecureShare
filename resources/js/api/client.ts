import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL
        ? `${import.meta.env.VITE_API_URL}/api`
        : '/api',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;
        const url = error.config?.url ?? '';

        if (status === 419) {
            window.location.reload();
        } else if (
            status === 401 &&
            !['/login', '/register', '/user', '/sanctum/csrf-cookie'].some(
                (path) => url.includes(path)
            )
        ) {
            window.location.reload();
        }

        return Promise.reject(error);
    }
);

export async function primeCsrfToken() {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export default api;