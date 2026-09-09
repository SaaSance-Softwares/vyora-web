import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import api from '@/lib/api';

let syncTimeout: any;

export interface WishlistItem {
    productId: number;
    name: string;
    slug: string;
    price: number;
    mrp: number;
    discount_percentage: number;
    image: string | null;
    video?: string | null;
    brand: string | null;
    category: string;
    addedAt: string; // ISO timestamp
    skuId?: number;
    variant?: string;
    colorName?: string;
    colorHex?: string;
    sizeName?: string;
    size?: string;
    deliveryDate?: string;
}

interface WishlistState {
    items: WishlistItem[];
    addItem: (item: Omit<WishlistItem, 'addedAt'>) => void;
    removeItem: (productId: number) => void;
    isInWishlist: (productId: number) => boolean;
    clearWishlist: () => void;
    fetchFromServer: (merge?: boolean) => Promise<void>;
}

export const useWishlistStore = create<WishlistState>()(
    persist(
        (set, get) => ({
            items: [],

            addItem: (newItem) => set((state) => {
                const existing = state.items.find(i => i.productId === newItem.productId);
                if (existing) return state; // Already in wishlist
                return {
                    items: [{ ...newItem, addedAt: new Date().toISOString() }, ...state.items]
                };
            }),

            removeItem: (productId) => set((state) => ({
                items: state.items.filter(i => i.productId !== productId)
            })),

            isInWishlist: (productId) => {
                return get().items.some(i => i.productId === productId);
            },

            clearWishlist: () => set({ items: [] }),

            fetchFromServer: async () => {
                
                try {
                    clearTimeout(syncTimeout);
                    const res = await api.get('/api/wishlist');
                    if (res.data.items) {
                        set((state) => {
                            const merged = [...state.items];
                            res.data.items.forEach((sItem: any) => {
                                const existingIndex = merged.findIndex(i => i.productId === sItem.productId);
                                if (existingIndex === -1) {
                                    merged.push(sItem);
                                }
                            });
                            // Sort by addedAt descending
                            merged.sort((a, b) => new Date(b.addedAt).getTime() - new Date(a.addedAt).getTime());
                            return { items: merged };
                        });
                    }
                    
                    const state = get();
                    api.post('/api/wishlist/sync', { items: state.items }).catch(e => console.error("Wishlist sync failed", e));
                } catch (e) {
                    console.error("Wishlist fetch failed", e);
                }
            }
        }),
        {
            name: 'dope-wishlist-storage',
            storage: createJSONStorage(() => localStorage),
        }
    )
);

// Subscribe to store changes and sync with backend
useWishlistStore.subscribe((state, prevState) => {
    if (JSON.stringify(state.items) !== JSON.stringify(prevState.items)) {
        
        clearTimeout(syncTimeout);
        syncTimeout = setTimeout(() => {
            api.post('/api/wishlist/sync', { items: state.items }).catch(e => console.error("Wishlist sync failed:", e));
        }, 1000);
    }
});
