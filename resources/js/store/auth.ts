import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '@/lib/api';
import { useCartStore } from '@/store/cart';
import { useWishlistStore } from '@/store/wishlist';

interface User {
    id: number;
    name: string;
    email: string;
    phone?: string;
}

interface AuthState {
    user: User | null;
    token: string | null;
    login: (token: string, user: User) => void;
    logout: () => void;
    checkAuth: () => Promise<void>;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            user: null,
            token: null,
            login: (token, user) => {
                set({ token, user });
                // Make sure API instance uses the new token immediately
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                useCartStore.getState().fetchFromServer(true);
                useWishlistStore.getState().fetchFromServer(true);
            },
            logout: () => {
                set({ token: null, user: null });
                api.defaults.headers.common['Authorization'] = '';
                useCartStore.getState().clearCart();
                useWishlistStore.getState().clearWishlist();
            },
            checkAuth: async () => {
                const token = useAuthStore.getState().token;
                if (!token) return;

                try {
                    const res = await api.get('/api/user');
                    set({ user: res.data });
                    useCartStore.getState().fetchFromServer(false);
                    useWishlistStore.getState().fetchFromServer(false);
                } catch (error) {
                    set({ token: null, user: null });
                }
            }
        }),
        {
            name: 'auth-storage',
        }
    )
);

// Interceptor to always attach token
api.interceptors.request.use((config) => {
    const token = useAuthStore.getState().token;
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Interceptor to gracefully handle expired tokens
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            useAuthStore.getState().logout();
        }
        return Promise.reject(error);
    }
);
