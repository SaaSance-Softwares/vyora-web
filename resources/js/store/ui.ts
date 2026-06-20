import { create } from 'zustand';
import { ProductDetail } from '@/types';

type AuthView = 'login' | 'register';
type QuickViewAction = 'cart' | 'buy';

interface UIState {
    isAuthModalOpen: boolean;
    authView: AuthView;
    isSearchOpen: boolean;
    quickViewProduct: ProductDetail | null;
    quickViewAction: QuickViewAction | null;
    openAuthModal: (view?: AuthView) => void;
    closeAuthModal: () => void;
    setAuthView: (view: AuthView) => void;
    openSearch: () => void;
    closeSearch: () => void;
    openQuickView: (product: ProductDetail, action: QuickViewAction) => void;
    closeQuickView: () => void;
}

export const useUIStore = create<UIState>((set) => ({
    isAuthModalOpen: false,
    authView: 'login',
    isSearchOpen: false,
    quickViewProduct: null,
    quickViewAction: null,
    openAuthModal: (view = 'login') => set({ isAuthModalOpen: true, authView: view }),
    closeAuthModal: () => set({ isAuthModalOpen: false }),
    setAuthView: (view) => set({ authView: view }),
    openSearch: () => set({ isSearchOpen: true }),
    closeSearch: () => set({ isSearchOpen: false }),
    openQuickView: (product, action) => set({ quickViewProduct: product, quickViewAction: action }),
    closeQuickView: () => set({ quickViewProduct: null, quickViewAction: null }),
}));
