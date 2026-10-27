import { create } from 'zustand';

interface CartDrawerState {
    isOpen: boolean;
    openCartDrawer: () => void;
    closeCartDrawer: () => void;
    toggleCartDrawer: () => void;
}

export const useCartDrawerStore = create<CartDrawerState>((set) => ({
    isOpen: false,
    openCartDrawer: () => set({ isOpen: true }),
    closeCartDrawer: () => set({ isOpen: false }),
    toggleCartDrawer: () => set((state) => ({ isOpen: !state.isOpen })),
}));
