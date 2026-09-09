import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import api from '@/lib/api';

let syncTimeout: any;

export interface CartItem {
    skuId: number;
    productId: number;
    name: string;
    slug: string;
    variant: string; // "Black - L"
    price: number;
    mrp?: number;
    image: string;
    quantity: number;
    tax_class?: string;
    colorName?: string;
    colorHex?: string;
    sizeName?: string;
    size?: string;
    deliveryDate?: string;
}

interface CartState {
    items: CartItem[];
    addItem: (item: CartItem) => void;
    removeItem: (skuId: number) => void;
    updateQuantity: (skuId: number, quantity: number) => void;
    clearCart: () => void;
    total: () => number;
    appliedCoupon: { code: string; discountAmount: number } | null;
    setAppliedCoupon: (coupon: { code: string; discountAmount: number } | null) => void;
    cartToken: string;
    guestEmail: string | null;
    setGuestEmail: (email: string | null) => void;
    fetchFromServer: (merge?: boolean) => Promise<void>;
}

export const useCartStore = create<CartState>()(
    persist(
        (set, get) => ({
            items: [],
            appliedCoupon: null,
            cartToken: crypto.randomUUID(),
            guestEmail: null,

            setGuestEmail: (email) => set({ guestEmail: email }),
            setAppliedCoupon: (coupon) => set({ appliedCoupon: coupon }),

            fetchFromServer: async (merge = false) => {
                try {
                    clearTimeout(syncTimeout);
                    const res = await api.get('/api/cart');
                    if (res.data.cart_token) {
                        set((state) => {
                            if (!merge) {
                                return { items: res.data.items, cartToken: res.data.cart_token };
                            }
                            const merged = [...state.items];
                            res.data.items.forEach((sItem: any) => {
                                const existingIndex = merged.findIndex(i => i.skuId === sItem.skuId);
                                if (existingIndex > -1) {
                                    merged[existingIndex].quantity = Math.max(merged[existingIndex].quantity, sItem.quantity);
                                } else {
                                    merged.push(sItem);
                                }
                            });
                            return { items: merged, cartToken: res.data.cart_token };
                        });
                    }
                    // Force push to server to ensure user_id and merged items are saved
                    const state = get();
                    api.post('/api/cart/sync', {
                        cart_token: state.cartToken,
                        guest_email: state.guestEmail,
                        items: state.items
                    }).catch(e => console.error("Cart sync failed:", e));
                } catch (e) {
                    console.error("Cart fetch failed", e);
                }
            },

            addItem: (newItem) => set((state) => {
                const existing = state.items.find(i => i.skuId === newItem.skuId);
                if (existing) {
                    return {
                        items: state.items.map(i =>
                            i.skuId === newItem.skuId
                                ? { ...i, quantity: i.quantity + newItem.quantity, price: newItem.price, mrp: newItem.mrp }
                                : i
                        )
                    };
                }
                return { items: [...state.items, newItem] };
            }),

            removeItem: (skuId) => set((state) => ({
                items: state.items.filter(i => i.skuId !== skuId)
            })),

            updateQuantity: (skuId, quantity) => set((state) => ({
                items: state.items.map(i =>
                    i.skuId === skuId ? { ...i, quantity } : i
                )
            })),

            clearCart: () => set({ items: [], appliedCoupon: null }),

            total: () => {
                return get().items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            }
        }),
        {
            name: 'dope-cart-storage',
            storage: createJSONStorage(() => localStorage),
        }
    )
);

// Subscribe to store changes and sync with backend
useCartStore.subscribe((state, prevState) => {
    // Only sync if items or guestEmail changed
    if (JSON.stringify(state.items) !== JSON.stringify(prevState.items) || state.guestEmail !== prevState.guestEmail) {
        clearTimeout(syncTimeout);
        syncTimeout = setTimeout(() => {
            api.post('/api/cart/sync', {
                cart_token: state.cartToken,
                guest_email: state.guestEmail,
                items: state.items
            }).catch(e => console.error("Cart sync failed:", e));
        }, 1000); // debounce 1 second
    }
});
