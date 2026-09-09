import { jsxs, jsx, Fragment } from "react/jsx-runtime";
import { create } from "zustand";
import { persist, createJSONStorage } from "zustand/middleware";
import axios from "axios";
import React, { useState, useRef, useEffect, Suspense, useCallback, useId, useMemo } from "react";
import { Link, usePage, router, Head, createInertiaApp } from "@inertiajs/react";
import { ChevronDown, Search, User, ArrowRight, LogOut, Shield, MapPin, Package, Gift, ChevronRight, Wallet, AlertCircle, Check, Lock, EyeOff, Eye, Plus, Pencil, Trash2, Mail, Phone, ShoppingBag, Heart, Minus, Loader2, SlidersHorizontal, Filter, X, Tag, Ticket, CheckCircle, Truck, Sparkles, CreditCard, Zap, Smartphone, History, LayoutGrid, Clock, Copy, MessageCircle, Share2, Star, Ruler, ChevronUp, ShieldCheck, TrendingUp, Home as Home$1, Menu, Landmark } from "lucide-react";
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import Confetti from "react-confetti";
import { useWindowSize } from "react-use";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation, Pagination, Autoplay } from "swiper/modules";
import createServer from "@inertiajs/react/server";
import { renderToString } from "react-dom/server";
const api = axios.create({
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Requested-With": "XMLHttpRequest"
  },
  withCredentials: true
});
let syncTimeout$1;
const useCartStore = create()(
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
          clearTimeout(syncTimeout$1);
          const res = await api.get("/api/cart");
          if (res.data.cart_token) {
            set((state2) => {
              if (!merge) {
                return { items: res.data.items, cartToken: res.data.cart_token };
              }
              const merged = [...state2.items];
              res.data.items.forEach((sItem) => {
                const existingIndex = merged.findIndex((i) => i.skuId === sItem.skuId);
                if (existingIndex > -1) {
                  merged[existingIndex].quantity = Math.max(merged[existingIndex].quantity, sItem.quantity);
                } else {
                  merged.push(sItem);
                }
              });
              return { items: merged, cartToken: res.data.cart_token };
            });
          }
          const state = get();
          api.post("/api/cart/sync", {
            cart_token: state.cartToken,
            guest_email: state.guestEmail,
            items: state.items
          }).catch((e) => console.error("Cart sync failed:", e));
        } catch (e) {
          console.error("Cart fetch failed", e);
        }
      },
      addItem: (newItem) => set((state) => {
        const existing = state.items.find((i) => i.skuId === newItem.skuId);
        if (existing) {
          return {
            items: state.items.map(
              (i) => i.skuId === newItem.skuId ? { ...i, quantity: i.quantity + newItem.quantity, price: newItem.price, mrp: newItem.mrp } : i
            )
          };
        }
        return { items: [...state.items, newItem] };
      }),
      removeItem: (skuId) => set((state) => ({
        items: state.items.filter((i) => i.skuId !== skuId)
      })),
      updateQuantity: (skuId, quantity) => set((state) => ({
        items: state.items.map(
          (i) => i.skuId === skuId ? { ...i, quantity } : i
        )
      })),
      clearCart: () => set({ items: [], appliedCoupon: null }),
      total: () => {
        return get().items.reduce((sum, item) => sum + item.price * item.quantity, 0);
      }
    }),
    {
      name: "dope-cart-storage",
      storage: createJSONStorage(() => localStorage)
    }
  )
);
useCartStore.subscribe((state, prevState) => {
  if (JSON.stringify(state.items) !== JSON.stringify(prevState.items) || state.guestEmail !== prevState.guestEmail) {
    clearTimeout(syncTimeout$1);
    syncTimeout$1 = setTimeout(() => {
      api.post("/api/cart/sync", {
        cart_token: state.cartToken,
        guest_email: state.guestEmail,
        items: state.items
      }).catch((e) => console.error("Cart sync failed:", e));
    }, 1e3);
  }
});
let syncTimeout;
const useWishlistStore = create()(
  persist(
    (set, get) => ({
      items: [],
      addItem: (newItem) => set((state) => {
        const existing = state.items.find((i) => i.productId === newItem.productId);
        if (existing) return state;
        return {
          items: [{ ...newItem, addedAt: (/* @__PURE__ */ new Date()).toISOString() }, ...state.items]
        };
      }),
      removeItem: (productId) => set((state) => ({
        items: state.items.filter((i) => i.productId !== productId)
      })),
      isInWishlist: (productId) => {
        return get().items.some((i) => i.productId === productId);
      },
      clearWishlist: () => set({ items: [] }),
      fetchFromServer: async () => {
        try {
          clearTimeout(syncTimeout);
          const res = await api.get("/api/wishlist");
          if (res.data.items) {
            set((state2) => {
              const merged = [...state2.items];
              res.data.items.forEach((sItem) => {
                const existingIndex = merged.findIndex((i) => i.productId === sItem.productId);
                if (existingIndex === -1) {
                  merged.push(sItem);
                }
              });
              merged.sort((a, b) => new Date(b.addedAt).getTime() - new Date(a.addedAt).getTime());
              return { items: merged };
            });
          }
          const state = get();
          api.post("/api/wishlist/sync", { items: state.items }).catch((e) => console.error("Wishlist sync failed", e));
        } catch (e) {
          console.error("Wishlist fetch failed", e);
        }
      }
    }),
    {
      name: "dope-wishlist-storage",
      storage: createJSONStorage(() => localStorage)
    }
  )
);
useWishlistStore.subscribe((state, prevState) => {
  if (JSON.stringify(state.items) !== JSON.stringify(prevState.items)) {
    clearTimeout(syncTimeout);
    syncTimeout = setTimeout(() => {
      api.post("/api/wishlist/sync", { items: state.items }).catch((e) => console.error("Wishlist sync failed:", e));
    }, 1e3);
  }
});
const useAuthStore = create()(
  persist(
    (set) => ({
      user: null,
      token: null,
      login: (token, user) => {
        set({ token, user });
        api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        useCartStore.getState().fetchFromServer(true);
        useWishlistStore.getState().fetchFromServer(true);
      },
      logout: () => {
        set({ token: null, user: null });
        api.defaults.headers.common["Authorization"] = "";
        useCartStore.getState().clearCart();
        useWishlistStore.getState().clearWishlist();
      },
      checkAuth: async () => {
        const token = useAuthStore.getState().token;
        if (!token) return;
        try {
          const res = await api.get("/api/user");
          set({ user: res.data });
          useCartStore.getState().fetchFromServer(false);
          useWishlistStore.getState().fetchFromServer(false);
        } catch (error) {
          set({ token: null, user: null });
        }
      }
    }),
    {
      name: "auth-storage"
    }
  )
);
api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore.getState().logout();
    }
    return Promise.reject(error);
  }
);
const useUIStore = create((set) => ({
  isAuthModalOpen: false,
  authView: "login",
  isSearchOpen: false,
  quickViewProduct: null,
  quickViewAction: null,
  openAuthModal: (view = "login") => set({ isAuthModalOpen: true, authView: view }),
  closeAuthModal: () => set({ isAuthModalOpen: false }),
  setAuthView: (view) => set({ authView: view }),
  openSearch: () => set({ isSearchOpen: true }),
  closeSearch: () => set({ isSearchOpen: false }),
  openQuickView: (product, action) => set({ quickViewProduct: product, quickViewAction: action }),
  closeQuickView: () => set({ quickViewProduct: null, quickViewAction: null })
}));
const COUNTRIES = [
  { name: "Afghanistan", dial_code: "+93", code: "AF", flag: "🇦🇫" },
  { name: "Albania", dial_code: "+355", code: "AL", flag: "🇦🇱" },
  { name: "Algeria", dial_code: "+213", code: "DZ", flag: "🇩🇿" },
  { name: "Andorra", dial_code: "+376", code: "AD", flag: "🇦🇩" },
  { name: "Angola", dial_code: "+244", code: "AO", flag: "🇦🇴" },
  { name: "Argentina", dial_code: "+54", code: "AR", flag: "🇦🇷" },
  { name: "Armenia", dial_code: "+374", code: "AM", flag: "🇦🇲" },
  { name: "Australia", dial_code: "+61", code: "AU", flag: "🇦🇺" },
  { name: "Austria", dial_code: "+43", code: "AT", flag: "🇦🇹" },
  { name: "Azerbaijan", dial_code: "+994", code: "AZ", flag: "🇦🇿" },
  { name: "Bahrain", dial_code: "+973", code: "BH", flag: "🇧🇭" },
  { name: "Bangladesh", dial_code: "+880", code: "BD", flag: "🇧🇩" },
  { name: "Belarus", dial_code: "+375", code: "BY", flag: "🇧🇾" },
  { name: "Belgium", dial_code: "+32", code: "BE", flag: "🇧🇪" },
  { name: "Bolivia", dial_code: "+591", code: "BO", flag: "🇧🇴" },
  { name: "Bosnia and Herzegovina", dial_code: "+387", code: "BA", flag: "🇧🇦" },
  { name: "Brazil", dial_code: "+55", code: "BR", flag: "🇧🇷" },
  { name: "Bulgaria", dial_code: "+359", code: "BG", flag: "🇧🇬" },
  { name: "Cambodia", dial_code: "+855", code: "KH", flag: "🇰🇭" },
  { name: "Cameroon", dial_code: "+237", code: "CM", flag: "🇨🇲" },
  { name: "Canada", dial_code: "+1", code: "CA", flag: "🇨🇦" },
  { name: "Chile", dial_code: "+56", code: "CL", flag: "🇨🇱" },
  { name: "China", dial_code: "+86", code: "CN", flag: "🇨🇳" },
  { name: "Colombia", dial_code: "+57", code: "CO", flag: "🇨🇴" },
  { name: "Costa Rica", dial_code: "+506", code: "CR", flag: "🇨🇷" },
  { name: "Croatia", dial_code: "+385", code: "HR", flag: "🇭🇷" },
  { name: "Cuba", dial_code: "+53", code: "CU", flag: "🇨🇺" },
  { name: "Cyprus", dial_code: "+357", code: "CY", flag: "🇨🇾" },
  { name: "Czech Republic", dial_code: "+420", code: "CZ", flag: "🇨🇿" },
  { name: "Denmark", dial_code: "+45", code: "DK", flag: "🇩🇰" },
  { name: "Dominican Republic", dial_code: "+1", code: "DO", flag: "🇩🇴" },
  { name: "Ecuador", dial_code: "+593", code: "EC", flag: "🇪🇨" },
  { name: "Egypt", dial_code: "+20", code: "EG", flag: "🇪🇬" },
  { name: "El Salvador", dial_code: "+503", code: "SV", flag: "🇸🇻" },
  { name: "Estonia", dial_code: "+372", code: "EE", flag: "🇪🇪" },
  { name: "Finland", dial_code: "+358", code: "FI", flag: "🇫🇮" },
  { name: "France", dial_code: "+33", code: "FR", flag: "🇫🇷" },
  { name: "Georgia", dial_code: "+995", code: "GE", flag: "🇬🇪" },
  { name: "Germany", dial_code: "+49", code: "DE", flag: "🇩🇪" },
  { name: "Ghana", dial_code: "+233", code: "GH", flag: "🇬🇭" },
  { name: "Greece", dial_code: "+30", code: "GR", flag: "🇬🇷" },
  { name: "Guatemala", dial_code: "+502", code: "GT", flag: "🇬🇹" },
  { name: "Honduras", dial_code: "+504", code: "HN", flag: "🇭🇳" },
  { name: "Hong Kong", dial_code: "+852", code: "HK", flag: "🇭🇰" },
  { name: "Hungary", dial_code: "+36", code: "HU", flag: "🇭🇺" },
  { name: "Iceland", dial_code: "+354", code: "IS", flag: "🇮🇸" },
  { name: "India", dial_code: "+91", code: "IN", flag: "🇮🇳" },
  { name: "Indonesia", dial_code: "+62", code: "ID", flag: "🇮🇩" },
  { name: "Iran", dial_code: "+98", code: "IR", flag: "🇮🇷" },
  { name: "Iraq", dial_code: "+964", code: "IQ", flag: "🇮🇶" },
  { name: "Ireland", dial_code: "+353", code: "IE", flag: "🇮🇪" },
  { name: "Israel", dial_code: "+972", code: "IL", flag: "🇮🇱" },
  { name: "Italy", dial_code: "+39", code: "IT", flag: "🇮🇹" },
  { name: "Japan", dial_code: "+81", code: "JP", flag: "🇯🇵" },
  { name: "Jordan", dial_code: "+962", code: "JO", flag: "🇯🇴" },
  { name: "Kazakhstan", dial_code: "+7", code: "KZ", flag: "🇰🇿" },
  { name: "Kenya", dial_code: "+254", code: "KE", flag: "🇰🇪" },
  { name: "Kuwait", dial_code: "+965", code: "KW", flag: "🇰🇼" },
  { name: "Latvia", dial_code: "+371", code: "LV", flag: "🇱🇻" },
  { name: "Lebanon", dial_code: "+961", code: "LB", flag: "🇱🇧" },
  { name: "Lithuania", dial_code: "+370", code: "LT", flag: "🇱🇹" },
  { name: "Luxembourg", dial_code: "+352", code: "LU", flag: "🇱🇺" },
  { name: "Macau", dial_code: "+853", code: "MO", flag: "🇲🇴" },
  { name: "Macedonia", dial_code: "+389", code: "MK", flag: "🇲🇰" },
  { name: "Malaysia", dial_code: "+60", code: "MY", flag: "🇲🇾" },
  { name: "Maldives", dial_code: "+960", code: "MV", flag: "🇲🇻" },
  { name: "Malta", dial_code: "+356", code: "MT", flag: "🇲🇹" },
  { name: "Mexico", dial_code: "+52", code: "MX", flag: "🇲🇽" },
  { name: "Moldova", dial_code: "+373", code: "MD", flag: "🇲🇩" },
  { name: "Morocco", dial_code: "+212", code: "MA", flag: "🇲🇦" },
  { name: "Myanmar", dial_code: "+95", code: "MM", flag: "🇲🇲" },
  { name: "Nepal", dial_code: "+977", code: "NP", flag: "🇳🇵" },
  { name: "Netherlands", dial_code: "+31", code: "NL", flag: "🇳🇱" },
  { name: "New Zealand", dial_code: "+64", code: "NZ", flag: "🇳🇿" },
  { name: "Nigeria", dial_code: "+234", code: "NG", flag: "🇳🇬" },
  { name: "North Korea", dial_code: "+850", code: "KP", flag: "🇰🇵" },
  { name: "Norway", dial_code: "+47", code: "NO", flag: "🇳🇴" },
  { name: "Oman", dial_code: "+968", code: "OM", flag: "🇴🇲" },
  { name: "Pakistan", dial_code: "+92", code: "PK", flag: "🇵🇰" },
  { name: "Palestine", dial_code: "+970", code: "PS", flag: "🇵🇸" },
  { name: "Panama", dial_code: "+507", code: "PA", flag: "🇵🇦" },
  { name: "Paraguay", dial_code: "+595", code: "PY", flag: "🇵🇾" },
  { name: "Peru", dial_code: "+51", code: "PE", flag: "🇵🇪" },
  { name: "Philippines", dial_code: "+63", code: "PH", flag: "🇵🇭" },
  { name: "Poland", dial_code: "+48", code: "PL", flag: "🇵🇱" },
  { name: "Portugal", dial_code: "+351", code: "PT", flag: "🇵🇹" },
  { name: "Qatar", dial_code: "+974", code: "QA", flag: "🇶🇦" },
  { name: "Romania", dial_code: "+40", code: "RO", flag: "🇷🇴" },
  { name: "Russia", dial_code: "+7", code: "RU", flag: "🇷🇺" },
  { name: "Saudi Arabia", dial_code: "+966", code: "SA", flag: "🇸🇦" },
  { name: "Senegal", dial_code: "+221", code: "SN", flag: "🇸🇳" },
  { name: "Serbia", dial_code: "+381", code: "RS", flag: "🇷🇸" },
  { name: "Singapore", dial_code: "+65", code: "SG", flag: "🇸🇬" },
  { name: "Slovakia", dial_code: "+421", code: "SK", flag: "🇸🇰" },
  { name: "Slovenia", dial_code: "+386", code: "SI", flag: "🇸🇮" },
  { name: "South Africa", dial_code: "+27", code: "ZA", flag: "🇿🇦" },
  { name: "South Korea", dial_code: "+82", code: "KR", flag: "🇰🇷" },
  { name: "Spain", dial_code: "+34", code: "ES", flag: "🇪🇸" },
  { name: "Sri Lanka", dial_code: "+94", code: "LK", flag: "🇱🇰" },
  { name: "Sudan", dial_code: "+249", code: "SD", flag: "🇸🇩" },
  { name: "Sweden", dial_code: "+46", code: "SE", flag: "🇸🇪" },
  { name: "Switzerland", dial_code: "+41", code: "CH", flag: "🇨🇭" },
  { name: "Syria", dial_code: "+963", code: "SY", flag: "🇸🇾" },
  { name: "Taiwan", dial_code: "+886", code: "TW", flag: "🇹🇼" },
  { name: "Tajikistan", dial_code: "+992", code: "TJ", flag: "🇹🇯" },
  { name: "Tanzania", dial_code: "+255", code: "TZ", flag: "🇹🇿" },
  { name: "Thailand", dial_code: "+66", code: "TH", flag: "🇹🇭" },
  { name: "Tunisia", dial_code: "+216", code: "TN", flag: "🇹🇳" },
  { name: "Turkey", dial_code: "+90", code: "TR", flag: "🇹🇷" },
  { name: "Uganda", dial_code: "+256", code: "UG", flag: "🇺🇬" },
  { name: "Ukraine", dial_code: "+380", code: "UA", flag: "🇺🇦" },
  { name: "United Arab Emirates", dial_code: "+971", code: "AE", flag: "🇦🇪" },
  { name: "United Kingdom", dial_code: "+44", code: "GB", flag: "🇬🇧" },
  { name: "United States", dial_code: "+1", code: "US", flag: "🇺🇸" },
  { name: "Uruguay", dial_code: "+598", code: "UY", flag: "🇺🇾" },
  { name: "Uzbekistan", dial_code: "+998", code: "UZ", flag: "🇺🇿" },
  { name: "Venezuela", dial_code: "+58", code: "VE", flag: "🇻🇪" },
  { name: "Vietnam", dial_code: "+84", code: "VN", flag: "🇻🇳" },
  { name: "Yemen", dial_code: "+967", code: "YE", flag: "🇾🇪" },
  { name: "Zimbabwe", dial_code: "+263", code: "ZW", flag: "🇿🇼" }
];
function CountryCodePicker({ value, onChange }) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const dropdownRef = useRef(null);
  const searchInputRef = useRef(null);
  const selectedCountry = COUNTRIES.find((c) => c.dial_code === value) || COUNTRIES.find((c) => c.code === "US") || COUNTRIES[0];
  const filteredCountries = COUNTRIES.filter(
    (country) => country.name.toLowerCase().includes(searchQuery.toLowerCase()) || country.dial_code.includes(searchQuery)
  );
  useEffect(() => {
    function handleClickOutside(event) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);
  useEffect(() => {
    if (isOpen && searchInputRef.current) {
      searchInputRef.current.focus();
    } else {
      setSearchQuery("");
    }
  }, [isOpen]);
  return /* @__PURE__ */ jsxs("div", { className: "relative z-10 shrink-0", ref: dropdownRef, children: [
    /* @__PURE__ */ jsxs(
      "button",
      {
        type: "button",
        onClick: () => setIsOpen(!isOpen),
        className: "flex items-center gap-1.5 h-full px-3 py-3 border-r border-gray-200 bg-gray-50/50 hover:bg-gray-100 transition-colors focus:outline-none focus:bg-gray-100",
        style: { borderTopLeftRadius: "0.75rem", borderBottomLeftRadius: "0.75rem" },
        children: [
          /* @__PURE__ */ jsx("span", { className: "text-base leading-none", children: selectedCountry.flag }),
          /* @__PURE__ */ jsx("span", { className: "text-sm font-semibold text-gray-700", children: selectedCountry.dial_code }),
          /* @__PURE__ */ jsx(ChevronDown, { className: `w-3.5 h-3.5 text-gray-400 transition-transform ${isOpen ? "rotate-180" : ""}` })
        ]
      }
    ),
    isOpen && /* @__PURE__ */ jsxs("div", { className: "absolute top-full left-0 mt-1.5 w-64 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden", style: { zIndex: 100 }, children: [
      /* @__PURE__ */ jsxs("div", { className: "p-2 border-b border-gray-100 relative", children: [
        /* @__PURE__ */ jsx(Search, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" }),
        /* @__PURE__ */ jsx(
          "input",
          {
            ref: searchInputRef,
            type: "text",
            placeholder: "Search country or code...",
            className: "w-full bg-gray-50 rounded-lg pl-8 pr-3 py-2 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-black",
            value: searchQuery,
            onChange: (e) => setSearchQuery(e.target.value)
          }
        )
      ] }),
      /* @__PURE__ */ jsx("ul", { className: "max-h-60 overflow-y-auto p-1", children: filteredCountries.length === 0 ? /* @__PURE__ */ jsx("li", { className: "px-3 py-4 text-xs text-center text-gray-500", children: "No countries found" }) : filteredCountries.map((country) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsxs(
        "button",
        {
          type: "button",
          onClick: () => {
            onChange(country.dial_code);
            setIsOpen(false);
          },
          className: `w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg hover:bg-gray-50 transition-colors ${value === country.dial_code ? "bg-gray-50 font-semibold text-black" : "text-gray-600"}`,
          children: [
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx("span", { className: "text-base leading-none", children: country.flag }),
              /* @__PURE__ */ jsx("span", { className: "truncate max-w-[120px] text-left", children: country.name })
            ] }),
            /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-400 font-medium", children: country.dial_code })
          ]
        }
      ) }, country.code)) })
    ] })
  ] });
}
function cn(...inputs) {
  return twMerge(clsx(inputs));
}
function formatPrice(price) {
  const num = typeof price === "string" ? parseFloat(price) : price;
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0
  }).format(num);
}
function SectionCard({ title, icon: Icon, children }) {
  return /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-2xl border border-gray-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] overflow-hidden", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 px-6 py-4 border-b border-gray-50", children: [
      /* @__PURE__ */ jsx("div", { className: "w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center", children: /* @__PURE__ */ jsx(Icon, { className: "w-4 h-4 text-gray-700" }) }),
      /* @__PURE__ */ jsx("h2", { className: "text-sm font-bold text-gray-900 uppercase tracking-wider", children: title })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "px-6 py-6", children })
  ] });
}
function Field$1({ label, ...props }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1.5", children: [
    /* @__PURE__ */ jsx("label", { className: "text-[11px] font-bold text-gray-500 uppercase tracking-wider", children: label }),
    /* @__PURE__ */ jsx(
      "input",
      {
        ...props,
        className: "w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all disabled:bg-gray-50 disabled:text-gray-400 read-only:bg-gray-100 read-only:text-gray-600 read-only:cursor-not-allowed read-only:focus:ring-0 read-only:focus:border-gray-200"
      }
    )
  ] });
}
function ProfileSection({ user, onSaved }) {
  const [name, setName] = useState(user?.name || "");
  const [email, setEmail] = useState(user?.email || "");
  const [phone, setPhone] = useState(user?.phone || "");
  const [saving, setSaving] = useState(false);
  useEffect(() => {
    setName(user?.name || "");
    setEmail(user?.email || "");
    setPhone(user?.phone || "");
  }, [user]);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState("");
  const handleSave = async () => {
    setSaving(true);
    setError("");
    try {
      await api.put("/api/account/profile", { name, email, phone });
      setSaved(true);
      onSaved();
      setTimeout(() => setSaved(false), 3e3);
    } catch (e) {
      setError(e.response?.data?.message || "Could not save. Try again.");
    } finally {
      setSaving(false);
    }
  };
  return /* @__PURE__ */ jsxs(SectionCard, { title: "Personal Details", icon: User, children: [
    /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 sm:grid-cols-2 gap-4", children: [
      /* @__PURE__ */ jsx(Field$1, { label: "Full Name", value: name, onChange: (e) => setName(e.target.value), placeholder: "Your name" }),
      /* @__PURE__ */ jsx(Field$1, { label: "Email Address", value: email, onChange: (e) => setEmail(e.target.value), type: "email", placeholder: "you@example.com" }),
      /* @__PURE__ */ jsx(Field$1, { label: "Phone Number", value: phone, onChange: (e) => setPhone(e.target.value), placeholder: "+91 98765 43210" })
    ] }),
    error && /* @__PURE__ */ jsxs("p", { className: "mt-3 text-xs text-red-500 flex items-center gap-1", children: [
      /* @__PURE__ */ jsx(AlertCircle, { className: "w-3.5 h-3.5" }),
      error
    ] }),
    /* @__PURE__ */ jsx("div", { className: "mt-6 flex items-center gap-3", children: /* @__PURE__ */ jsx(
      "button",
      {
        onClick: handleSave,
        disabled: saving,
        className: "flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50 active:scale-[0.98]",
        children: saved ? /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx(Check, { className: "w-3.5 h-3.5" }),
          " Saved!"
        ] }) : saving ? "Saving…" : "Save Changes"
      }
    ) })
  ] });
}
function SecuritySection() {
  const [current, setCurrent] = useState("");
  const [newPwd, setNewPwd] = useState("");
  const [confirm2, setConfirm] = useState("");
  const [showCur, setShowCur] = useState(false);
  const [showNew, setShowNew] = useState(false);
  const [saving, setSaving] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteStep, setDeleteStep] = useState(1);
  const [deleteOtp, setDeleteOtp] = useState("");
  const [sendingOtp, setSendingOtp] = useState(false);
  const [success, setSuccess] = useState("");
  const [error, setError] = useState("");
  const logout = useAuthStore((state) => state.logout);
  const handleChange = async () => {
    if (newPwd !== confirm2) {
      setError("Passwords do not match.");
      return;
    }
    if (newPwd.length < 8) {
      setError("Password must be at least 8 characters.");
      return;
    }
    setSaving(true);
    setError("");
    setSuccess("");
    try {
      await api.put("/api/account/password", { current_password: current, password: newPwd, password_confirmation: confirm2 });
      setSuccess("Password changed successfully!");
      setCurrent("");
      setNewPwd("");
      setConfirm("");
    } catch (e) {
      setError(e.response?.data?.message || "Could not change password.");
    } finally {
      setSaving(false);
    }
  };
  const handleRequestDeleteOtp = async () => {
    setSendingOtp(true);
    setError("");
    try {
      await api.post("/api/user/delete-account/otp");
      setDeleteStep(2);
    } catch (e) {
      setError(e.response?.data?.message || "Could not send OTP.");
    } finally {
      setSendingOtp(false);
    }
  };
  const handleDeleteAccount = async () => {
    if (!deleteOtp || deleteOtp.length !== 6) {
      setError("Please enter a valid 6-digit OTP.");
      return;
    }
    setDeleting(true);
    setError("");
    try {
      await api.delete("/api/user/delete-account", { data: { otp: deleteOtp } });
      setShowDeleteModal(false);
      alert("Your account has been successfully deleted/anonymized.");
      logout();
      window.location.href = "/";
    } catch (e) {
      setError(e.response?.data?.errors?.otp?.[0] || e.response?.data?.message || "Could not delete account.");
      setDeleting(false);
    }
  };
  return /* @__PURE__ */ jsxs(SectionCard, { title: "Security", icon: Lock, children: [
    /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 sm:grid-cols-2 gap-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1.5 sm:col-span-2", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
          /* @__PURE__ */ jsx("label", { className: "text-[11px] font-bold text-gray-500 uppercase tracking-wider", children: "Current Password" }),
          /* @__PURE__ */ jsx("span", { className: "text-[10px] text-gray-400 italic", children: "If you do not remember, logout and forget password." })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "relative", children: [
          /* @__PURE__ */ jsx(
            "input",
            {
              type: showCur ? "text" : "password",
              value: current,
              onChange: (e) => setCurrent(e.target.value),
              className: "w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all",
              placeholder: "Enter current password"
            }
          ),
          /* @__PURE__ */ jsx("button", { type: "button", onClick: () => setShowCur((v) => !v), className: "absolute right-3 top-1/2 -translate-y-1/2 text-gray-400", children: showCur ? /* @__PURE__ */ jsx(EyeOff, { className: "w-4 h-4" }) : /* @__PURE__ */ jsx(Eye, { className: "w-4 h-4" }) })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1.5", children: [
        /* @__PURE__ */ jsx("label", { className: "text-[11px] font-bold text-gray-500 uppercase tracking-wider", children: "New Password" }),
        /* @__PURE__ */ jsxs("div", { className: "relative", children: [
          /* @__PURE__ */ jsx(
            "input",
            {
              type: showNew ? "text" : "password",
              value: newPwd,
              onChange: (e) => setNewPwd(e.target.value),
              className: "w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all",
              placeholder: "Min 8 characters"
            }
          ),
          /* @__PURE__ */ jsx("button", { type: "button", onClick: () => setShowNew((v) => !v), className: "absolute right-3 top-1/2 -translate-y-1/2 text-gray-400", children: showNew ? /* @__PURE__ */ jsx(EyeOff, { className: "w-4 h-4" }) : /* @__PURE__ */ jsx(Eye, { className: "w-4 h-4" }) })
        ] })
      ] }),
      /* @__PURE__ */ jsx(Field$1, { label: "Confirm New Password", type: "password", value: confirm2, onChange: (e) => setConfirm(e.target.value), placeholder: "Repeat new password" })
    ] }),
    error && /* @__PURE__ */ jsxs("p", { className: "mt-3 text-xs text-red-500 flex items-center gap-1", children: [
      /* @__PURE__ */ jsx(AlertCircle, { className: "w-3.5 h-3.5" }),
      error
    ] }),
    success && /* @__PURE__ */ jsxs("p", { className: "mt-3 text-xs text-green-600 flex items-center gap-1", children: [
      /* @__PURE__ */ jsx(Check, { className: "w-3.5 h-3.5" }),
      success
    ] }),
    /* @__PURE__ */ jsx("div", { className: "mt-6", children: /* @__PURE__ */ jsx(
      "button",
      {
        onClick: handleChange,
        disabled: saving || deleting,
        className: "flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50 active:scale-[0.98]",
        children: saving ? "Updating…" : "Update Password"
      }
    ) }),
    /* @__PURE__ */ jsxs("div", { className: "mt-12 pt-6 border-t border-red-50", children: [
      /* @__PURE__ */ jsx("h3", { className: "text-sm font-bold text-red-600 uppercase tracking-wider mb-2", children: "Danger Zone" }),
      /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mb-4", children: "Permanently delete your account. If you have placed orders, your profile data will be anonymized to maintain order history. This action cannot be undone." }),
      /* @__PURE__ */ jsx(
        "button",
        {
          onClick: () => {
            setShowDeleteModal(true);
            setDeleteStep(1);
            setDeleteOtp("");
            setError("");
          },
          disabled: deleting,
          className: "flex items-center gap-2 bg-red-50 text-red-600 text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl border border-red-100 hover:bg-red-600 hover:text-white transition-all disabled:opacity-50 active:scale-[0.98]",
          children: deleting ? "Deleting..." : "Delete Account"
        }
      )
    ] }),
    showDeleteModal && /* @__PURE__ */ jsx("div", { className: "fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4", children: /* @__PURE__ */ jsx("div", { className: "bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-200", children: /* @__PURE__ */ jsxs("div", { className: "p-6", children: [
      /* @__PURE__ */ jsx("div", { className: "w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4", children: /* @__PURE__ */ jsx(AlertCircle, { className: "w-6 h-6 text-red-600" }) }),
      /* @__PURE__ */ jsx("h2", { className: "text-xl font-black text-gray-900 mb-2 tracking-tight", children: "Delete Account" }),
      deleteStep === 1 ? /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-6", children: "Are you absolutely sure you want to delete your account? This action cannot be undone. If you have pending or past orders, your profile data will be anonymized to preserve order history." }),
        error && /* @__PURE__ */ jsx("p", { className: "mb-4 text-xs text-red-500", children: error }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-3 justify-end", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: () => setShowDeleteModal(false),
              className: "px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition-colors",
              children: "Cancel"
            }
          ),
          /* @__PURE__ */ jsxs(
            "button",
            {
              onClick: handleRequestDeleteOtp,
              disabled: sendingOtp,
              className: "px-5 py-2.5 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center gap-2",
              children: [
                sendingOtp && /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" }),
                "Request OTP"
              ]
            }
          )
        ] })
      ] }) : /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4", children: "We've sent a 6-digit OTP to your registered WhatsApp/Email. Please enter it below to confirm deletion." }),
        /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
          /* @__PURE__ */ jsx("label", { className: "text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 block", children: "Enter OTP" }),
          /* @__PURE__ */ jsx(
            "input",
            {
              type: "text",
              inputMode: "numeric",
              pattern: "[0-9]*",
              value: deleteOtp,
              onChange: (e) => setDeleteOtp(e.target.value),
              maxLength: 6,
              className: "w-full border border-gray-200 rounded-xl px-4 py-2.5 text-center tracking-[0.5em] font-bold text-lg focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all",
              placeholder: "------"
            }
          )
        ] }),
        error && /* @__PURE__ */ jsx("p", { className: "mb-4 text-xs text-red-500", children: error }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-3 justify-end", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: () => setShowDeleteModal(false),
              className: "px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition-colors",
              children: "Cancel"
            }
          ),
          /* @__PURE__ */ jsxs(
            "button",
            {
              onClick: handleDeleteAccount,
              disabled: deleting || !deleteOtp || deleteOtp.length !== 6,
              className: "px-5 py-2.5 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center gap-2",
              children: [
                deleting && /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" }),
                "Confirm Deletion"
              ]
            }
          )
        ] })
      ] })
    ] }) }) })
  ] });
}
function AddressCard({ addr, onDelete, onSetDefault, onEdit }) {
  return /* @__PURE__ */ jsxs("div", { className: `relative p-4 rounded-xl border transition-all ${addr.is_default ? "border-gray-900 bg-gray-50" : "border-gray-200 bg-white"}`, children: [
    addr.is_default && /* @__PURE__ */ jsx("span", { className: "absolute top-3 right-3 text-[9px] font-black uppercase tracking-widest bg-gray-900 text-white px-2 py-0.5 rounded-full", children: "Default" }),
    /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: addr.name }),
    /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mt-0.5", children: addr.phone }),
    /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-600 mt-2 leading-relaxed", children: [
      addr.address_line1,
      addr.address_line2 ? `, ${addr.address_line2}` : "",
      /* @__PURE__ */ jsx("br", {}),
      addr.city,
      addr.district ? `, ${addr.district}` : "",
      ", ",
      addr.state,
      " – ",
      addr.zip_code,
      /* @__PURE__ */ jsx("br", {}),
      addr.country
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 mt-3", children: [
      !addr.is_default && /* @__PURE__ */ jsx("button", { onClick: () => onSetDefault(addr.id), className: "text-[11px] font-semibold text-gray-500 hover:text-gray-900 transition-colors underline underline-offset-2", children: "Set Default" }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 ml-auto", children: [
        /* @__PURE__ */ jsxs("button", { onClick: () => onEdit(addr), className: "text-[11px] font-semibold text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-0.5", children: [
          /* @__PURE__ */ jsx(Pencil, { className: "w-3 h-3" }),
          " Edit"
        ] }),
        /* @__PURE__ */ jsxs("button", { onClick: () => onDelete(addr.id), className: "text-[11px] font-semibold text-red-400 hover:text-red-600 transition-colors flex items-center gap-0.5", children: [
          /* @__PURE__ */ jsx(Trash2, { className: "w-3 h-3" }),
          " Remove"
        ] })
      ] })
    ] })
  ] });
}
function AddressesSection() {
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState({ name: "", phone: "", line1: "", line2: "", city: "", district: "", state: "", pincode: "", country: "India" });
  const [locked, setLocked] = useState({ city: false, district: false, state: false, country: true });
  const [activeCountryId, setActiveCountryId] = useState(null);
  const [countryCode, setCountryCode] = useState("+91");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  useEffect(() => {
    api.get("/api/localization/countries").then((res) => {
      if (res.data && res.data.length > 0) {
        setActiveCountryId(res.data[0].id);
        setForm((p) => ({ ...p, country: res.data[0].name }));
      }
    }).catch(() => {
    });
  }, []);
  useEffect(() => {
    const fetchPincode = async () => {
      if (form.pincode.length === 6 && activeCountryId) {
        try {
          const res = await api.get(`/api/localization/postal-code/${activeCountryId}/${form.pincode}`);
          if (res.data.found) {
            setForm((p) => ({
              ...p,
              city: res.data.city || p.city,
              district: res.data.district || p.district,
              state: res.data.state || p.state
            }));
            setLocked((p) => ({
              ...p,
              city: !!res.data.city,
              district: !!res.data.district,
              state: !!res.data.state
            }));
          } else {
            setLocked((p) => ({ ...p, city: false, district: false, state: false }));
          }
        } catch (e) {
          setLocked((p) => ({ ...p, city: false, district: false, state: false }));
        }
      } else if (form.pincode.length < 6) {
        setLocked((p) => ({ ...p, city: false, district: false, state: false }));
      }
    };
    fetchPincode();
  }, [form.pincode]);
  const load = async () => {
    try {
      const r = await api.get("/api/account/addresses");
      setAddresses(r.data);
    } catch {
      setAddresses([]);
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => {
    load();
  }, []);
  const handleAdd = async () => {
    setSaving(true);
    setError("");
    let finalPhone = form.phone.trim();
    if (finalPhone.startsWith(countryCode)) {
      finalPhone = finalPhone.substring(countryCode.length);
    }
    const rawCode = countryCode.replace("+", "");
    if (finalPhone.startsWith(rawCode)) {
      finalPhone = finalPhone.substring(rawCode.length);
    }
    finalPhone = `${countryCode}${finalPhone}`;
    try {
      if (editingId) {
        await api.put(`/api/account/addresses/${editingId}`, { ...form, phone: finalPhone });
      } else {
        await api.post("/api/account/addresses", { ...form, phone: finalPhone });
      }
      setShowForm(false);
      setEditingId(null);
      setForm({ name: "", phone: "", line1: "", line2: "", city: "", district: "", state: "", pincode: "", country: form.country });
      setCountryCode("+91");
      setLocked({ city: false, district: false, state: false, country: true });
      load();
    } catch (e) {
      setError(e.response?.data?.message || "Could not save address.");
    } finally {
      setSaving(false);
    }
  };
  const handleEdit = (addr) => {
    let ph = addr.phone || "";
    let cCode = "+91";
    const sortedCodes = [...COUNTRIES].sort((a, b) => b.dial_code.length - a.dial_code.length);
    let foundCode = false;
    if (ph.startsWith("+")) {
      for (const country of sortedCodes) {
        if (ph.startsWith(country.dial_code)) {
          cCode = country.dial_code;
          ph = ph.substring(country.dial_code.length);
          foundCode = true;
          break;
        }
      }
    }
    if (!foundCode && ph.length > 10) {
      for (const country of sortedCodes) {
        const codeWithoutPlus = country.dial_code.replace("+", "");
        if (ph.startsWith(codeWithoutPlus) && ph.length > codeWithoutPlus.length + 5) {
          cCode = country.dial_code;
          ph = ph.substring(codeWithoutPlus.length);
          break;
        }
      }
    }
    setCountryCode(cCode);
    setForm({
      name: addr.name,
      phone: ph,
      line1: addr.address_line1,
      line2: addr.address_line2 || "",
      city: addr.city,
      district: addr.district || "",
      state: addr.state,
      pincode: addr.zip_code,
      country: addr.country || form.country
    });
    setEditingId(addr.id);
    setShowForm(true);
  };
  const handleDelete = async (id) => {
    if (!confirm("Remove this address?")) return;
    try {
      await api.delete(`/api/account/addresses/${id}`);
      load();
    } catch {
    }
  };
  const handleDefault = async (id) => {
    try {
      await api.put(`/api/account/addresses/${id}/default`);
      load();
    } catch {
    }
  };
  const f = (k, v) => setForm((p) => ({ ...p, [k]: v }));
  return /* @__PURE__ */ jsxs(SectionCard, { title: "Saved Addresses", icon: MapPin, children: [
    loading ? /* @__PURE__ */ jsx("div", { className: "space-y-3", children: [1, 2].map((i) => /* @__PURE__ */ jsx("div", { className: "h-24 rounded-xl bg-gray-50 animate-pulse" }, i)) }) : addresses.length === 0 && !showForm ? /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-400 text-center py-6", children: "No saved addresses yet." }) : /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 sm:grid-cols-2 gap-3", children: addresses.map((a) => /* @__PURE__ */ jsx(AddressCard, { addr: a, onDelete: handleDelete, onSetDefault: handleDefault, onEdit: handleEdit }, a.id)) }),
    showForm && /* @__PURE__ */ jsxs("div", { className: "mt-4 p-4 rounded-xl border border-gray-200 bg-gray-50 space-y-3", children: [
      /* @__PURE__ */ jsx("p", { className: "text-xs font-bold text-gray-700 uppercase tracking-wider mb-2", children: editingId ? "Edit Address" : "New Address" }),
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 sm:grid-cols-2 gap-3", children: [
        /* @__PURE__ */ jsx(Field$1, { label: "Full Name", value: form.name, onChange: (e) => f("name", e.target.value), placeholder: "Recipient name" }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1.5", children: [
          /* @__PURE__ */ jsx("label", { className: "text-[11px] font-bold text-gray-500 uppercase tracking-wider", children: "Phone" }),
          /* @__PURE__ */ jsxs("div", { className: "flex bg-white rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-gray-900 focus-within:border-gray-900 border border-gray-200 transition-all", children: [
            /* @__PURE__ */ jsx(CountryCodePicker, { value: countryCode, onChange: setCountryCode }),
            /* @__PURE__ */ jsx(
              "input",
              {
                value: form.phone,
                onChange: (e) => f("phone", e.target.value),
                placeholder: "10-digit number",
                className: "flex-1 bg-transparent px-3.5 py-2.5 text-sm focus:outline-none"
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsx(Field$1, { label: "Address Line 1", value: form.line1, onChange: (e) => f("line1", e.target.value), placeholder: "House / Flat / Street" }),
        /* @__PURE__ */ jsx(Field$1, { label: "Address Line 2", value: form.line2, onChange: (e) => f("line2", e.target.value), placeholder: "Area / Landmark (optional)" }),
        /* @__PURE__ */ jsx(Field$1, { label: "Pincode", value: form.pincode, onChange: (e) => f("pincode", e.target.value), placeholder: "6-digit pincode" }),
        /* @__PURE__ */ jsx(Field$1, { label: "State", value: form.state, onChange: (e) => f("state", e.target.value), placeholder: "State", readOnly: locked.state }),
        /* @__PURE__ */ jsx(Field$1, { label: "City", value: form.city, onChange: (e) => f("city", e.target.value), placeholder: "City", readOnly: locked.city }),
        /* @__PURE__ */ jsx(Field$1, { label: "District", value: form.district, onChange: (e) => f("district", e.target.value), placeholder: "District (optional)", readOnly: locked.district }),
        /* @__PURE__ */ jsx(Field$1, { label: "Country", value: form.country, onChange: (e) => f("country", e.target.value), placeholder: "Country", readOnly: locked.country })
      ] }),
      error && /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 flex items-center gap-1", children: [
        /* @__PURE__ */ jsx(AlertCircle, { className: "w-3.5 h-3.5" }),
        error
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex gap-2 pt-1", children: [
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: handleAdd,
            disabled: saving,
            className: "flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-5 py-2 rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50",
            children: saving ? "Saving…" : "Save Address"
          }
        ),
        /* @__PURE__ */ jsx("button", { onClick: () => {
          setShowForm(false);
          setEditingId(null);
          setForm({ name: "", phone: "", line1: "", line2: "", city: "", district: "", state: "", pincode: "", country: form.country });
          setCountryCode("+91");
          setLocked({ city: false, district: false, state: false, country: true });
        }, className: "text-xs text-gray-500 font-medium hover:text-gray-800 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all", children: "Cancel" })
      ] })
    ] }),
    !showForm && /* @__PURE__ */ jsxs(
      "button",
      {
        onClick: () => setShowForm(true),
        className: "mt-4 flex items-center gap-2 text-xs font-bold text-gray-600 hover:text-gray-900 border border-dashed border-gray-300 hover:border-gray-500 px-4 py-2.5 rounded-xl w-full justify-center transition-all",
        children: [
          /* @__PURE__ */ jsx(Plus, { className: "w-4 h-4" }),
          " Add New Address"
        ]
      }
    )
  ] });
}
function AccountPage() {
  const { user, logout } = useAuthStore();
  const { openAuthModal: openAuthModal2 } = useUIStore();
  const [mounted, setMounted] = useState(false);
  const getInitialTab = () => {
    if (typeof window !== "undefined") {
      const params = new URLSearchParams(window.location.search);
      const t = params.get("tab");
      if (t && ["profile", "security", "addresses", "orders", "gift-cards"].includes(t)) {
        return t;
      }
    }
    return "profile";
  };
  const [activeTab, setActiveTab] = useState(getInitialTab);
  const [userData, setUserData] = useState(null);
  const [latestOrders, setLatestOrders] = useState([]);
  const [ordersLoading, setOrdersLoading] = useState(false);
  const [latestGiftCards, setLatestGiftCards] = useState([]);
  const [giftCardsLoading, setGiftCardsLoading] = useState(false);
  const handleTabChange = (tab) => {
    setActiveTab(tab);
    if (typeof window !== "undefined") {
      const url = new URL(window.location.href);
      url.searchParams.set("tab", tab);
      window.history.replaceState({}, "", url);
    }
  };
  useEffect(() => {
    setMounted(true);
    if (user) {
      api.get("/api/user").then((r) => setUserData(r.data)).catch(() => setUserData(user));
    }
  }, [user]);
  useEffect(() => {
    if (activeTab === "gift-cards" && latestGiftCards.length === 0 && user) {
      setGiftCardsLoading(true);
      api.get("/api/gift-cards/my-cards").then((r) => {
        setLatestGiftCards(Array.isArray(r.data) ? r.data.slice(0, 4) : []);
      }).catch(() => {
      }).finally(() => setGiftCardsLoading(false));
    }
    if (activeTab === "orders" && latestOrders.length === 0 && user) {
      setOrdersLoading(true);
      api.get("/api/my-orders?page=1").then((r) => {
        setLatestOrders(r.data.data.slice(0, 6));
      }).catch(() => {
      }).finally(() => setOrdersLoading(false));
    }
  }, [activeTab, user, latestOrders.length]);
  if (!mounted) return /* @__PURE__ */ jsx("div", { className: "min-h-[60vh]" });
  if (!user) {
    return /* @__PURE__ */ jsxs("div", { className: "min-h-[70vh] flex flex-col items-center justify-center px-4", children: [
      /* @__PURE__ */ jsx("div", { className: "w-20 h-20 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mb-6", children: /* @__PURE__ */ jsx(User, { className: "w-8 h-8 text-gray-300" }) }),
      /* @__PURE__ */ jsx("h1", { className: "text-2xl font-black text-gray-900 mb-2", children: "Sign in to your account" }),
      /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-8", children: "View your profile, orders, addresses and more." }),
      /* @__PURE__ */ jsxs(Link, { href: "/login", className: "flex items-center gap-2 bg-black text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-gray-800 transition-all shadow-lg shadow-black/10", children: [
        "Sign In ",
        /* @__PURE__ */ jsx(ArrowRight, { className: "w-4 h-4" })
      ] })
    ] });
  }
  const navTabs = [
    { id: "profile", label: "Profile", icon: User },
    { id: "security", label: "Security", icon: Shield },
    { id: "addresses", label: "Addresses", icon: MapPin },
    { id: "orders", label: "Orders", icon: Package },
    { id: "gift-cards", label: "Gift Cards", icon: Gift }
  ];
  return /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 py-12", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-10", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
        /* @__PURE__ */ jsx("div", { className: "w-14 h-14 rounded-2xl bg-gray-900 flex items-center justify-center text-white text-xl font-black shadow-lg", children: (userData?.name || user.name || "U")[0].toUpperCase() }),
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h1", { className: "text-2xl font-black text-gray-900 tracking-tight", children: userData?.name || user.name }),
          /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500", children: userData?.email || user.email })
        ] })
      ] }),
      /* @__PURE__ */ jsxs(
        "button",
        {
          onClick: logout,
          className: "flex items-center gap-2 text-xs font-bold text-gray-400 hover:text-red-500 border border-gray-200 hover:border-red-200 hover:bg-red-50 px-4 py-2 rounded-xl transition-all uppercase tracking-wider",
          children: [
            /* @__PURE__ */ jsx(LogOut, { className: "w-3.5 h-3.5" }),
            " Sign Out"
          ]
        }
      )
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row gap-6", children: [
      /* @__PURE__ */ jsx("aside", { className: "md:w-52 shrink-0", children: /* @__PURE__ */ jsx("nav", { className: "bg-white rounded-2xl border border-gray-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] overflow-hidden", children: navTabs.map((tab) => /* @__PURE__ */ jsxs(
        "button",
        {
          onClick: () => handleTabChange(tab.id),
          className: `w-full flex items-center gap-3 px-4 py-3.5 text-sm font-semibold transition-all border-l-2
                                    ${activeTab === tab.id ? "border-gray-900 bg-gray-50 text-gray-900" : "border-transparent text-gray-500 hover:text-gray-900 hover:bg-gray-50"}`,
          children: [
            /* @__PURE__ */ jsx(tab.icon, { className: "w-4 h-4 shrink-0" }),
            tab.label,
            activeTab === tab.id && /* @__PURE__ */ jsx(ChevronRight, { className: "w-3.5 h-3.5 ml-auto" })
          ]
        },
        tab.id
      )) }) }),
      /* @__PURE__ */ jsxs("main", { className: "flex-1 space-y-6 min-w-0", children: [
        activeTab === "profile" && /* @__PURE__ */ jsx(ProfileSection, { user: userData || user, onSaved: () => {
          useAuthStore.getState().checkAuth().then(() => api.get("/api/user").then((r) => setUserData(r.data)));
        } }),
        activeTab === "security" && /* @__PURE__ */ jsx(SecuritySection, {}),
        activeTab === "addresses" && /* @__PURE__ */ jsx(AddressesSection, {}),
        activeTab === "orders" && /* @__PURE__ */ jsx(SectionCard, { title: "Recent Orders", icon: Package, children: ordersLoading ? /* @__PURE__ */ jsx("div", { className: "space-y-3", children: [1, 2, 3].map((i) => /* @__PURE__ */ jsx("div", { className: "h-20 rounded-xl bg-gray-50 animate-pulse" }, i)) }) : latestOrders.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-8", children: [
          /* @__PURE__ */ jsx(Package, { className: "w-10 h-10 text-gray-200 mx-auto mb-3" }),
          /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4", children: "You haven't placed any orders yet." }),
          /* @__PURE__ */ jsxs(Link, { href: "/shop", className: "inline-flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all", children: [
            "Start Shopping ",
            /* @__PURE__ */ jsx(ArrowRight, { className: "w-3.5 h-3.5" })
          ] })
        ] }) : /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
          /* @__PURE__ */ jsx("div", { className: "divide-y divide-gray-50 border border-gray-100 rounded-xl overflow-hidden", children: latestOrders.map((order) => /* @__PURE__ */ jsxs("div", { className: "p-4 bg-white hover:bg-gray-50 transition-colors flex items-center justify-between gap-4", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("h3", { className: "text-sm font-bold text-gray-900 mb-1", children: [
                "#",
                order.order_number
              ] }),
              /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500", children: [
                new Date(order.created_at).toLocaleDateString("en-US", { day: "numeric", month: "short", year: "numeric" }),
                " • ",
                order.items_count,
                " ",
                order.items_count === 1 ? "Item" : "Items"
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4 text-right", children: [
              /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: formatPrice(order.total_amount) }),
                /* @__PURE__ */ jsx("p", { className: "text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-1", children: order.status })
              ] }),
              /* @__PURE__ */ jsx(Link, { href: `/orders/${order.uuid}`, className: "w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black transition-all shrink-0", children: /* @__PURE__ */ jsx(ChevronRight, { className: "w-4 h-4" }) })
            ] })
          ] }, order.uuid)) }),
          /* @__PURE__ */ jsx("div", { className: "pt-2 text-center", children: /* @__PURE__ */ jsxs(Link, { href: "/orders", className: "inline-flex items-center gap-2 bg-gray-900 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl hover:bg-black transition-all", children: [
            "View All Orders ",
            /* @__PURE__ */ jsx(ArrowRight, { className: "w-3.5 h-3.5" })
          ] }) })
        ] }) }),
        activeTab === "gift-cards" && /* @__PURE__ */ jsx(SectionCard, { title: "Gift Cards", icon: Gift, children: giftCardsLoading ? /* @__PURE__ */ jsx("div", { className: "space-y-3", children: [1, 2, 3].map((i) => /* @__PURE__ */ jsx("div", { className: "h-16 rounded-xl bg-gray-50 animate-pulse" }, i)) }) : latestGiftCards.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-8", children: [
          /* @__PURE__ */ jsx(Gift, { className: "w-10 h-10 text-gray-200 mx-auto mb-3" }),
          /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4", children: "Manage your gift cards, wallet balance, and send gifts." }),
          /* @__PURE__ */ jsxs("div", { className: "flex gap-3 justify-center", children: [
            /* @__PURE__ */ jsxs(Link, { href: "/gift-cards/my-cards", className: "inline-flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all", children: [
              /* @__PURE__ */ jsx(Wallet, { className: "w-3.5 h-3.5" }),
              " My Wallet"
            ] }),
            /* @__PURE__ */ jsxs(Link, { href: "/gift-cards", className: "inline-flex items-center gap-2 border border-gray-200 text-gray-600 text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:border-gray-400 transition-all", children: [
              /* @__PURE__ */ jsx(Gift, { className: "w-3.5 h-3.5" }),
              " Buy Cards"
            ] })
          ] })
        ] }) : /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
          /* @__PURE__ */ jsx("div", { className: "overflow-x-auto", children: /* @__PURE__ */ jsxs("table", { className: "w-full text-left border-collapse", children: [
            /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { className: "border-b border-gray-100", children: [
              /* @__PURE__ */ jsx("th", { className: "py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider", children: "Card Code" }),
              /* @__PURE__ */ jsx("th", { className: "py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider", children: "Type" }),
              /* @__PURE__ */ jsx("th", { className: "py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right", children: "Balance" })
            ] }) }),
            /* @__PURE__ */ jsx("tbody", { className: "divide-y divide-gray-50", children: latestGiftCards.map((card) => /* @__PURE__ */ jsxs("tr", { className: "hover:bg-gray-50/50 transition-colors", children: [
              /* @__PURE__ */ jsxs("td", { className: "py-4 px-4", children: [
                /* @__PURE__ */ jsx("p", { className: "font-mono text-sm font-bold text-gray-900", children: card.card_number }),
                /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mt-1", children: card.status_badge?.label || card.status })
              ] }),
              /* @__PURE__ */ jsx("td", { className: "py-4 px-4 text-sm text-gray-600 font-medium", children: card.template_name || "Direct Gift" }),
              /* @__PURE__ */ jsxs("td", { className: "py-4 px-4 text-right", children: [
                /* @__PURE__ */ jsxs("p", { className: "text-sm font-bold text-gray-900", children: [
                  "₹",
                  card.remaining_amount?.toLocaleString()
                ] }),
                card.used_amount > 0 && /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-400 mt-1", children: [
                  "₹",
                  card.used_amount?.toLocaleString(),
                  " Used"
                ] })
              ] })
            ] }, card.id)) })
          ] }) }),
          /* @__PURE__ */ jsx("div", { className: "flex justify-center border-t border-gray-100 pt-4", children: /* @__PURE__ */ jsxs(Link, { href: "/gift-cards/my-cards", className: "inline-flex items-center gap-2 text-xs font-bold text-black uppercase tracking-widest hover:text-gray-600 transition-all", children: [
            "View More ",
            /* @__PURE__ */ jsx(ArrowRight, { className: "w-3.5 h-3.5" })
          ] }) })
        ] }) })
      ] })
    ] })
  ] });
}
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: AccountPage
}, Symbol.toStringTag, { value: "Module" }));
function OrderDetailsPage({ uuid }) {
  const { user } = useAuthStore();
  const { settings } = usePage().props;
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [actionModal, setActionModal] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);
  const getActionFee = (action) => {
    const method = order?.payment_method === "cod" ? "cod" : "prepaid";
    const rules = settings?.shipping_rules?.[method];
    if (rules && rules[`${action}_fee`]) {
      const percent = parseFloat(rules[`${action}_fee`]);
      const baseValue = order?.items.reduce((acc, item) => acc + parseFloat(item.price || "0") * item.quantity, 0) || 0;
      return percent / 100 * baseValue;
    }
    return 0;
  };
  const handleActionSubmit = async () => {
    if (!actionModal || !order) return;
    setActionLoading(true);
    try {
      await api.post(`/api/my-orders/${order.uuid}/${actionModal}`);
      setActionModal(null);
      fetchOrder();
    } catch (err) {
      alert(err.response?.data?.message || "Failed to process request.");
    } finally {
      setActionLoading(false);
    }
  };
  useEffect(() => {
    if (!user) {
      router.visit("/login");
      return;
    }
    fetchOrder();
  }, [user, uuid]);
  const fetchOrder = async () => {
    try {
      const res = await api.get(`/api/my-orders/${uuid}`);
      setOrder(res.data);
    } catch (error) {
      console.error("Error fetching order:", error);
    } finally {
      setLoading(false);
    }
  };
  const getStatusStyle = (status) => {
    switch (status?.toLowerCase()) {
      case "pending":
        return "bg-amber-100 text-amber-800 border-amber-200";
      case "processing":
        return "bg-blue-100 text-blue-800 border-blue-200";
      case "shipped":
        return "bg-purple-100 text-purple-800 border-purple-200";
      case "delivered":
        return "bg-emerald-100 text-emerald-800 border-emerald-200";
      case "cancelled":
        return "bg-rose-100 text-rose-800 border-rose-200";
      default:
        return "bg-gray-100 text-gray-800 border-gray-200";
    }
  };
  if (loading) return /* @__PURE__ */ jsx("div", { className: "min-h-screen flex items-center justify-center bg-gray-50", children: /* @__PURE__ */ jsx("div", { className: "w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin" }) });
  if (!order) return /* @__PURE__ */ jsxs("div", { className: "min-h-screen flex flex-col items-center justify-center bg-gray-50", children: [
    /* @__PURE__ */ jsx("p", { className: "text-gray-500 mb-4 text-lg", children: "Order not found." }),
    /* @__PURE__ */ jsx(Link, { href: "/orders", className: "text-black font-semibold hover:underline", children: "Back to Orders" })
  ] });
  const safeSubtotal = order.items.reduce((acc, item) => acc + parseFloat(item.price || "0") * item.quantity, 0);
  const hasNonReturnableItems = order.items.some((item) => {
    return item.sku?.product?.is_returnable === 0 || item.sku?.product?.is_returnable === false;
  });
  return /* @__PURE__ */ jsx("div", { className: "min-h-screen bg-gray-50 py-12 md:py-20 font-sans", children: /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8", children: [
    /* @__PURE__ */ jsxs("div", { className: "mb-8", children: [
      /* @__PURE__ */ jsxs(Link, { href: "/orders", className: "inline-flex items-center text-sm text-gray-500 hover:text-black transition-colors mb-6 font-medium", children: [
        /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 mr-2", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M10 19l-7-7m0 0l7-7m-7 7h18" }) }),
        "Back to Orders"
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row md:items-end justify-between gap-4", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsxs("h1", { className: "text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight", children: [
            "Order #",
            order.order_number
          ] }),
          /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-500 mt-2 font-medium", children: [
            "Placed on ",
            new Date(order.created_at).toLocaleDateString("en-US", { day: "numeric", month: "long", year: "numeric" }),
            " at ",
            new Date(order.created_at).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" })
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { children: /* @__PURE__ */ jsx("span", { className: `inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border ${getStatusStyle(order.status)} shadow-sm`, children: order.status }) })
      ] })
    ] }),
    (order.status === "shipped" || order.status === "delivered" && order.tracking_number) && /* @__PURE__ */ jsxs("div", { className: "mb-8 bg-gradient-to-r from-gray-900 to-black rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-xl relative overflow-hidden", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl pointer-events-none" }),
      /* @__PURE__ */ jsxs("div", { className: "relative z-10", children: [
        /* @__PURE__ */ jsx("h3", { className: "text-lg font-bold text-white tracking-wide", children: "Tracking Information" }),
        /* @__PURE__ */ jsxs("p", { className: "text-gray-300 mt-2 text-sm", children: [
          order.courier_partner ? `${order.courier_partner} - ` : "",
          order.tracking_number ? /* @__PURE__ */ jsx("span", { className: "font-mono bg-white/10 px-2.5 py-1 rounded-md text-white border border-white/20", children: order.tracking_number }) : "Preparing tracking details..."
        ] })
      ] }),
      order.tracking_url && /* @__PURE__ */ jsx(
        "a",
        {
          href: order.tracking_url,
          target: "_blank",
          rel: "noopener noreferrer",
          className: "relative z-10 inline-flex items-center justify-center px-6 py-3 bg-white text-black text-sm font-bold rounded-xl hover:bg-gray-100 transition-transform hover:scale-105 active:scale-95 shadow-lg w-full sm:w-auto",
          children: "Track Package"
        }
      )
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-12 gap-8", children: [
      /* @__PURE__ */ jsx("div", { className: "lg:col-span-7 xl:col-span-8 space-y-6", children: /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4", children: "Items Ordered" }),
        /* @__PURE__ */ jsx("div", { className: "space-y-6", children: order.items.map((item) => {
          const imgUrl = item.image_url || item.sku?.product?.featured_image;
          return /* @__PURE__ */ jsxs("div", { className: "flex gap-4 sm:gap-6 group", children: [
            /* @__PURE__ */ jsx("div", { className: "w-24 h-32 sm:w-28 sm:h-36 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 relative shadow-sm transition-transform group-hover:scale-[1.02]", children: imgUrl ? /* @__PURE__ */ jsx(
              "img",
              {
                src: imgUrl,
                alt: item.product_name,
                className: "w-full h-full object-cover object-top"
              }
            ) : /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center h-full text-xs text-gray-400 font-medium bg-gray-100", children: "No Image" }) }),
            /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0 flex flex-col justify-center", children: [
              /* @__PURE__ */ jsx("div", { className: "flex justify-between items-start gap-4", children: /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsx("h3", { className: "text-base font-bold text-gray-900 leading-tight", children: item.product_name }),
                item.variant_name && /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mt-1 font-medium", children: item.variant_name }),
                item.delivery_date && /* @__PURE__ */ jsxs("p", { className: "text-xs text-green-700 mt-2 font-medium bg-green-50 inline-block px-2 py-1 rounded border border-green-100", children: [
                  "Delivered by: ",
                  /* @__PURE__ */ jsx("span", { className: "font-bold", children: item.delivery_date })
                ] })
              ] }) }),
              /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center gap-6", children: [
                /* @__PURE__ */ jsxs("div", { className: "text-sm font-medium bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 text-gray-700", children: [
                  "Qty: ",
                  item.quantity
                ] }),
                /* @__PURE__ */ jsx("div", { className: "text-base font-bold text-gray-900", children: formatPrice(parseFloat(item.price || "0") * item.quantity) })
              ] })
            ] })
          ] }, item.id);
        }) })
      ] }) }),
      /* @__PURE__ */ jsxs("div", { className: "lg:col-span-5 xl:col-span-4 space-y-6", children: [
        /* @__PURE__ */ jsx("div", { className: "bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: "p-6 sm:p-8", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4", children: "Order Summary" }),
          /* @__PURE__ */ jsxs("div", { className: "space-y-2.5 text-sm", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600", children: [
              /* @__PURE__ */ jsx("span", { children: "MRP Total" }),
              /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: formatPrice(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0)) })
            ] }),
            order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) > safeSubtotal && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
              /* @__PURE__ */ jsx("span", { children: "Discount on MRP" }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal)
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600 font-medium", children: [
              /* @__PURE__ */ jsx("span", { children: "Cart Subtotal" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(safeSubtotal) })
            ] }),
            parseFloat(order.coupon_discount_amount || "0") > 0 || parseFloat(order.prepaid_discount_amount || "0") > 0 || parseFloat(order.gift_card_discount_amount || "0") > 0 ? /* @__PURE__ */ jsxs(Fragment, { children: [
              parseFloat(order.coupon_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                /* @__PURE__ */ jsxs("span", { children: [
                  "Coupon Discount ",
                  order.coupon_code ? `(${order.coupon_code})` : ""
                ] }),
                /* @__PURE__ */ jsxs("span", { children: [
                  "−",
                  formatPrice(parseFloat(order.coupon_discount_amount || "0"))
                ] })
              ] }),
              parseFloat(order.prepaid_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                /* @__PURE__ */ jsx("span", { children: "Prepaid Discount" }),
                /* @__PURE__ */ jsxs("span", { children: [
                  "−",
                  formatPrice(parseFloat(order.prepaid_discount_amount || "0"))
                ] })
              ] }),
              parseFloat(order.gift_card_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                /* @__PURE__ */ jsx("span", { children: "Gift Card Applied" }),
                /* @__PURE__ */ jsxs("span", { children: [
                  "−",
                  formatPrice(parseFloat(order.gift_card_discount_amount || "0"))
                ] })
              ] })
            ] }) : parseFloat(order.discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
              /* @__PURE__ */ jsx("span", { children: "Coupon Discount" }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(parseFloat(order.discount_amount || "0"))
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
              /* @__PURE__ */ jsx("span", { children: "Shipping" }),
              /* @__PURE__ */ jsx("span", { children: parseFloat(order.shipping_amount || "0") === 0 ? "Free" : formatPrice(parseFloat(order.shipping_amount || "0")) })
            ] }),
            order.tax_breakdown && Object.keys(order.tax_breakdown).length > 0 ? Object.entries(order.tax_breakdown).map(([rate, amount]) => /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
              /* @__PURE__ */ jsxs("span", { children: [
                "Tax @ ",
                rate,
                "% ",
                Math.abs(parseFloat(order.total_amount || "0") + parseFloat(order.discount_amount || "0") - (safeSubtotal + parseFloat(order.shipping_amount || "0"))) < 0.1 ? "(Included)" : "(Excluded)"
              ] }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(amount) })
            ] }, rate)) : parseFloat(order.tax_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
              /* @__PURE__ */ jsxs("span", { children: [
                "Tax ",
                Math.abs(parseFloat(order.total_amount || "0") + parseFloat(order.discount_amount || "0") - (safeSubtotal + parseFloat(order.shipping_amount || "0"))) < 0.1 ? "(Included)" : "(Excluded)"
              ] }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(parseFloat(order.tax_amount || "0")) })
            ] }),
            order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal + parseFloat(order.discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600 font-medium pt-1", children: [
              /* @__PURE__ */ jsx("span", { children: "Total Savings" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal + parseFloat(order.discount_amount || "0")) })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "h-px bg-gray-100 my-1" }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between font-bold text-gray-900 text-base", children: [
              /* @__PURE__ */ jsx("span", { children: "Total" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(parseFloat(order.total_amount || "0")) })
            ] }),
            parseFloat(order.upfront_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-2 p-3 bg-orange-50 border border-orange-100 rounded-lg space-y-2", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-sm text-orange-800 font-semibold", children: [
                /* @__PURE__ */ jsx("span", { children: "Upfront (Non Refundable)" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice(parseFloat(order.upfront_amount || "0")) })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-xs text-orange-700 font-bold", children: [
                /* @__PURE__ */ jsx("span", { children: "Due on Delivery" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice(Math.max(0, parseFloat(order.total_amount || "0") - parseFloat(order.upfront_amount || "0"))) })
              ] })
            ] })
          ] })
        ] }) }),
        /* @__PURE__ */ jsx("div", { className: "bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: "p-6 sm:p-8", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-lg font-bold text-gray-900 mb-6", children: "Order Details" }),
          /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3", children: "Shipping Address" }),
              /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-800 leading-relaxed font-medium", children: [
                /* @__PURE__ */ jsx("span", { className: "block text-gray-900 font-bold mb-1", children: order.shipping_address.name }),
                order.shipping_address.address_line1,
                /* @__PURE__ */ jsx("br", {}),
                order.shipping_address.address_line2 && /* @__PURE__ */ jsxs(Fragment, { children: [
                  order.shipping_address.address_line2,
                  /* @__PURE__ */ jsx("br", {})
                ] }),
                order.shipping_address.city,
                ", ",
                order.shipping_address.state,
                " ",
                order.shipping_address.zip_code
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3", children: "Contact Details" }),
              /* @__PURE__ */ jsxs("div", { className: "text-sm text-gray-800 font-medium space-y-1.5", children: [
                /* @__PURE__ */ jsxs("p", { className: "flex items-center gap-2", children: [
                  /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 text-gray-400", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" }) }),
                  order.shipping_address.email
                ] }),
                /* @__PURE__ */ jsxs("p", { className: "flex items-center gap-2", children: [
                  /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 text-gray-400", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" }) }),
                  order.shipping_address.phone
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-4 pt-6 border-t border-gray-100", children: [
              /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsx("h3", { className: "text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2", children: "Payment Method" }),
                /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-900 font-bold", children: order.payment_method })
              ] }),
              /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsx("h3", { className: "text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-2", children: "Payment Status" }),
                /* @__PURE__ */ jsx("p", { className: `text-sm font-extrabold capitalize ${order.payment_status === "paid" ? "text-emerald-600" : "text-amber-600"}`, children: order.payment_status })
              ] })
            ] })
          ] })
        ] }) }),
        (order.status === "pending" || order.status === "processing" || order.status === "delivered") && /* @__PURE__ */ jsx("div", { className: "bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: "p-6 sm:p-8", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-lg font-bold text-gray-900 mb-4", children: "Order Actions" }),
          /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-3", children: [
            (order.status === "pending" || order.status === "processing") && /* @__PURE__ */ jsx("button", { onClick: () => setActionModal("cancel"), className: "w-full py-3 text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl transition-colors", children: "Cancel Order" }),
            order.status === "delivered" && /* @__PURE__ */ jsxs(Fragment, { children: [
              !hasNonReturnableItems ? /* @__PURE__ */ jsx("button", { onClick: () => setActionModal("return"), className: "w-full py-3 text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl transition-colors", children: "Return Order" }) : /* @__PURE__ */ jsx("div", { className: "bg-orange-50 border border-orange-100 p-3 rounded-xl mb-1", children: /* @__PURE__ */ jsx("p", { className: "text-xs text-orange-800 font-semibold text-center", children: "This order contains non-returnable items. You can only exchange this order." }) }),
              /* @__PURE__ */ jsx("button", { onClick: () => setActionModal("exchange"), className: "w-full py-3 text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors", children: "Exchange Order" })
            ] })
          ] })
        ] }) })
      ] })
    ] }),
    actionModal && /* @__PURE__ */ jsx("div", { className: "fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm", children: /* @__PURE__ */ jsx("div", { className: "bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-200", children: /* @__PURE__ */ jsxs("div", { className: "p-6", children: [
      /* @__PURE__ */ jsxs("h3", { className: "text-xl font-bold text-gray-900 capitalize mb-2", children: [
        actionModal,
        " Order"
      ] }),
      /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-600 mb-6", children: [
        "Are you sure you want to ",
        actionModal,
        " this order?"
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center text-sm font-medium", children: [
          /* @__PURE__ */ jsxs("span", { className: "text-gray-700 capitalize", children: [
            actionModal,
            " Fee"
          ] }),
          /* @__PURE__ */ jsx("span", { className: "text-gray-900", children: formatPrice(getActionFee(actionModal)) })
        ] }),
        order?.payment_method === "cod" && actionModal === "cancel" && (!settings?.shipping_rules?.cod?.upfront_refundable || settings?.shipping_rules?.cod?.upfront_refundable != "1") && /* @__PURE__ */ jsx("p", { className: "text-xs text-red-500 font-semibold mt-3 pt-3 border-t border-gray-200", children: "Note: The upfront shipping amount is non-refundable." }),
        order?.payment_method === "prepaid" && /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 font-medium mt-3 pt-3 border-t border-gray-200", children: "Note: The fee will be automatically deducted from your refund." })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => setActionModal(null),
            className: "flex-1 px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors",
            children: "No, Keep It"
          }
        ),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: handleActionSubmit,
            disabled: actionLoading,
            className: `flex-1 px-4 py-2.5 text-sm font-bold text-white bg-black hover:bg-gray-900 rounded-xl transition-colors flex items-center justify-center ${actionLoading ? "opacity-70 cursor-not-allowed" : ""}`,
            children: actionLoading ? /* @__PURE__ */ jsx("div", { className: "w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" }) : "Confirm"
          }
        )
      ] })
    ] }) }) })
  ] }) });
}
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: OrderDetailsPage
}, Symbol.toStringTag, { value: "Module" }));
function MyOrdersPage() {
  const { user } = useAuthStore();
  const { settings } = usePage().props;
  const [activeTab, setActiveTab] = useState("orders");
  const [orders, setOrders] = useState([]);
  const [orderMeta, setOrderMeta] = useState(null);
  const [orderPage, setOrderPage] = useState(1);
  const [giftCards, setGiftCards] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    if (user) {
      fetchData();
    } else {
      setLoading(false);
    }
  }, [user, orderPage]);
  const fetchData = async () => {
    setLoading(true);
    try {
      const [ordersRes, gcRes] = await Promise.all([
        api.get(`/api/my-orders?page=${orderPage}`),
        api.get("/api/gift-cards/my-cards")
      ]);
      setOrders(ordersRes.data.data);
      setOrderMeta(ordersRes.data);
      setGiftCards(gcRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };
  const getStatusColor = (status) => {
    switch (status.toLowerCase()) {
      case "pending":
        return "bg-amber-100 text-amber-700 border-amber-200";
      case "processing":
        return "bg-blue-100 text-blue-700 border-blue-200";
      case "shipped":
        return "bg-indigo-100 text-indigo-700 border-indigo-200";
      case "delivered":
        return "bg-emerald-100 text-emerald-700 border-emerald-200";
      case "cancelled":
        return "bg-rose-100 text-rose-700 border-rose-200";
      default:
        return "bg-slate-100 text-slate-700 border-slate-200";
    }
  };
  if (!user) {
    return /* @__PURE__ */ jsxs("div", { className: "min-h-[70vh] flex flex-col items-center justify-center p-4", children: [
      /* @__PURE__ */ jsx("div", { className: "w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-6", children: /* @__PURE__ */ jsx("svg", { className: "w-8 h-8 text-gray-400", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "1.5", d: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" }) }) }),
      /* @__PURE__ */ jsx("h2", { className: "text-2xl font-heading font-bold mb-2", children: "Login Required" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-500 mb-8 text-center max-w-xs", children: "Please sign in to your account to view and track your orders." }),
      /* @__PURE__ */ jsx(
        Link,
        {
          href: "/login",
          className: "bg-primary text-white px-10 py-3 rounded-full font-bold uppercase tracking-wider text-xs shadow-lg shadow-primary/20 hover:scale-105 transition-transform active:scale-95",
          children: "Sign In"
        }
      )
    ] });
  }
  if (loading && orders.length === 0) {
    return /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 py-20", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 mb-10", children: [
        /* @__PURE__ */ jsx("div", { className: "w-8 h-8 bg-gray-200 animate-pulse rounded-md" }),
        /* @__PURE__ */ jsx("div", { className: "w-48 h-8 bg-gray-200 animate-pulse rounded-md" })
      ] }),
      [1, 2, 3].map((i) => /* @__PURE__ */ jsx("div", { className: "mb-6 h-40 bg-white border border-gray-100 rounded-2xl animate-pulse" }, i))
    ] });
  }
  return /* @__PURE__ */ jsx("div", { className: "min-h-screen bg-[#fafafa]", children: /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 py-16", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12", children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsxs("h1", { className: "text-3xl md:text-5xl font-heading font-black tracking-tight text-gray-900", children: [
          "Account ",
          /* @__PURE__ */ jsx("span", { className: "text-primary", children: "History" })
        ] }),
        /* @__PURE__ */ jsx("p", { className: "text-gray-500 mt-2", children: "Manage your orders and digital assets." })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex p-1.5 bg-gray-100 rounded-2xl w-fit", children: [
        /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: () => setActiveTab("orders"),
            className: `px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all ${activeTab === "orders" ? "bg-white text-black shadow-sm" : "text-gray-500 hover:text-gray-700"}`,
            children: [
              "Orders (",
              orderMeta?.total || 0,
              ")"
            ]
          }
        ),
        /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: () => setActiveTab("giftcards"),
            className: `px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all ${activeTab === "giftcards" ? "bg-white text-black shadow-sm" : "text-gray-500 hover:text-gray-700"}`,
            children: [
              "Gift Cards (",
              giftCards.length,
              ")"
            ]
          }
        )
      ] })
    ] }),
    activeTab === "orders" ? /* @__PURE__ */ jsx(Fragment, { children: orders.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-20 bg-white border border-dashed border-gray-200 rounded-[2.5rem] shadow-sm", children: [
      /* @__PURE__ */ jsx("div", { className: "w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100", children: /* @__PURE__ */ jsx("svg", { className: "w-10 h-10 text-gray-300", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "1.5", d: "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" }) }) }),
      /* @__PURE__ */ jsx("h3", { className: "text-xl font-bold text-gray-900 mb-2", children: "No orders yet" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-500 mb-8", children: "Looks like you haven't made your first purchase." }),
      /* @__PURE__ */ jsxs(Link, { href: "/shop", className: "inline-flex items-center gap-2 bg-black text-white px-8 py-3 rounded-full font-bold uppercase tracking-wider text-xs hover:bg-gray-800 transition-all group", children: [
        "Start Shopping",
        /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 group-hover:translate-x-1 transition-transform", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M14 5l7 7m0 0l-7 7m7-7H3" }) })
      ] })
    ] }) : /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
      /* @__PURE__ */ jsx("div", { className: "grid gap-6", children: orders.map((order) => /* @__PURE__ */ jsxs("div", { className: "group bg-white border border-gray-100 rounded-[2rem] p-6 md:p-8 transition-all duration-300 hover:shadow-[0_20px_50px_rgb(0,0,0,0.04)] hover:-translate-y-1 relative overflow-hidden", children: [
        /* @__PURE__ */ jsx("div", { className: "absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary/5 to-transparent rounded-bl-[5rem] -mr-10 -mt-10 group-hover:scale-110 transition-transform duration-500" }),
        /* @__PURE__ */ jsxs("div", { className: "relative z-10 flex flex-col lg:flex-row gap-8 items-start lg:items-center", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex-1 w-full lg:w-auto", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between lg:justify-start gap-4 mb-4", children: [
              /* @__PURE__ */ jsx("span", { className: `px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border ${getStatusColor(order.status)}`, children: order.status }),
              /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-400 font-medium", children: new Date(order.created_at).toLocaleDateString("en-US", { day: "numeric", month: "short", year: "numeric" }) })
            ] }),
            /* @__PURE__ */ jsxs("h3", { className: "text-xl font-heading font-black text-gray-900 mb-1 group-hover:text-primary transition-colors", children: [
              "#",
              order.order_number
            ] }),
            /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-500", children: [
              "Total ",
              /* @__PURE__ */ jsx("span", { className: "text-gray-900 font-bold ml-1", children: formatPrice(order.total_amount) }),
              /* @__PURE__ */ jsx("span", { className: "mx-2 text-gray-200", children: "|" }),
              order.items_count,
              " ",
              order.items_count === 1 ? "Item" : "Items"
            ] })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2 lg:gap-3 w-full lg:w-auto overflow-hidden", children: order.items?.slice(0, 4).map((item, idx) => /* @__PURE__ */ jsxs("div", { title: item.delivery_date ? `Delivered by: ${item.delivery_date}` : item.product_name, className: "relative w-14 h-18 md:w-16 md:h-20 bg-gray-50 rounded-xl overflow-hidden border border-gray-100 shrink-0 shadow-sm transition-transform hover:scale-105", children: [
            item.image_url ? /* @__PURE__ */ jsx("img", { src: item.image_url, alt: item.product_name, fill: true, className: "object-cover", unoptimized: true }) : /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center h-full text-[10px] text-gray-300", children: "NA" }),
            idx === 3 && order.items.length > 4 && /* @__PURE__ */ jsxs("div", { className: "absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center text-white text-xs font-bold", children: [
              "+",
              order.items.length - 3
            ] })
          ] }, item.id)) }),
          /* @__PURE__ */ jsxs("div", { className: "w-full lg:w-auto pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-50 flex gap-2", children: [
            order.tracking_url && /* @__PURE__ */ jsxs(
              "a",
              {
                href: order.tracking_url,
                target: "_blank",
                rel: "noopener noreferrer",
                className: "flex items-center justify-center gap-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-4 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all border border-indigo-200",
                children: [
                  /* @__PURE__ */ jsx("svg", { className: "w-3.5 h-3.5", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" }) }),
                  "Track"
                ]
              }
            ),
            /* @__PURE__ */ jsxs(Link, { href: `/orders/${order.uuid}`, className: "flex items-center justify-center gap-2 bg-gray-50 text-gray-900 hover:bg-primary hover:text-white px-6 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all", children: [
              "Details",
              /* @__PURE__ */ jsx("svg", { className: "w-4 h-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 5l7 7-7 7" }) })
            ] })
          ] })
        ] })
      ] }, order.uuid)) }),
      orderMeta && orderMeta.last_page > 1 && /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center gap-2 mt-12", children: Array.from({ length: orderMeta.last_page }, (_, i) => i + 1).map((p) => /* @__PURE__ */ jsx(
        "button",
        {
          onClick: () => setOrderPage(p),
          className: `w-10 h-10 rounded-xl text-xs font-bold transition-all ${orderPage === p ? "bg-black text-white" : "bg-white border border-gray-100 text-gray-500 hover:border-gray-300"}`,
          children: p
        },
        p
      )) })
    ] }) }) : /* @__PURE__ */ jsx(Fragment, { children: giftCards.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-20 bg-white border border-dashed border-gray-200 rounded-[2.5rem]", children: [
      /* @__PURE__ */ jsx("div", { className: "w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6", children: /* @__PURE__ */ jsx("svg", { className: "w-10 h-10 text-gray-300", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "1.5", d: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" }) }) }),
      /* @__PURE__ */ jsx("h3", { className: "text-xl font-bold text-gray-900 mb-2", children: "No Gift Cards" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-500 mb-8", children: "You don't have any active gift cards in your wallet." }),
      /* @__PURE__ */ jsx(Link, { href: "/gift-cards", className: "inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-full font-bold uppercase tracking-wider text-xs", children: "Browse Gift Cards" })
    ] }) : /* @__PURE__ */ jsx("div", { className: "grid md:grid-cols-2 lg:grid-cols-3 gap-8", children: giftCards.map((card) => /* @__PURE__ */ jsx("div", { className: "relative group perspective", children: /* @__PURE__ */ jsxs("div", { className: "bg-gradient-to-br from-gray-900 to-black p-8 rounded-[2.5rem] text-white overflow-hidden shadow-2xl transition-all duration-500 group-hover:rotate-y-12", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20 blur-2xl" }),
      /* @__PURE__ */ jsx("div", { className: "absolute bottom-0 left-0 w-32 h-32 bg-primary/10 rounded-full -ml-16 -mb-16 blur-xl" }),
      /* @__PURE__ */ jsxs("div", { className: "relative z-10 flex flex-col h-full", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-12", children: [
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("p", { className: "text-[10px] font-black uppercase tracking-[0.3em] opacity-40 mb-1", children: "Redemption Code" }),
            /* @__PURE__ */ jsx("h4", { className: "text-lg font-mono font-bold tracking-wider", children: card.plain_code })
          ] }),
          /* @__PURE__ */ jsx("span", { className: `px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest ${card.status_badge.class}`, children: card.status_badge.label })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "mt-auto", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-end justify-between", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "text-[10px] font-black uppercase tracking-widest opacity-40 mb-1", children: "Balance" }),
              /* @__PURE__ */ jsx("p", { className: "text-3xl font-heading font-black", children: formatPrice(card.remaining_amount) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "text-right", children: [
              /* @__PURE__ */ jsx("p", { className: "text-[10px] font-black uppercase tracking-widest opacity-40 mb-1", children: "Original Value" }),
              /* @__PURE__ */ jsx("p", { className: "text-sm font-bold opacity-80", children: formatPrice(card.amount) })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "mt-8 pt-6 border-t border-white/10 flex justify-between items-center text-[10px] font-bold uppercase tracking-widest opacity-40", children: [
            /* @__PURE__ */ jsx("span", { children: card.card_number }),
            /* @__PURE__ */ jsxs("span", { children: [
              "Valid until ",
              card.expires_at ? new Date(card.expires_at).toLocaleDateString() : "Infinite"
            ] })
          ] })
        ] })
      ] })
    ] }) }, card.id)) }) })
  ] }) });
}
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: MyOrdersPage
}, Symbol.toStringTag, { value: "Module" }));
function LoginForm({ settings, onSuccess, onSwitchToRegister, isModal }) {
  const login = useAuthStore((state) => state.login);
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [countryCode, setCountryCode] = useState("+91");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const parse = (val) => {
    if (typeof val === "string") {
      try {
        return JSON.parse(val);
      } catch {
        return {};
      }
    }
    return val || {};
  };
  const authHeader = parse(settings.auth_header);
  const authFooter = parse(settings.auth_footer);
  const authAppearance = parse(settings.auth_appearance);
  const { auth } = usePage().props;
  const socialProviders = auth?.social_providers || [];
  const rawFields = parse(settings.auth_fields);
  const parseBool = (val, defaultVal) => {
    if (val === void 0 || val === null) return defaultVal;
    if (typeof val === "boolean") return val;
    if (typeof val === "string") return val === "1" || val.toLowerCase() === "true" || val.toLowerCase() === "on";
    if (typeof val === "number") return val === 1;
    return !!val;
  };
  const normalize = (field, defaultVisible = true, defaultRequired = false) => {
    if (typeof field === "object" && field !== null) {
      return {
        ...field,
        visible: parseBool(field.visible, defaultVisible),
        required: parseBool(field.required, defaultRequired),
        auth_type: field.auth_type || "data_entry"
      };
    }
    if (field === void 0) return { visible: defaultVisible, required: defaultRequired, auth_type: "data_entry" };
    return { visible: parseBool(field, defaultVisible), required: defaultRequired, auth_type: "data_entry" };
  };
  const authFields = {
    email: normalize(rawFields.email, true, true),
    phone: normalize(rawFields.phone, false, false)
  };
  const isEmailVisible = authFields.email.visible;
  const isPhoneVisible = authFields.phone.visible || authFields.phone.auth_type !== "data_entry";
  const isPhoneOtp = authFields.phone.auth_type === "whatsapp_otp" || authFields.phone.auth_type === "sms_otp";
  const defaultMethod = isPhoneVisible ? "phone" : "email";
  const [loginMethod, setLoginMethod] = useState(defaultMethod);
  const [phoneLoginType, setPhoneLoginType] = useState(isPhoneOtp ? "otp" : "password");
  const [isOtpSent, setIsOtpSent] = useState(false);
  const [otpArray, setOtpArray] = useState(["", "", "", "", "", ""]);
  const inputRefs = useRef([]);
  let fieldLabel = "Email Address";
  let fieldPlaceholder = "name@example.com";
  let FieldIcon = Mail;
  if (loginMethod === "phone") {
    fieldLabel = "Phone Number";
    fieldPlaceholder = "555 000 0000";
    FieldIcon = Phone;
  }
  const formatPhone = () => {
    let finalIdentifier = identifier.trim();
    if (finalIdentifier.startsWith("+")) {
      finalIdentifier = finalIdentifier.substring(1);
    }
    const rawCode = countryCode.replace("+", "");
    if (finalIdentifier.startsWith(rawCode)) {
      finalIdentifier = finalIdentifier.substring(rawCode.length);
    }
    return `${countryCode}${finalIdentifier}`;
  };
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const finalIdentifier = loginMethod === "phone" ? formatPhone() : identifier.trim();
      if (loginMethod === "phone" && phoneLoginType === "otp") {
        await api.post("/api/login/send-otp", { phone: finalIdentifier });
        setIsOtpSent(true);
      } else {
        const res = await api.post("/api/login", { identifier: finalIdentifier, password });
        login(res.data.access_token, res.data.user);
        if (onSuccess) onSuccess();
        else router.visit("/");
      }
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.error || err.response?.data?.errors?.identifier?.[0] || err.response?.data?.errors?.phone?.[0] || "Authentication failed");
    } finally {
      setLoading(false);
    }
  };
  const handleVerifyOtp = async (e) => {
    if (e) e.preventDefault();
    setLoading(true);
    setError("");
    const finalOtp = otpArray.join("");
    const finalSubmitPhone = formatPhone();
    try {
      const res = await api.post("/api/login/verify-otp", {
        phone: finalSubmitPhone,
        otp: finalOtp
      });
      login(res.data.access_token, res.data.user);
      if (onSuccess) onSuccess();
      else router.visit("/");
    } catch (err) {
      if (err.response?.data?.errors) {
        const firstError = Object.values(err.response.data.errors)[0];
        setError(firstError[0]);
      } else {
        setError(err.response?.data?.error || err.response?.data?.message || "Verification failed");
      }
    } finally {
      setLoading(false);
    }
  };
  const handleOtpChange = (index, value) => {
    const val = value.replace(/[^0-9]/g, "");
    if (!val && value !== "") return;
    const newOtp = [...otpArray];
    if (val.length > 1) {
      for (let i = 0; i < val.length && index + i < 6; i++) {
        newOtp[index + i] = val[i];
      }
      setOtpArray(newOtp);
      const nextEmptyIndex = newOtp.findIndex((v) => v === "");
      const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
      inputRefs.current[focusIndex]?.focus();
      return;
    }
    newOtp[index] = val;
    setOtpArray(newOtp);
    if (val !== "" && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };
  const handleOtpKeyDown = (index, e) => {
    if (e.key === "Backspace") {
      if (otpArray[index] === "" && index > 0) {
        const newOtp = [...otpArray];
        newOtp[index - 1] = "";
        setOtpArray(newOtp);
        inputRefs.current[index - 1]?.focus();
      } else if (otpArray[index] !== "") {
        const newOtp = [...otpArray];
        newOtp[index] = "";
        setOtpArray(newOtp);
      }
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "w-full", children: [
    /* @__PURE__ */ jsx("div", { className: `text-center mb-8 space-y-3 ${isModal ? "pt-2" : ""}`, children: (authHeader.order || ["image", "text"]).map((item) => /* @__PURE__ */ jsxs("div", { children: [
      item === "image" && authHeader.image && /* @__PURE__ */ jsx(
        "img",
        {
          src: authHeader.image,
          alt: "Logo",
          className: "mx-auto h-auto object-contain",
          style: { width: authHeader.image_width ? `${authHeader.image_width}px` : "100px" }
        }
      ),
      item === "text" && /* @__PURE__ */ jsx("h1", { className: "text-3xl font-bold tracking-tight text-gray-900", style: { fontFamily: "var(--font-heading)" }, children: authHeader.text || "Sign In" })
    ] }, item)) }),
    /* @__PURE__ */ jsxs(
      "div",
      {
        className: !isModal ? "bg-white p-8 rounded-2xl shadow-sm border border-gray-200" : "",
        style: !isModal ? {
          borderRadius: authAppearance.border_radius ? `${authAppearance.border_radius}px` : void 0,
          borderColor: authAppearance.border_color
        } : {},
        children: [
          error && /* @__PURE__ */ jsxs("div", { className: "mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3", children: [
            /* @__PURE__ */ jsx("div", { className: "w-1.5 h-1.5 rounded-full bg-red-500 mt-1.5 shrink-0" }),
            /* @__PURE__ */ jsxs("p", { className: "text-sm font-medium text-red-800 leading-tight", children: [
              error,
              error.toLowerCase().includes("no account found") && /* @__PURE__ */ jsx("span", { className: "block mt-2", children: /* @__PURE__ */ jsx(
                "button",
                {
                  type: "button",
                  onClick: () => onSwitchToRegister ? onSwitchToRegister() : router.visit("/register"),
                  className: "font-bold text-emerald-600 hover:text-emerald-700 underline decoration-2 underline-offset-2",
                  children: "Create account"
                }
              ) })
            ] })
          ] }),
          isOtpSent ? /* @__PURE__ */ jsxs("form", { onSubmit: handleVerifyOtp, className: "space-y-6", children: [
            /* @__PURE__ */ jsxs("div", { className: "text-center mb-6", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-lg font-bold text-gray-900 mb-2", children: "Verify your phone" }),
              /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-500", children: [
                "We've sent a 6-digit verification code to ",
                /* @__PURE__ */ jsx("span", { className: "font-semibold text-gray-900", children: formatPhone() }),
                "."
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
              /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1 block text-center", children: "Enter 6-Digit Code" }),
              /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center gap-3", children: otpArray.map((digit, index) => /* @__PURE__ */ jsx(
                "input",
                {
                  ref: (el) => inputRefs.current[index] = el,
                  type: "text",
                  inputMode: "numeric",
                  pattern: "[0-9]*",
                  maxLength: 6,
                  autoComplete: "one-time-code",
                  className: "w-12 h-14 bg-gray-50/50 border border-gray-200 rounded-xl text-center text-xl font-bold text-gray-900 focus:border-black focus:bg-white transition-all outline-none shadow-sm",
                  value: digit,
                  onPaste: (e) => {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData("text/plain").replace(/[^0-9]/g, "").slice(0, 6);
                    if (pastedData) {
                      const newOtp = [...otpArray];
                      for (let i = 0; i < pastedData.length; i++) {
                        newOtp[i] = pastedData[i];
                      }
                      setOtpArray(newOtp);
                      const nextEmptyIndex = newOtp.findIndex((v) => v === "");
                      const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
                      inputRefs.current[focusIndex]?.focus();
                    }
                  },
                  onChange: (e) => handleOtpChange(index, e.target.value),
                  onKeyDown: (e) => handleOtpKeyDown(index, e)
                },
                index
              )) })
            ] }),
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "submit",
                disabled: loading || otpArray.join("").length !== 6,
                className: "w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2",
                children: loading ? /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" }) : /* @__PURE__ */ jsx("span", { children: "Verify & Login" })
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "text-center", children: /* @__PURE__ */ jsx(
              "button",
              {
                type: "button",
                onClick: () => {
                  setIsOtpSent(false);
                  setOtpArray(["", "", "", "", "", ""]);
                },
                className: "text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors",
                children: "← Change Phone Number"
              }
            ) })
          ] }) : /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "space-y-4", children: [
            isEmailVisible && isPhoneVisible && /* @__PURE__ */ jsxs("div", { className: "flex p-1 bg-gray-100/80 rounded-xl mb-4 relative z-0", children: [
              /* @__PURE__ */ jsx(
                "button",
                {
                  type: "button",
                  onClick: () => {
                    setLoginMethod("phone");
                    setIdentifier("");
                  },
                  className: `flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all ${loginMethod === "phone" ? "bg-white text-black shadow-sm" : "text-gray-400 hover:text-gray-600"}`,
                  children: "Phone"
                }
              ),
              /* @__PURE__ */ jsx(
                "button",
                {
                  type: "button",
                  onClick: () => {
                    setLoginMethod("email");
                    setIdentifier("");
                  },
                  className: `flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all ${loginMethod === "email" ? "bg-white text-black shadow-sm" : "text-gray-400 hover:text-gray-600"}`,
                  children: "Email"
                }
              )
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
              /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: fieldLabel }),
              loginMethod === "phone" ? /* @__PURE__ */ jsxs("div", { className: "flex bg-gray-50/50 border border-gray-200 rounded-xl focus-within:border-black focus-within:bg-white transition-all group", children: [
                /* @__PURE__ */ jsx(CountryCodePicker, { value: countryCode, onChange: setCountryCode }),
                /* @__PURE__ */ jsxs("div", { className: "relative flex-1", children: [
                  /* @__PURE__ */ jsx(FieldIcon, { className: "absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "tel",
                      required: true,
                      name: "phone",
                      autoComplete: "tel",
                      placeholder: fieldPlaceholder,
                      maxLength: 10,
                      className: "w-full bg-transparent pl-9 pr-4 py-3 text-sm font-medium text-gray-900 focus:outline-none",
                      value: identifier,
                      onChange: (e) => {
                        const val = e.target.value.replace(/[^0-9]/g, "");
                        if (val.length <= 10) setIdentifier(val);
                      }
                    }
                  )
                ] })
              ] }) : /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                /* @__PURE__ */ jsx(FieldIcon, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    type: "email",
                    required: true,
                    name: "email",
                    autoComplete: "email",
                    placeholder: fieldPlaceholder,
                    className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                    value: identifier,
                    onChange: (e) => setIdentifier(e.target.value)
                  }
                )
              ] })
            ] }),
            (loginMethod === "email" || loginMethod === "phone" && phoneLoginType === "password") && /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between ml-1", children: [
                /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400", children: "Password" }),
                /* @__PURE__ */ jsx("button", { type: "button", className: "text-[10px] font-black uppercase tracking-widest text-gray-300 hover:text-black transition-colors", children: "Forgot?" })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                /* @__PURE__ */ jsx(Lock, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    type: "password",
                    required: true,
                    name: "password",
                    autoComplete: "current-password",
                    placeholder: "••••••••",
                    className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                    value: password,
                    onChange: (e) => setPassword(e.target.value)
                  }
                )
              ] })
            ] }),
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "submit",
                disabled: loading,
                className: "w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-2",
                children: loading ? /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" }) : /* @__PURE__ */ jsx("span", { children: loginMethod === "phone" && phoneLoginType === "otp" ? `Get OTP on ${authFields.phone.auth_type === "whatsapp_otp" ? "WhatsApp" : "SMS"}` : "Sign in now" })
              }
            ),
            loginMethod === "phone" && isPhoneOtp && /* @__PURE__ */ jsx("div", { className: "text-center pt-2", children: /* @__PURE__ */ jsx(
              "button",
              {
                type: "button",
                onClick: () => setPhoneLoginType((prev) => prev === "otp" ? "password" : "otp"),
                className: "text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-black transition-colors underline decoration-2 underline-offset-4",
                children: phoneLoginType === "otp" ? "Login with Password instead" : "Login with OTP instead"
              }
            ) })
          ] }),
          !isOtpSent && socialProviders.length > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-8 space-y-4", children: [
            /* @__PURE__ */ jsxs("div", { className: "relative", children: [
              /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center", children: /* @__PURE__ */ jsx("div", { className: "w-full border-t border-gray-100" }) }),
              /* @__PURE__ */ jsx("div", { className: "relative flex justify-center text-[10px] uppercase", children: /* @__PURE__ */ jsx("span", { className: "bg-white px-3 text-gray-300 font-black tracking-[0.2em] italic", children: "Social Auth" }) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap justify-center gap-3", children: [
              socialProviders.includes("google") && /* @__PURE__ */ jsxs("a", { href: "/auth/google/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/303108/google-icon-logo.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Google" })
              ] }),
              socialProviders.includes("snapchat") && /* @__PURE__ */ jsxs("a", { href: "/auth/snapchat/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://upload.wikimedia.org/wikipedia/en/c/c4/Snapchat_logo.svg", className: "w-4 h-4 object-contain", alt: "Snapchat" }),
                /* @__PURE__ */ jsx("span", { children: "Snapchat" })
              ] }),
              socialProviders.includes("apple") && /* @__PURE__ */ jsxs("a", { href: "/auth/apple/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/511330/apple-173.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Apple" })
              ] }),
              socialProviders.includes("facebook") && /* @__PURE__ */ jsxs("a", { href: "/auth/facebook/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/303114/facebook-3-logo.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Facebook" })
              ] }),
              socialProviders.includes("github") && /* @__PURE__ */ jsxs("a", { href: "/auth/github/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/512317/github-142.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "GitHub" })
              ] })
            ] })
          ] }),
          !isOtpSent && /* @__PURE__ */ jsx("div", { className: "mt-8 pt-6 border-t border-gray-50 text-center", children: /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-400 font-bold uppercase tracking-widest", children: [
            "New here?",
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "button",
                onClick: () => onSwitchToRegister ? onSwitchToRegister() : router.visit("/register"),
                className: "text-black font-black ml-2 hover:underline underline-offset-4 decoration-2",
                children: "Create account"
              }
            )
          ] }) })
        ]
      }
    ),
    !isModal && !isOtpSent && /* @__PURE__ */ jsx("div", { className: "mt-10 text-center space-y-4", children: (authFooter.order || ["image", "text"]).map((item) => /* @__PURE__ */ jsxs("div", { children: [
      item === "image" && authFooter.image && /* @__PURE__ */ jsx(
        "img",
        {
          src: authFooter.image,
          alt: "Footer Logo",
          className: "mx-auto h-auto object-contain",
          style: { width: authFooter.image_width ? `${authFooter.image_width}px` : "80px" }
        }
      ),
      item === "text" && /* @__PURE__ */ jsx("p", { className: "text-[10px] text-gray-300 font-black uppercase tracking-[0.2em] leading-relaxed italic", children: authFooter.text })
    ] }, item)) })
  ] });
}
function LoginPage() {
  const { settings } = usePage().props;
  const { openAuthModal: openAuthModal2 } = useUIStore();
  const parse = (val) => {
    if (typeof val === "string") {
      try {
        return JSON.parse(val);
      } catch {
        return {};
      }
    }
    return val || {};
  };
  const authAppearance = parse(settings.auth_appearance);
  const isModalMode = authAppearance.ux_mode === "modal";
  useEffect(() => {
    if (isModalMode) {
      router.visit("/");
      openAuthModal2("login");
    }
  }, [isModalMode, router, openAuthModal2]);
  if (isModalMode) return null;
  return /* @__PURE__ */ jsx("div", { className: "min-h-screen", children: /* @__PURE__ */ jsx("div", { className: "max-w-[1280px] mx-auto px-4 py-16 flex items-center justify-center", children: /* @__PURE__ */ jsx("div", { className: "max-w-[420px] w-full", children: /* @__PURE__ */ jsx(LoginForm, { settings }) }) }) });
}
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: LoginPage
}, Symbol.toStringTag, { value: "Module" }));
function RegisterForm({ settings, onSuccess, onSwitchToLogin, isModal }) {
  const login = useAuthStore((state) => state.login);
  const parse = (val) => {
    if (typeof val === "string") {
      try {
        return JSON.parse(val);
      } catch {
        return {};
      }
    }
    return val || {};
  };
  const rawFields = parse(settings.auth_fields);
  const { auth } = usePage().props;
  const socialProviders = auth?.social_providers || [];
  const authHeader = parse(settings.auth_header);
  const authFooter = parse(settings.auth_footer);
  const authAppearance = parse(settings.auth_appearance);
  const parseBool = (val, defaultVal) => {
    if (val === void 0 || val === null) return defaultVal;
    if (typeof val === "boolean") return val;
    if (typeof val === "string") return val === "1" || val.toLowerCase() === "true" || val.toLowerCase() === "on";
    if (typeof val === "number") return val === 1;
    return !!val;
  };
  const normalize = (field, defaultVisible = true, defaultRequired = false) => {
    if (typeof field === "object" && field !== null) {
      return {
        ...field,
        visible: parseBool(field.visible, defaultVisible),
        required: parseBool(field.required, defaultRequired),
        auth_type: field.auth_type || "data_entry"
      };
    }
    if (field === void 0) return { visible: defaultVisible, required: defaultRequired, auth_type: "data_entry" };
    return { visible: parseBool(field, defaultVisible), required: defaultRequired, auth_type: "data_entry" };
  };
  const authFields = {
    name: normalize(rawFields.name, true, true),
    email: normalize(rawFields.email, true, true),
    phone: normalize(rawFields.phone, false, false)
  };
  const isPhoneVisible = authFields.phone.visible || authFields.phone.auth_type !== "data_entry";
  const isNameVisible = authFields.name.visible;
  const isEmailVisible = authFields.email.visible;
  const [form, setForm] = useState({
    name: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
    has_consented_to_terms: false,
    has_consented_to_marketing: false
  });
  const [countryCode, setCountryCode] = useState("+91");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [isOtpSent, setIsOtpSent] = useState(false);
  const [otpArray, setOtpArray] = useState(Array(6).fill(""));
  const inputRefs = useRef([]);
  const [finalSubmitPhone, setFinalSubmitPhone] = useState("");
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      let finalPhone = form.phone.trim();
      if (finalPhone) {
        if (finalPhone.startsWith("+")) {
          finalPhone = finalPhone.substring(1);
        }
        const rawCode = countryCode.replace("+", "");
        if (finalPhone.startsWith(rawCode)) {
          finalPhone = finalPhone.substring(rawCode.length);
        }
        finalPhone = `${countryCode}${finalPhone}`;
      }
      const isOtpRequired = authFields.phone.auth_type === "whatsapp_otp" || authFields.phone.auth_type === "sms_otp";
      if (isOtpRequired && finalPhone) {
        const res = await api.post("/api/register/send-otp", { ...form, phone: finalPhone });
        if (res.data.requires_otp) {
          setIsOtpSent(true);
          setFinalSubmitPhone(finalPhone);
        }
      } else {
        const res = await api.post("/api/register", { ...form, phone: finalPhone });
        login(res.data.access_token, res.data.user);
        if (onSuccess) onSuccess();
        else router.visit("/");
      }
    } catch (err) {
      if (err.response?.data?.errors) {
        const firstError = Object.values(err.response.data.errors)[0];
        setError(firstError[0]);
      } else {
        setError(err.response?.data?.error || err.response?.data?.message || "Something went wrong");
      }
    } finally {
      setLoading(false);
    }
  };
  const handleVerifyOtp = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);
    const finalOtp = otpArray.join("");
    try {
      const res = await api.post("/api/register/verify-otp", {
        phone: finalSubmitPhone,
        otp: finalOtp
      });
      login(res.data.access_token, res.data.user);
      if (onSuccess) onSuccess();
      else router.visit("/");
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.error || "Invalid OTP. Please try again.");
    } finally {
      setLoading(false);
    }
  };
  const handleOtpChange = (index, value) => {
    const val = value.replace(/[^0-9]/g, "");
    if (!val && value !== "") return;
    const newOtp = [...otpArray];
    if (val.length > 1) {
      for (let i = 0; i < val.length && index + i < 6; i++) {
        newOtp[index + i] = val[i];
      }
      setOtpArray(newOtp);
      const nextEmptyIndex = newOtp.findIndex((v) => v === "");
      const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
      inputRefs.current[focusIndex]?.focus();
      return;
    }
    newOtp[index] = val;
    setOtpArray(newOtp);
    if (val !== "" && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };
  const handleOtpKeyDown = (index, e) => {
    if (e.key === "Backspace") {
      if (otpArray[index] === "" && index > 0) {
        const newOtp = [...otpArray];
        newOtp[index - 1] = "";
        setOtpArray(newOtp);
        inputRefs.current[index - 1]?.focus();
      } else if (otpArray[index] !== "") {
        const newOtp = [...otpArray];
        newOtp[index] = "";
        setOtpArray(newOtp);
      }
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "w-full", children: [
    /* @__PURE__ */ jsx("div", { className: `text-center mb-8 space-y-3 ${isModal ? "pt-2" : ""}`, children: (authHeader.order || ["image", "text"]).map((item) => /* @__PURE__ */ jsxs("div", { children: [
      item === "image" && authHeader.image && /* @__PURE__ */ jsx(
        "img",
        {
          src: authHeader.image,
          alt: "Logo",
          className: "mx-auto h-auto object-contain",
          style: { width: authHeader.image_width ? `${authHeader.image_width}px` : "100px" }
        }
      ),
      item === "text" && /* @__PURE__ */ jsx("h1", { className: "text-3xl font-bold tracking-tight text-gray-900", style: { fontFamily: "var(--font-heading)" }, children: authHeader.text || "Create Account" })
    ] }, item)) }),
    /* @__PURE__ */ jsxs(
      "div",
      {
        className: !isModal ? "bg-white p-8 rounded-2xl shadow-sm border border-gray-200" : "",
        style: !isModal ? {
          borderRadius: authAppearance.border_radius ? `${authAppearance.border_radius}px` : void 0,
          borderColor: authAppearance.border_color
        } : {},
        children: [
          error && /* @__PURE__ */ jsxs("div", { className: "mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3", children: [
            /* @__PURE__ */ jsx("div", { className: "w-1.5 h-1.5 rounded-full bg-red-500 mt-1.5 shrink-0" }),
            /* @__PURE__ */ jsx("p", { className: "text-sm font-medium text-red-800 leading-tight", children: error })
          ] }),
          isOtpSent ? /* @__PURE__ */ jsxs("form", { onSubmit: handleVerifyOtp, className: "space-y-6", children: [
            /* @__PURE__ */ jsxs("div", { className: "text-center mb-6", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-lg font-bold text-gray-900 mb-2", children: "Verify your phone" }),
              /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-500", children: [
                "We've sent a 6-digit verification code to ",
                /* @__PURE__ */ jsx("span", { className: "font-semibold text-gray-900", children: finalSubmitPhone }),
                "."
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
              /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1 block text-center", children: "Enter 6-Digit Code" }),
              /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center gap-3", children: otpArray.map((digit, index) => /* @__PURE__ */ jsx(
                "input",
                {
                  ref: (el) => inputRefs.current[index] = el,
                  type: "text",
                  inputMode: "numeric",
                  pattern: "[0-9]*",
                  maxLength: 6,
                  autoComplete: "one-time-code",
                  className: "w-12 h-14 bg-gray-50/50 border border-gray-200 rounded-xl text-center text-xl font-bold text-gray-900 focus:border-black focus:bg-white transition-all outline-none shadow-sm",
                  value: digit,
                  onPaste: (e) => {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData("text/plain").replace(/[^0-9]/g, "").slice(0, 6);
                    if (pastedData) {
                      const newOtp = [...otpArray];
                      for (let i = 0; i < pastedData.length; i++) {
                        newOtp[i] = pastedData[i];
                      }
                      setOtpArray(newOtp);
                      const nextEmptyIndex = newOtp.findIndex((v) => v === "");
                      const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
                      inputRefs.current[focusIndex]?.focus();
                    }
                  },
                  onChange: (e) => handleOtpChange(index, e.target.value),
                  onKeyDown: (e) => handleOtpKeyDown(index, e)
                },
                index
              )) })
            ] }),
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "submit",
                disabled: loading || otpArray.join("").length !== 6,
                className: "w-full py-3.5 px-4 bg-black text-white rounded-xl font-bold text-sm tracking-wide hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:shadow-lg hover:shadow-black/20",
                style: { backgroundColor: authAppearance.button_color || "var(--color-primary)" },
                children: loading ? /* @__PURE__ */ jsxs("span", { className: "flex items-center justify-center gap-2", children: [
                  /* @__PURE__ */ jsxs("svg", { className: "animate-spin h-5 w-5", viewBox: "0 0 24 24", children: [
                    /* @__PURE__ */ jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4", fill: "none" }),
                    /* @__PURE__ */ jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })
                  ] }),
                  "Verifying..."
                ] }) : "Verify & Register"
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "text-center", children: /* @__PURE__ */ jsx("button", { type: "button", onClick: () => setIsOtpSent(false), className: "text-xs font-semibold text-gray-500 hover:text-black", children: "Change phone number" }) })
          ] }) : /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "space-y-4", children: [
            isNameVisible && /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
              /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: "Full Name" }),
              /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                /* @__PURE__ */ jsx(User, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    type: "text",
                    name: "name",
                    autoComplete: "name",
                    required: authFields.name.required,
                    placeholder: "Enter your full name",
                    className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                    value: form.name,
                    onChange: (e) => setForm({ ...form, name: e.target.value })
                  }
                )
              ] })
            ] }),
            isEmailVisible && /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
              /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: "Email Address" }),
              /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                /* @__PURE__ */ jsx(Mail, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    type: "email",
                    name: "email",
                    autoComplete: "email",
                    required: authFields.email.required,
                    placeholder: "name@example.com",
                    className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                    value: form.email,
                    onChange: (e) => setForm({ ...form, email: e.target.value })
                  }
                )
              ] })
            ] }),
            isPhoneVisible && /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
              /* @__PURE__ */ jsxs("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: [
                "Phone",
                authFields.phone.auth_type === "sms_otp" && /* @__PURE__ */ jsx("span", { className: "text-[10px] text-gray-300 ml-2", children: "(SMS OTP)" }),
                authFields.phone.auth_type === "whatsapp_otp" && /* @__PURE__ */ jsx("span", { className: "text-[10px] text-gray-300 ml-2", children: "(WhatsApp OTP)" })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex bg-gray-50/50 border border-gray-200 rounded-xl focus-within:border-black focus-within:bg-white transition-all group", children: [
                /* @__PURE__ */ jsx(CountryCodePicker, { value: countryCode, onChange: setCountryCode }),
                /* @__PURE__ */ jsxs("div", { className: "relative flex-1", children: [
                  /* @__PURE__ */ jsx(Phone, { className: "absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" }),
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "tel",
                      name: "phone",
                      autoComplete: "tel",
                      required: authFields.phone.required,
                      placeholder: "555 000 0000",
                      className: "w-full bg-transparent pl-9 pr-4 py-3 text-sm font-medium text-gray-900 focus:outline-none",
                      value: form.phone,
                      onChange: (e) => setForm({ ...form, phone: e.target.value })
                    }
                  )
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-4", children: [
              /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
                /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: "Password" }),
                /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                  /* @__PURE__ */ jsx(Lock, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 group-focus-within:text-black transition-colors" }),
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "password",
                      required: true,
                      name: "password",
                      autoComplete: "new-password",
                      className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                      value: form.password,
                      onChange: (e) => setForm({ ...form, password: e.target.value })
                    }
                  )
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "space-y-1.5", children: [
                /* @__PURE__ */ jsx("label", { className: "text-xs font-bold uppercase tracking-widest text-gray-400 ml-1", children: "Confirm" }),
                /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
                  /* @__PURE__ */ jsx(Lock, { className: "absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 group-focus-within:text-black transition-colors" }),
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "password",
                      required: true,
                      name: "password_confirmation",
                      autoComplete: "new-password",
                      className: "w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none",
                      value: form.password_confirmation,
                      onChange: (e) => setForm({ ...form, password_confirmation: e.target.value })
                    }
                  )
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "pt-2 space-y-3", children: [
              /* @__PURE__ */ jsxs("label", { className: "flex items-start gap-3 cursor-pointer group", children: [
                /* @__PURE__ */ jsxs("div", { className: "relative flex items-start mt-0.5", children: [
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "checkbox",
                      required: true,
                      className: "peer w-4 h-4 border-2 border-gray-300 rounded appearance-none checked:bg-black checked:border-black transition-all cursor-pointer",
                      checked: form.has_consented_to_terms,
                      onChange: (e) => setForm({ ...form, has_consented_to_terms: e.target.checked })
                    }
                  ),
                  /* @__PURE__ */ jsx("svg", { className: "absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", strokeWidth: "3", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", d: "M5 13l4 4L19 7" }) })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "text-sm text-gray-600 group-hover:text-black transition-colors", children: [
                  "I agree to the ",
                  /* @__PURE__ */ jsx("a", { href: "/policy/terms-of-service", className: "font-bold underline decoration-gray-300 underline-offset-2 hover:decoration-black transition-colors", children: "Terms of Service" }),
                  " and ",
                  /* @__PURE__ */ jsx("a", { href: "/policy/privacy-policy", className: "font-bold underline decoration-gray-300 underline-offset-2 hover:decoration-black transition-colors", children: "Privacy Policy" }),
                  " ",
                  /* @__PURE__ */ jsx("span", { className: "text-red-500", children: "*" })
                ] })
              ] }),
              /* @__PURE__ */ jsxs("label", { className: "flex items-start gap-3 cursor-pointer group", children: [
                /* @__PURE__ */ jsxs("div", { className: "relative flex items-start mt-0.5", children: [
                  /* @__PURE__ */ jsx(
                    "input",
                    {
                      type: "checkbox",
                      className: "peer w-4 h-4 border-2 border-gray-300 rounded appearance-none checked:bg-black checked:border-black transition-all cursor-pointer",
                      checked: form.has_consented_to_marketing,
                      onChange: (e) => setForm({ ...form, has_consented_to_marketing: e.target.checked })
                    }
                  ),
                  /* @__PURE__ */ jsx("svg", { className: "absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", strokeWidth: "3", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", d: "M5 13l4 4L19 7" }) })
                ] }),
                /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-600 group-hover:text-black transition-colors", children: "I consent to receiving marketing emails and exclusive offers. (Optional)" })
              ] })
            ] }),
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "submit",
                disabled: loading,
                className: "w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-2",
                children: loading ? /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" }) : /* @__PURE__ */ jsx("span", { children: "Create account" })
              }
            )
          ] }),
          socialProviders.length > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-8 space-y-4", children: [
            /* @__PURE__ */ jsxs("div", { className: "relative", children: [
              /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center", children: /* @__PURE__ */ jsx("div", { className: "w-full border-t border-gray-100" }) }),
              /* @__PURE__ */ jsx("div", { className: "relative flex justify-center text-[10px] uppercase", children: /* @__PURE__ */ jsx("span", { className: "bg-white px-3 text-gray-300 font-black tracking-[0.2em] italic", children: "Social Auth" }) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap justify-center gap-3", children: [
              socialProviders.includes("google") && /* @__PURE__ */ jsxs("a", { href: "/auth/google/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/303108/google-icon-logo.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Google" })
              ] }),
              socialProviders.includes("snapchat") && /* @__PURE__ */ jsxs("a", { href: "/auth/snapchat/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://upload.wikimedia.org/wikipedia/en/c/c4/Snapchat_logo.svg", className: "w-4 h-4 object-contain", alt: "Snapchat" }),
                /* @__PURE__ */ jsx("span", { children: "Snapchat" })
              ] }),
              socialProviders.includes("apple") && /* @__PURE__ */ jsxs("a", { href: "/auth/apple/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/511330/apple-173.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Apple" })
              ] }),
              socialProviders.includes("facebook") && /* @__PURE__ */ jsxs("a", { href: "/auth/facebook/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/303114/facebook-3-logo.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "Facebook" })
              ] }),
              socialProviders.includes("github") && /* @__PURE__ */ jsxs("a", { href: "/auth/github/redirect", className: "w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors", children: [
                /* @__PURE__ */ jsx("img", { src: "https://www.svgrepo.com/show/512317/github-142.svg", className: "w-3.5 h-3.5" }),
                /* @__PURE__ */ jsx("span", { children: "GitHub" })
              ] })
            ] })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "mt-8 pt-6 border-t border-gray-50 text-center", children: /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-400 font-bold uppercase tracking-widest", children: [
            "Already have an account?",
            /* @__PURE__ */ jsx(
              "button",
              {
                type: "button",
                onClick: () => onSwitchToLogin ? onSwitchToLogin() : router.visit("/login"),
                className: "text-black font-black ml-2 hover:underline underline-offset-4 decoration-2",
                children: "Sign in"
              }
            )
          ] }) })
        ]
      }
    ),
    !isModal && /* @__PURE__ */ jsx("div", { className: "mt-10 text-center space-y-4", children: (authFooter.order || ["image", "text"]).map((item) => /* @__PURE__ */ jsxs("div", { children: [
      item === "image" && authFooter.image && /* @__PURE__ */ jsx(
        "img",
        {
          src: authFooter.image,
          alt: "Footer Logo",
          className: "mx-auto h-auto object-contain",
          style: { width: authFooter.image_width ? `${authFooter.image_width}px` : "80px" }
        }
      ),
      item === "text" && /* @__PURE__ */ jsx("p", { className: "text-[10px] text-gray-300 font-black uppercase tracking-[0.2em] leading-relaxed italic", children: authFooter.text })
    ] }, item)) })
  ] });
}
function RegisterPage() {
  const { settings } = usePage().props;
  const { openAuthModal: openAuthModal2 } = useUIStore();
  const parse = (val) => {
    if (typeof val === "string") {
      try {
        return JSON.parse(val);
      } catch {
        return {};
      }
    }
    return val || {};
  };
  const authAppearance = parse(settings.auth_appearance);
  const isModalMode = authAppearance.ux_mode === "modal";
  useEffect(() => {
    if (isModalMode) {
      router.visit("/");
      openAuthModal2("register");
    }
  }, [isModalMode, router, openAuthModal2]);
  if (isModalMode) return null;
  return /* @__PURE__ */ jsx("div", { className: "min-h-screen", children: /* @__PURE__ */ jsx("div", { className: "max-w-[1280px] mx-auto px-4 py-16 flex items-center justify-center", children: /* @__PURE__ */ jsx("div", { className: "max-w-[460px] w-full", children: /* @__PURE__ */ jsx(RegisterForm, { settings }) }) }) });
}
const __vite_glob_0_4 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: RegisterPage
}, Symbol.toStringTag, { value: "Module" }));
function CartRow$1({ item, update, remove }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex gap-4 py-5 border-b border-gray-100 last:border-0", children: [
    /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, className: "relative w-20 h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 block", children: item.image && /* @__PURE__ */ jsx("img", { src: item.image, alt: item.name, className: "w-full h-full object-cover" }) }),
    /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start gap-2", children: [
        /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, children: /* @__PURE__ */ jsx("h3", { className: "text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-snug line-clamp-2", children: item.name }) }),
        /* @__PURE__ */ jsx("button", { onClick: () => remove(item.skuId), className: "text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-0.5", children: /* @__PURE__ */ jsx(Trash2, { size: 15 }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-2 flex-wrap", children: [
        item.colorName && /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-1.5 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          /* @__PURE__ */ jsx("span", { className: "w-2.5 h-2.5 rounded-full border border-white shadow-sm", style: { backgroundColor: item.colorHex || "#aaa" } }),
          item.colorName
        ] }),
        item.sizeName && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          "Size ",
          item.sizeName
        ] }),
        item.deliveryDate && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full border border-green-100", children: [
          "Delivered by: ",
          item.deliveryDate
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-3", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 border border-gray-200 rounded-lg", children: [
          /* @__PURE__ */ jsx("button", { onClick: () => update(item.skuId, Math.max(1, item.quantity - 1)), className: "px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500", children: /* @__PURE__ */ jsx(Minus, { size: 12, strokeWidth: 3 }) }),
          /* @__PURE__ */ jsx("span", { className: "text-sm font-bold text-gray-900 w-7 text-center", children: item.quantity }),
          /* @__PURE__ */ jsx("button", { onClick: () => update(item.skuId, item.quantity + 1), className: "px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500", children: /* @__PURE__ */ jsx(Plus, { size: 12, strokeWidth: 3 }) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-end", children: [
          /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: formatPrice(item.price * item.quantity) }),
          item.mrp && item.mrp > item.price ? /* @__PURE__ */ jsx("p", { className: "text-[10px] text-gray-400 line-through mt-0.5", children: formatPrice(item.mrp * item.quantity) }) : null
        ] })
      ] })
    ] })
  ] });
}
function WishlistRow({ item, remove }) {
  const cart = useCartStore();
  const handleBuyNow = () => {
    if (!item.skuId) {
      window.location.href = `/product/${item.slug}`;
      return;
    }
    cart.addItem({
      skuId: item.skuId,
      productId: item.productId,
      name: item.name,
      slug: item.slug,
      variant: item.variant || "",
      price: item.price,
      mrp: item.mrp,
      image: item.image || "",
      quantity: 1,
      colorName: item.colorName,
      colorHex: item.colorHex,
      sizeName: item.sizeName,
      size: item.size,
      deliveryDate: item.deliveryDate
    });
    remove(item.productId);
  };
  return /* @__PURE__ */ jsxs("div", { className: "flex gap-4 py-5 border-b border-gray-100 last:border-0", children: [
    /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, className: "relative w-20 h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 block", children: item.image && /* @__PURE__ */ jsx("img", { src: item.image, alt: item.name, className: "w-full h-full object-cover" }) }),
    /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start gap-2", children: [
        /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, children: /* @__PURE__ */ jsx("h3", { className: "text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-snug line-clamp-2", children: item.name }) }),
        /* @__PURE__ */ jsx("button", { onClick: () => remove(item.productId), className: "text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-0.5", children: /* @__PURE__ */ jsx(Trash2, { size: 15 }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-2 flex-wrap", children: [
        item.colorName && /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-1.5 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          /* @__PURE__ */ jsx("span", { className: "w-2.5 h-2.5 rounded-full border border-white shadow-sm", style: { backgroundColor: item.colorHex || "#aaa" } }),
          item.colorName
        ] }),
        item.sizeName && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          "Size ",
          item.sizeName
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-3", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
          /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: formatPrice(item.price) }),
          item.mrp && item.mrp > item.price ? /* @__PURE__ */ jsx("p", { className: "text-[10px] text-gray-400 line-through mt-0.5", children: formatPrice(item.mrp) }) : null
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx("button", { onClick: handleBuyNow, className: "text-[10px] font-black uppercase tracking-widest text-white bg-black border border-black px-3 py-1.5 rounded-lg hover:bg-gray-800 transition-all", children: "Buy Now" }),
          /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, className: "text-gray-500 border border-gray-200 p-1.5 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all", title: "View Product", children: /* @__PURE__ */ jsx(Eye, { size: 14 }) })
        ] })
      ] })
    ] })
  ] });
}
function CartPage() {
  const { settings: sharedSettings } = usePage().props;
  sharedSettings?.store_name || "Store";
  const cart = useCartStore();
  const wishlist = useWishlistStore();
  const [mounted, setMounted] = useState(false);
  const [settings, setSettings] = useState(null);
  useEffect(() => {
    setMounted(true);
    api.get("/api/settings").then((r) => setSettings(r.data)).catch(() => {
    });
    api.get("/api/coupons/public").then(async (r) => {
      const magicCoupons = r.data.magic_coupons || [];
      const state = useCartStore.getState();
      if (!state.appliedCoupon && magicCoupons.length > 0 && state.items.length > 0) {
        for (const mc of magicCoupons) {
          try {
            const sub = state.items.reduce((s, i) => s + i.price * i.quantity, 0);
            const items = state.items.map((i) => ({ product_id: i.productId, price: i.price, original_price: i.price, quantity: i.quantity }));
            const applyRes = await api.post("/api/coupons/apply", { code: mc.code, cart: { subtotal: sub, items } });
            if (applyRes.data.success) {
              state.setAppliedCoupon({ code: applyRes.data.data.coupon.code, discountAmount: applyRes.data.data.discount_amount });
              break;
            }
          } catch (e) {
          }
        }
      }
    }).catch(() => {
    });
  }, []);
  if (!mounted) return /* @__PURE__ */ jsx("div", { className: "min-h-[60vh]" });
  if (cart.items.length === 0 && wishlist.items.length === 0) return /* @__PURE__ */ jsxs("div", { className: "max-w-sm mx-auto px-4 py-28 text-center", children: [
    /* @__PURE__ */ jsx(ShoppingBag, { className: "w-12 h-12 text-gray-200 mx-auto mb-5" }),
    /* @__PURE__ */ jsx("h1", { className: "text-xl font-bold text-gray-900 mb-2", children: "Your bag is empty" }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-400 mb-8", children: "Add items to get started." }),
    /* @__PURE__ */ jsxs(Link, { href: "/shop", className: "inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all", children: [
      "Browse Shop ",
      /* @__PURE__ */ jsx(ArrowRight, { size: 15 })
    ] })
  ] });
  const mrpTotal = cart.items.reduce((s, i) => s + Math.max(Number(i.mrp) || 0, Number(i.price)) * i.quantity, 0);
  const subtotal = cart.items.reduce((s, i) => s + Number(i.price) * i.quantity, 0);
  const mrpDiscount = mrpTotal > subtotal ? mrpTotal - subtotal : 0;
  const discount = cart.appliedCoupon?.discountAmount || 0;
  let taxAmount = 0;
  const isTaxEnabled = settings?.is_tax_enabled == "1";
  const taxLabel = settings?.tax_label || "Tax";
  const taxInclusive = settings?.tax_inclusion === "include";
  const taxBreakdown = {};
  let trueSubtotal = 0;
  cart.items.forEach((item) => {
    const itemTotal = item.price * item.quantity;
    let itemTax = 0;
    let trueItemTotal = itemTotal;
    if (isTaxEnabled) {
      let taxRate = 0;
      if (item.tax_class && settings?.taxes) {
        const t = settings.taxes.find((t2) => t2.id === item.tax_class);
        if (t) taxRate = parseFloat(t.rate);
      }
      if (taxRate > 0) {
        if (taxInclusive) {
          trueItemTotal = itemTotal / (1 + taxRate / 100);
          itemTax = itemTotal - trueItemTotal;
        } else {
          trueItemTotal = itemTotal;
          itemTax = itemTotal * (taxRate / 100);
        }
        const rateKey = taxRate.toString();
        taxBreakdown[rateKey] = (taxBreakdown[rateKey] || 0) + itemTax;
        taxAmount += itemTax;
      }
    }
    trueSubtotal += trueItemTotal;
  });
  const total = Math.max(0, (taxInclusive ? subtotal : trueSubtotal + taxAmount) - discount);
  return /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16", children: [
    /* @__PURE__ */ jsx(Head, { title: "Cart" }),
    /* @__PURE__ */ jsxs("div", { className: "mb-10", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold text-gray-900", children: "Your Cart" }),
      /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-400 mt-1", children: [
        cart.items.length,
        " ",
        cart.items.length === 1 ? "item" : "items",
        " in your bag",
        wishlist.items.length > 0 && ` • ${wishlist.items.length} saved for later`
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col lg:flex-row gap-10 items-start", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex-1 space-y-10 min-w-0", children: [
        cart.items.length > 0 && /* @__PURE__ */ jsx("section", { children: /* @__PURE__ */ jsx("div", { className: "bg-white border-y sm:border sm:rounded-2xl px-4 sm:px-5 divide-y divide-gray-50 -mx-4 sm:mx-0", children: cart.items.map((item) => /* @__PURE__ */ jsx(CartRow$1, { item, update: cart.updateQuantity, remove: cart.removeItem }, item.skuId)) }) }),
        wishlist.items.length > 0 && /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsxs("h2", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5 mt-2", children: [
            /* @__PURE__ */ jsx(Heart, { size: 12 }),
            " Saved for Later / Wishlist"
          ] }),
          /* @__PURE__ */ jsx("div", { className: "bg-white border-y sm:border sm:rounded-2xl px-4 sm:px-5 divide-y divide-gray-50 -mx-4 sm:mx-0", children: wishlist.items.map((item) => /* @__PURE__ */ jsx(WishlistRow, { item, remove: wishlist.removeItem }, item.productId)) })
        ] })
      ] }),
      cart.items.length > 0 && /* @__PURE__ */ jsx("div", { className: "w-full lg:w-[340px] shrink-0 space-y-4", children: /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-5", children: [
        /* @__PURE__ */ jsx("p", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4", children: "Order Summary" }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2.5 text-sm", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600", children: [
            /* @__PURE__ */ jsx("span", { children: "MRP Total" }),
            /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: formatPrice(mrpTotal) })
          ] }),
          mrpDiscount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
            /* @__PURE__ */ jsx("span", { children: "Discount on MRP" }),
            /* @__PURE__ */ jsxs("span", { children: [
              "−",
              formatPrice(mrpDiscount)
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600 font-medium", children: [
            /* @__PURE__ */ jsx("span", { children: "Cart Subtotal" }),
            /* @__PURE__ */ jsx("span", { children: formatPrice(subtotal) })
          ] }),
          cart.appliedCoupon && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600 font-medium", children: [
            /* @__PURE__ */ jsxs("span", { children: [
              "Coupon (",
              cart.appliedCoupon.code,
              ")"
            ] }),
            /* @__PURE__ */ jsxs("span", { children: [
              "−",
              formatPrice(discount)
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
            /* @__PURE__ */ jsx("span", { children: "Shipping" }),
            /* @__PURE__ */ jsx("span", { children: "Calculated at checkout" })
          ] }),
          isTaxEnabled && settings?.show_tax_in_cart_checkout !== "0" && /* @__PURE__ */ jsx(Fragment, { children: Object.entries(taxBreakdown).map(([rate, amount]) => /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
            /* @__PURE__ */ jsxs("span", { children: [
              taxLabel,
              " @ ",
              rate,
              "% ",
              taxInclusive ? "(Included)" : "(Excluded)"
            ] }),
            /* @__PURE__ */ jsx("span", { children: formatPrice(amount) })
          ] }, rate)) }),
          /* @__PURE__ */ jsx("div", { className: "h-px bg-gray-100 my-1" }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between font-bold text-gray-900 text-base", children: [
            /* @__PURE__ */ jsx("span", { children: "Estimated Total" }),
            /* @__PURE__ */ jsx("span", { children: formatPrice(total) })
          ] })
        ] }),
        /* @__PURE__ */ jsxs(Link, { href: "/checkout", className: "mt-5 w-full bg-gray-900 text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-black transition-all active:scale-[0.98] flex items-center justify-center gap-2", children: [
          "Proceed to Checkout",
          /* @__PURE__ */ jsx(ArrowRight, { size: 16 })
        ] })
      ] }) })
    ] })
  ] });
}
const __vite_glob_0_5 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: CartPage
}, Symbol.toStringTag, { value: "Module" }));
const gtag = (...args) => {
  if (typeof window !== "undefined" && typeof window.gtag === "function") {
    window.gtag(...args);
  } else if (typeof window !== "undefined" && window.dataLayer) {
    window.dataLayer.push(args);
  }
};
const generateEventId = () => {
  if (typeof crypto !== "undefined" && crypto.randomUUID) {
    return crypto.randomUUID();
  }
  return "evt_" + Date.now() + "_" + Math.floor(Math.random() * 1e6);
};
const fbq = (action, eventName, eventData = {}) => {
  const eventId = generateEventId();
  if (typeof window !== "undefined" && typeof window.fbq === "function") {
    window.fbq(action, eventName, eventData, { eventID: eventId });
  }
  if (typeof window !== "undefined" && action === "track") {
    fetch("/api/tracking/meta-event", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({
        event_name: eventName,
        event_id: eventId,
        event_source_url: window.location.href,
        custom_data: eventData
      })
    }).catch((err) => console.error("CAPI Event Error:", err));
  }
};
const snaptr = (action, eventName, eventData = {}) => {
  const eventId = generateEventId();
  if (typeof window !== "undefined" && typeof window.snaptr === "function") {
    window.snaptr(action, eventName, { ...eventData, client_dedup_id: eventId });
  }
  if (typeof window !== "undefined" && action === "track") {
    const scid = document.cookie.split("; ").find((row) => row.startsWith("_scid="))?.split("=")[1];
    fetch("/api/tracking/snapchat-event", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({
        event_name: eventName,
        event_id: eventId,
        event_source_url: window.location.href,
        custom_data: eventData,
        sc_cookie1: scid
      })
    }).catch((err) => console.error("Snapchat CAPI Event Error:", err));
  }
};
const trackViewContent = (product) => {
  const value = parseFloat(product.price);
  gtag("event", "view_item", {
    currency: "INR",
    value,
    items: [
      {
        item_id: product.id,
        item_name: product.title,
        price: value,
        quantity: 1
      }
    ]
  });
  fbq("track", "ViewContent", {
    content_ids: [product.id],
    content_type: "product_group",
    value,
    currency: "INR"
  });
  snaptr("track", "VIEW_CONTENT", {
    item_ids: [product.id],
    item_category: "product",
    price: value,
    currency: "INR"
  });
};
const trackAddToCart = (product, quantity = 1) => {
  const value = parseFloat(product.price) * quantity;
  gtag("event", "add_to_cart", {
    currency: "INR",
    value,
    items: [
      {
        item_id: product.id,
        item_name: product.title,
        price: parseFloat(product.price),
        quantity
      }
    ]
  });
  fbq("track", "AddToCart", {
    content_ids: [product.id],
    content_type: "product_group",
    value,
    currency: "INR"
  });
  snaptr("track", "ADD_CART", {
    item_ids: [product.id],
    price: value,
    currency: "INR"
  });
};
const trackAddToWishlist = (product) => {
  const value = parseFloat(product.price);
  gtag("event", "add_to_wishlist", {
    currency: "INR",
    value,
    items: [
      {
        item_id: product.id,
        item_name: product.title,
        price: parseFloat(product.price),
        quantity: 1
      }
    ]
  });
  fbq("track", "AddToWishlist", {
    content_ids: [product.id],
    content_type: "product_group",
    value,
    currency: "INR"
  });
  snaptr("track", "SAVE", {
    item_ids: [product.id],
    price: value,
    currency: "INR"
  });
};
const trackInitiateCheckout = (cartTotal, items) => {
  const gaItems = items.map((item) => ({
    item_id: item.product_id || item.id,
    item_name: item.title || item.name,
    price: parseFloat(item.price),
    quantity: item.quantity
  }));
  gtag("event", "begin_checkout", {
    currency: "INR",
    value: cartTotal,
    items: gaItems
  });
  fbq("track", "InitiateCheckout", {
    content_ids: gaItems.map((i) => i.item_id),
    content_type: "product_group",
    value: cartTotal,
    num_items: items.length,
    currency: "INR"
  });
  snaptr("track", "START_CHECKOUT", {
    item_ids: gaItems.map((i) => i.item_id),
    price: cartTotal,
    currency: "INR",
    number_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
  });
};
const trackPurchase = (transactionId, total, items) => {
  const gaItems = items.map((item) => ({
    item_id: item.product_id || item.id,
    item_name: item.title || item.name,
    price: parseFloat(item.price),
    quantity: item.quantity
  }));
  gtag("event", "purchase", {
    transaction_id: transactionId,
    value: total,
    currency: "INR",
    items: gaItems
  });
  fbq("track", "Purchase", {
    content_ids: gaItems.map((i) => i.item_id),
    content_type: "product_group",
    value: total,
    currency: "INR"
  });
  snaptr("track", "PURCHASE", {
    transaction_id: transactionId,
    item_ids: gaItems.map((i) => i.item_id),
    price: total,
    currency: "INR",
    number_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
  });
};
function ProductCard({ product, activeCategory, onRemove }) {
  const { settings } = usePage().props;
  const { addItem: addToWishlist, removeItem: removeFromWishlist, isInWishlist } = useWishlistStore();
  const wishlisted = isInWishlist(product.id);
  const { openQuickView } = useUIStore();
  const cart = useCartStore();
  const [loadingAction, setLoadingAction] = useState(null);
  const handleAction = async (e, action) => {
    e.preventDefault();
    e.stopPropagation();
    setLoadingAction(action);
    try {
      const res = await api.get(`/api/products/${product.slug}`);
      const data = res.data.data || res.data;
      if (data.variants && data.variants.length > 1) {
        openQuickView(data, action);
      } else {
        const v = data.variants?.[0];
        const colorAttr = v?.attributes?.find((a) => a.name === "Color");
        const sizeAttr = v?.attributes?.find((a) => a.name === "Size");
        const colorImg = colorAttr ? data.images?.find((img) => img.color_id?.toString() === colorAttr.id?.toString()) : null;
        cart.addItem({
          skuId: v?.id || data.id,
          productId: data.id,
          name: data.name,
          slug: data.slug,
          variant: v ? v.code : "",
          price: v?.price || data.price,
          mrp: v?.mrp || data.mrp,
          image: colorImg?.url || data.image || data.images?.[0]?.url || "",
          quantity: 1,
          colorName: colorAttr?.value || void 0,
          colorHex: colorAttr?.meta || void 0,
          sizeName: sizeAttr?.value || void 0,
          size: sizeAttr?.value || void 0,
          deliveryDate: data.delivery_timeline?.formatted_date || void 0
        });
        if (action === "buy") {
          router.visit("/checkout");
        }
      }
    } catch (error) {
      console.error("Failed to fetch product details", error);
    } finally {
      setLoadingAction(null);
    }
  };
  const handleWishlistToggle = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (wishlisted) {
      removeFromWishlist(product.id);
    } else {
      addToWishlist({
        productId: product.id,
        name: product.name,
        slug: product.slug,
        price: product.price,
        mrp: product.mrp,
        discount_percentage: product.discount_percentage,
        image: product.image,
        video: product.video,
        brand: product.brand,
        category: product.category?.name || "",
        deliveryDate: product.delivery_timeline?.formatted_date || void 0
      });
      trackAddToWishlist(product);
    }
  };
  const cardStyle = settings?.pc_style || "lift";
  const bgColor = settings?.pc_bg_color || "#ffffff";
  const borderRadius = settings?.pc_border_radius || "rounded";
  const shadowInt = settings?.pc_shadow || "soft";
  const buyNowStyle = settings?.pc_buynow_style || (settings?.pc_btn_layout === "icon_only" ? "icon_only" : settings?.pc_btn_layout === "both" ? "text_icon" : "text_only");
  const cartStyle = settings?.pc_cart_style || "hidden";
  const wishlistStyle = settings?.pc_wishlist_style || (settings?.pc_show_wishlist === "false" ? "hidden" : "icon_only");
  const textColor = settings?.pc_text_color || "#000000";
  const buynowBg = settings?.pc_buynow_bg_color || "#000000";
  const buynowText = settings?.pc_buynow_text_color || "#ffffff";
  const cartBg = settings?.pc_cart_bg_color || "#f3f4f6";
  const cartText = settings?.pc_cart_text_color || "#1f2937";
  const wishlistBg = settings?.pc_wishlist_bg_color || "#ffffff";
  const wishlistText = settings?.pc_wishlist_text_color || "#9ca3af";
  const imageAspect = settings?.pc_image_aspect || "aspect-[4/5]";
  const showColors = settings?.pc_show_colors || "0";
  const colorStyle = settings?.pc_color_style || "overlap";
  let cardClasses = "group block transition-all duration-300 relative border overflow-hidden ";
  if (borderRadius === "square") cardClasses += "rounded-none ";
  else if (borderRadius === "pill") cardClasses += "rounded-[2rem] ";
  else cardClasses += "rounded-2xl ";
  if (cardStyle === "outline") cardClasses += "border-gray-200 ";
  else cardClasses += "border-gray-100 ";
  if (cardStyle === "lift") {
    if (shadowInt === "soft") cardClasses += "shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] hover:-translate-y-1 ";
    else if (shadowInt === "strong") cardClasses += "shadow-lg hover:shadow-2xl hover:-translate-y-1.5 ";
    else cardClasses += "hover:-translate-y-1 ";
  } else {
    if (shadowInt === "soft") cardClasses += "shadow-sm hover:shadow ";
    else if (shadowInt === "strong") cardClasses += "shadow-md hover:shadow-lg ";
  }
  const imgRadiusClass = borderRadius === "square" ? "rounded-none" : borderRadius === "pill" ? "rounded-[1.75rem]" : "rounded-xl";
  const productUrl = activeCategory ? `/product/${product.slug}?category=${activeCategory}` : `/product/${product.slug}`;
  return /* @__PURE__ */ jsx("div", { className: cardClasses, style: { backgroundColor: bgColor, color: textColor }, children: /* @__PURE__ */ jsxs("div", { className: `p-3 h-full flex flex-col`, children: [
    /* @__PURE__ */ jsxs(Link, { href: productUrl, className: `block relative ${imageAspect} bg-gray-50 overflow-hidden ${imgRadiusClass} cursor-pointer`, children: [
      product.video || product.image ? /* @__PURE__ */ jsxs(Fragment, { children: [
        product.video || product.image && product.image.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx(
          "video",
          {
            src: product.video || product.image,
            className: `object-cover object-center absolute inset-0 w-full h-full transition-all duration-500 ease-out ${product.hover_image ? "group-hover:opacity-0 group-hover:scale-[1.03]" : "group-hover:scale-[1.03]"}`,
            autoPlay: true,
            loop: true,
            muted: true,
            playsInline: true
          }
        ) : /* @__PURE__ */ jsx(
          "img",
          {
            src: product.image,
            alt: product.name,
            fill: true,
            unoptimized: true,
            className: `object-cover object-center transition-all duration-500 ease-out ${product.hover_image ? "group-hover:opacity-0 group-hover:scale-[1.03]" : "group-hover:scale-[1.03]"}`
          }
        ),
        product.hover_image && (product.hover_image.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx(
          "video",
          {
            src: product.hover_image,
            className: "object-cover object-center absolute inset-0 w-full h-full opacity-0 group-hover:opacity-100 group-hover:scale-[1.03] transition-all duration-500 ease-out",
            autoPlay: true,
            loop: true,
            muted: true,
            playsInline: true
          }
        ) : /* @__PURE__ */ jsx(
          "img",
          {
            src: product.hover_image,
            alt: `${product.name} alternate view`,
            fill: true,
            unoptimized: true,
            className: "object-cover object-center absolute inset-0 opacity-0 group-hover:opacity-100 group-hover:scale-[1.03] transition-all duration-500 ease-out"
          }
        )),
        /* @__PURE__ */ jsx("div", { className: `absolute inset-0 bg-gradient-to-t from-black/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 ${imgRadiusClass}` })
      ] }) : /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center h-full text-gray-300 bg-gray-50", children: "No Image" }),
      product.is_new && /* @__PURE__ */ jsx("span", { className: "absolute top-3 left-3 bg-primary/90 backdrop-blur-md text-white border border-primary/20 text-[10px] px-2.5 py-1 font-bold rounded-full uppercase tracking-wider shadow-sm shadow-primary/20 z-10", children: "New" }),
      onRemove && /* @__PURE__ */ jsx(
        "button",
        {
          onClick: (e) => {
            e.preventDefault();
            e.stopPropagation();
            onRemove();
          },
          className: "absolute top-3 right-3 bg-white/90 backdrop-blur-md hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full p-2 shadow-sm transition-colors z-20",
          title: "Remove from Wishlist",
          children: /* @__PURE__ */ jsx(Trash2, { size: 15 })
        }
      )
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "mt-4 px-1 pb-1 flex flex-col gap-1 flex-grow", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center gap-2 min-h-[14px]", children: [
        /* @__PURE__ */ jsx("span", { className: "text-[10px] text-gray-400 uppercase font-bold tracking-widest truncate", children: product.brand || product.category }),
        showColors === "1" && product.colors && product.colors.length > 0 && /* @__PURE__ */ jsxs("div", { className: `flex items-center shrink-0 ${colorStyle === "overlap" ? "-space-x-1.5" : "space-x-1"}`, children: [
          product.colors.slice(0, 4).map((c, i) => /* @__PURE__ */ jsx(
            "div",
            {
              className: "w-3.5 h-3.5 rounded-full border border-gray-200/50 shadow-sm relative z-10",
              style: { backgroundColor: c.hex, zIndex: 10 - i },
              title: c.name
            },
            i
          )),
          product.colors.length > 4 && /* @__PURE__ */ jsxs("span", { className: `text-[9px] font-bold text-gray-500 ${colorStyle === "overlap" ? "ml-1 z-0" : ""}`, children: [
            "+",
            product.colors.length - 4
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsx(Link, { href: productUrl, className: "block cursor-pointer", children: /* @__PURE__ */ jsx("h3", { className: "text-sm font-heading font-medium text-gray-900 group-hover:text-primary transition-colors line-clamp-1", children: product.name }) }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-1", children: [
        product.mrp > product.price && /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-400 font-medium line-through", children: formatPrice(product.mrp) }),
        /* @__PURE__ */ jsx("span", { className: "text-base font-heading font-extrabold text-gray-900 shrink-0", children: formatPrice(product.price) }),
        product.discount_percentage > 0 && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded ml-auto", children: [
          product.discount_percentage,
          "% OFF"
        ] })
      ] }),
      product.coupon_price && /* @__PURE__ */ jsxs("div", { className: "text-[11px] text-gray-500 mt-1 font-medium bg-green-50/50 p-1.5 rounded-md border border-green-100/50", children: [
        "Best Price ",
        /* @__PURE__ */ jsx("span", { className: "text-green-700 font-bold", children: formatPrice(product.coupon_price) }),
        " with coupon"
      ] }),
      /* @__PURE__ */ jsx("div", { className: "flex-grow" }),
      (buyNowStyle !== "hidden" || cartStyle !== "hidden" || wishlistStyle !== "hidden") && /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center gap-1.5 mt-3 pt-2", children: [
        buyNowStyle !== "hidden" && /* @__PURE__ */ jsx(
          "button",
          {
            onClick: (e) => handleAction(e, "buy"),
            disabled: loadingAction !== null,
            style: { backgroundColor: buynowBg, color: buynowText },
            className: "flex-1 flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2.5 rounded-lg hover:opacity-90 transition-opacity shadow-sm focus:ring-2 focus:ring-offset-1 focus:ring-black cursor-pointer disabled:opacity-70 disabled:cursor-wait",
            children: loadingAction === "buy" ? /* @__PURE__ */ jsx(Loader2, { className: "w-4 h-4 shrink-0 animate-spin" }) : /* @__PURE__ */ jsxs(Fragment, { children: [
              (buyNowStyle === "icon_only" || buyNowStyle === "text_icon") && /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 shrink-0", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" }) }),
              buyNowStyle !== "icon_only" && /* @__PURE__ */ jsx("span", { children: "Buy Now" })
            ] })
          }
        ),
        cartStyle !== "hidden" && /* @__PURE__ */ jsx(
          "button",
          {
            onClick: (e) => handleAction(e, "cart"),
            disabled: loadingAction !== null,
            style: { backgroundColor: cartBg, color: cartText },
            className: "flex-1 flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2.5 rounded-lg hover:opacity-90 transition-opacity focus:ring-2 focus:ring-offset-1 focus:ring-gray-400 cursor-pointer disabled:opacity-70 disabled:cursor-wait",
            children: loadingAction === "cart" ? /* @__PURE__ */ jsx(Loader2, { className: "w-4 h-4 shrink-0 animate-spin text-gray-400" }) : /* @__PURE__ */ jsxs(Fragment, { children: [
              (cartStyle === "icon_only" || cartStyle === "text_icon") && /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 shrink-0", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" }) }),
              cartStyle !== "icon_only" && /* @__PURE__ */ jsx("span", { children: "Add to Cart" })
            ] })
          }
        ),
        wishlistStyle !== "hidden" && /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: handleWishlistToggle,
            style: wishlisted ? void 0 : { backgroundColor: wishlistBg, color: wishlistText },
            className: `flex shrink-0 items-center justify-center gap-1.5 border rounded-lg transition-all active:scale-95 cursor-pointer
                                        ${wishlisted ? "border-red-300 bg-red-50 text-red-500" : "border-gray-200 hover:opacity-90 hover:border-red-200"}
                                        ${wishlistStyle === "icon_only" ? "w-9 h-9" : "flex-1 py-2.5 px-3"}`,
            "aria-label": wishlisted ? "Remove from Wishlist" : "Add to Wishlist",
            children: [
              /* @__PURE__ */ jsx("svg", { className: "w-4 h-4 shrink-0", fill: wishlisted ? "currentColor" : "none", stroke: "currentColor", strokeWidth: "2", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", d: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" }) }),
              wishlistStyle !== "icon_only" && /* @__PURE__ */ jsx("span", { className: "text-xs font-bold uppercase tracking-wider", children: wishlisted ? "Saved" : "Wishlist" })
            ]
          }
        )
      ] })
    ] })
  ] }) });
}
const SORT_OPTIONS = [
  { label: "Newest Arrivals", value: "new" },
  { label: "Featured", value: "featured" },
  { label: "Best Sellers", value: "best_seller" },
  { label: "Price: Low to High", value: "price_low_high" },
  { label: "Price: High to Low", value: "price_high_low" },
  { label: "A to Z", value: "a_z" },
  { label: "Z to A", value: "z_a" }
];
function ProductListingInner({ title, initialFilters, baseEndpoint = "/api/products", queryKey, queryValue }) {
  const { settings } = usePage().props;
  const { url } = usePage();
  const searchParams = new URLSearchParams(url.substring(url.indexOf("?")));
  const primaryColor = settings?.tc_primary_color || "#000000";
  const activeQueryKey = queryKey || (searchParams.has("category") ? "category" : searchParams.has("collection") ? "collection" : void 0);
  const activeQueryValue = queryValue || (activeQueryKey ? searchParams.get(activeQueryKey) : void 0) || void 0;
  const [products, setProducts] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [isFilterOpen, setIsFilterOpen] = useState(false);
  const [filters, setFilters] = useState({
    in_stock: initialFilters?.in_stock || false,
    sort: initialFilters?.sort || "new",
    min_price: initialFilters?.min_price || "",
    max_price: initialFilters?.max_price || "",
    size: initialFilters?.size || [],
    color: initialFilters?.color || [],
    fit: initialFilters?.fit || [],
    fabric: initialFilters?.fabric || []
  });
  const [expandedSections, setExpandedSections] = useState({
    sort: true,
    price: true,
    size: true,
    color: true,
    fit: false,
    fabric: false
  });
  const toggleSection = (section) => {
    setExpandedSections((prev) => ({ ...prev, [section]: !prev[section] }));
  };
  const loader = useRef(null);
  const buildQueryString = (currentPage) => {
    const params = new URLSearchParams();
    params.append("page", currentPage.toString());
    if (activeQueryKey && activeQueryValue) params.append(activeQueryKey, activeQueryValue);
    if (filters.in_stock) params.append("in_stock", "1");
    if (filters.sort) params.append("sort", filters.sort);
    if (filters.min_price) params.append("min_price", filters.min_price);
    if (filters.max_price) params.append("max_price", filters.max_price);
    if (filters.size.length) params.append("size", filters.size.join(","));
    if (filters.color.length) params.append("color", filters.color.join(","));
    if (filters.fit.length) params.append("fit", filters.fit.join(","));
    if (filters.fabric.length) params.append("fabric", filters.fabric.join(","));
    params.append("_t", Date.now().toString());
    return params.toString();
  };
  const fetchProducts = useCallback(async (currentPage, append = false) => {
    if (!append) setLoading(true);
    else setLoadingMore(true);
    const currentUrl = `${baseEndpoint}?${buildQueryString(currentPage)}`;
    console.log("Fetching products from:", currentUrl);
    try {
      const res = await api.get(currentUrl);
      const fetched = res.data.data;
      if (append) {
        setProducts((prev) => [...prev, ...fetched]);
      } else {
        setProducts(fetched);
        if (res.data.filters) {
          const dynamicFilters = res.data.filters;
          if (dynamicFilters.fits) setAvailableFits(dynamicFilters.fits);
          if (dynamicFilters.fabrics) setAvailableFabrics(dynamicFilters.fabrics);
        }
      }
      setHasMore(res.data.meta.current_page < res.data.meta.last_page);
    } catch (error) {
      console.error("Failed to fetch products from " + currentUrl, error);
      if (!append) setProducts([]);
      setHasMore(false);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [filters, baseEndpoint, activeQueryKey, activeQueryValue]);
  useEffect(() => {
    setPage(1);
    setHasMore(true);
    fetchProducts(1, false);
  }, [filters, fetchProducts, activeQueryKey, activeQueryValue]);
  useEffect(() => {
    const handleObserver = (entities) => {
      const target = entities[0];
      if (target.isIntersecting && hasMore && !loading && !loadingMore) {
        setPage((prev) => {
          const nextPage = prev + 1;
          fetchProducts(nextPage, true);
          return nextPage;
        });
      }
    };
    const option = { root: null, rootMargin: "200px", threshold: 0 };
    const observer = new IntersectionObserver(handleObserver, option);
    if (loader.current) observer.observe(loader.current);
    return () => observer.disconnect();
  }, [hasMore, loading, loadingMore, fetchProducts]);
  const handleFilterChange = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
  };
  const toggleArrayFilter = (key, value) => {
    setFilters((prev) => {
      const current = prev[key];
      if (current.includes(value)) {
        return { ...prev, [key]: current.filter((item) => item !== value) };
      }
      return { ...prev, [key]: [...current, value] };
    });
  };
  const clearFilters = () => {
    setFilters({
      in_stock: false,
      sort: "new",
      min_price: "",
      max_price: "",
      size: [],
      color: [],
      fit: [],
      fabric: []
    });
  };
  const [availableFits, setAvailableFits] = useState([]);
  const [availableFabrics, setAvailableFabrics] = useState([]);
  const MOCK_SIZES = ["S", "M", "L", "XL", "XXL"];
  const MOCK_COLORS = ["Black", "White", "Red", "Blue", "Green", "Navy"];
  const displayTitle = title ? title : activeQueryValue ? activeQueryValue.split("-").join(" ") : "Products";
  return /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 py-8 relative", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row items-center justify-between mb-8 gap-4", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-3xl font-bold capitalize", children: displayTitle }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 w-full md:w-auto", children: [
        /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: () => setIsFilterOpen(true),
            className: "flex-1 md:flex-none flex items-center justify-center gap-2 border px-4 py-2 rounded-xl lg:hidden font-medium bg-white shadow-sm",
            children: [
              /* @__PURE__ */ jsx(SlidersHorizontal, { size: 18 }),
              " Filters"
            ]
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "hidden lg:flex items-center gap-2", children: [
          /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-500 font-medium", children: "Sort By:" }),
          /* @__PURE__ */ jsx(
            "select",
            {
              value: filters.sort,
              onChange: (e) => handleFilterChange("sort", e.target.value),
              className: "border-none font-semibold text-sm focus:ring-0 cursor-pointer bg-transparent",
              style: { color: primaryColor },
              children: SORT_OPTIONS.map((opt) => /* @__PURE__ */ jsx("option", { value: opt.value, children: opt.label }, opt.value))
            }
          )
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col lg:flex-row gap-8 items-start", children: [
      isFilterOpen && /* @__PURE__ */ jsx("div", { className: "fixed inset-0 bg-black/50 z-30 lg:hidden", onClick: () => setIsFilterOpen(false) }),
      /* @__PURE__ */ jsxs("div", { className: `
                    fixed lg:sticky top-[64px] pb-16 lg:pb-0 right-0 h-[calc(100vh-64px)] lg:h-auto 
                    w-80 lg:w-64 bg-white z-40 lg:z-0 
                    transform transition-transform duration-300 ease-in-out
                    flex flex-col border-l lg:border-l-0 lg:border-r border-gray-100 pr-0 lg:pr-6
                    ${isFilterOpen ? "translate-x-0" : "translate-x-full lg:translate-x-0"}
                `, children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between p-4 lg:p-0 lg:pb-4 border-b lg:border-none", children: [
          /* @__PURE__ */ jsxs("h2", { className: "text-lg font-bold flex items-center gap-2", children: [
            /* @__PURE__ */ jsx(Filter, { size: 18 }),
            " Filters"
          ] }),
          /* @__PURE__ */ jsx("button", { onClick: () => setIsFilterOpen(false), className: "lg:hidden p-2 text-gray-500", children: /* @__PURE__ */ jsx(X, { size: 20 }) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex-1 overflow-y-auto p-4 lg:p-0 space-y-6 lg:mt-2 custom-scrollbar", children: [
          /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-3 cursor-pointer group", children: [
            /* @__PURE__ */ jsxs("div", { className: "relative flex items-center", children: [
              /* @__PURE__ */ jsx(
                "input",
                {
                  type: "checkbox",
                  checked: filters.in_stock,
                  onChange: (e) => handleFilterChange("in_stock", e.target.checked),
                  className: "peer sr-only"
                }
              ),
              /* @__PURE__ */ jsx("div", { className: "w-5 h-5 border rounded border-gray-300 peer-checked:border-primary peer-checked:bg-primary transition-colors flex items-center justify-center", children: /* @__PURE__ */ jsx("svg", { className: "w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 placeholder-transition", viewBox: "0 0 20 20", fill: "currentColor", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z", clipRule: "evenodd" }) }) })
            ] }),
            /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-gray-700 group-hover:text-black", children: "In Stock Only" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-100 pt-5", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => toggleSection("price"), className: "flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4", children: [
              "Price ",
              /* @__PURE__ */ jsx(ChevronDown, { size: 16, className: `transition-transform ${expandedSections.price ? "rotate-180" : ""}` })
            ] }),
            expandedSections.price && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(
                "input",
                {
                  type: "number",
                  placeholder: "Min",
                  className: "w-full border border-gray-200 rounded-md p-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none",
                  value: filters.min_price,
                  onChange: (e) => handleFilterChange("min_price", e.target.value)
                }
              ),
              /* @__PURE__ */ jsx("span", { className: "text-gray-400", children: "-" }),
              /* @__PURE__ */ jsx(
                "input",
                {
                  type: "number",
                  placeholder: "Max",
                  className: "w-full border border-gray-200 rounded-md p-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none",
                  value: filters.max_price,
                  onChange: (e) => handleFilterChange("max_price", e.target.value)
                }
              )
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-100 pt-5", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => toggleSection("size"), className: "flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4", children: [
              "Size ",
              /* @__PURE__ */ jsx(ChevronDown, { size: 16, className: `transition-transform ${expandedSections.size ? "rotate-180" : ""}` })
            ] }),
            expandedSections.size && /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: MOCK_SIZES.map((s) => /* @__PURE__ */ jsx(
              "button",
              {
                onClick: () => toggleArrayFilter("size", s),
                className: `min-w-[40px] h-10 px-2 rounded-md border text-sm font-medium transition-colors ${filters.size.includes(s) ? "bg-black text-white border-black" : "border-gray-200 text-gray-700 hover:border-gray-300"}`,
                children: s
              },
              s
            )) })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-100 pt-5", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => toggleSection("color"), className: "flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4", children: [
              "Color ",
              /* @__PURE__ */ jsx(ChevronDown, { size: 16, className: `transition-transform ${expandedSections.color ? "rotate-180" : ""}` })
            ] }),
            expandedSections.color && /* @__PURE__ */ jsx("div", { className: "space-y-2", children: MOCK_COLORS.map((c) => /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-3 cursor-pointer group", children: [
              /* @__PURE__ */ jsx("input", { type: "checkbox", checked: filters.color.includes(c), onChange: () => toggleArrayFilter("color", c), className: "w-4 h-4 rounded border-gray-300 text-black focus:ring-black" }),
              /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-600 group-hover:text-black", children: c })
            ] }, c)) })
          ] }),
          availableFits.length > 0 && /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-100 pt-5", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => toggleSection("fit"), className: "flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 mb-4", children: [
              "Fit ",
              /* @__PURE__ */ jsx(ChevronDown, { size: 16, className: `transition-transform ${expandedSections.fit ? "rotate-180" : ""}` })
            ] }),
            expandedSections.fit && /* @__PURE__ */ jsx("div", { className: "space-y-2", children: availableFits.map((f) => /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-3 cursor-pointer group", children: [
              /* @__PURE__ */ jsx("input", { type: "checkbox", checked: filters.fit.includes(f), onChange: () => toggleArrayFilter("fit", f), className: "w-4 h-4 rounded border-gray-300 text-black focus:ring-black" }),
              /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-600 group-hover:text-black", children: f })
            ] }, f)) })
          ] }),
          availableFabrics.length > 0 && /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-100 pt-5", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => toggleSection("fabric"), className: "flex items-center justify-between w-full font-bold uppercase text-xs tracking-wider text-gray-900 pb-2", children: [
              "Fabric ",
              /* @__PURE__ */ jsx(ChevronDown, { size: 16, className: `transition-transform ${expandedSections.fabric ? "rotate-180" : ""}` })
            ] }),
            expandedSections.fabric && /* @__PURE__ */ jsx("div", { className: "space-y-2 pt-2", children: availableFabrics.map((f) => /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-3 cursor-pointer group", children: [
              /* @__PURE__ */ jsx("input", { type: "checkbox", checked: filters.fabric.includes(f), onChange: () => toggleArrayFilter("fabric", f), className: "w-4 h-4 rounded border-gray-300 text-black focus:ring-black" }),
              /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-600 group-hover:text-black", children: f })
            ] }, f)) })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "p-4 border-t border-gray-100 lg:sticky lg:bottom-0 lg:bg-white z-10 w-full", children: [
          /* @__PURE__ */ jsx("button", { onClick: clearFilters, className: "w-full py-2.5 text-sm font-bold text-gray-500 hover:text-black hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-200", children: "Clear All Filters" }),
          /* @__PURE__ */ jsx("button", { onClick: () => setIsFilterOpen(false), className: "w-full mt-2 py-3 bg-black text-white text-sm font-bold rounded-lg lg:hidden shadow-sm", children: "Show Results" })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex-1 w-full min-w-0", children: [
        loading && products.length === 0 ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-x-4 gap-y-10", children: [...Array(6)].map((_, i) => /* @__PURE__ */ jsxs("div", { className: "animate-pulse", children: [
          /* @__PURE__ */ jsx("div", { className: "bg-gray-100 aspect-[4/5] rounded-xl mb-4" }),
          /* @__PURE__ */ jsx("div", { className: "h-4 bg-gray-100 rounded w-3/4 mb-2" }),
          /* @__PURE__ */ jsx("div", { className: "h-4 bg-gray-100 rounded w-1/4" })
        ] }, i)) }) : products.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "py-20 flex flex-col items-center justify-center text-center", children: [
          /* @__PURE__ */ jsx("div", { className: "w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100", children: /* @__PURE__ */ jsx(Filter, { size: 32, className: "text-gray-300" }) }),
          /* @__PURE__ */ jsx("h3", { className: "text-xl font-bold mb-2 text-gray-900", children: "No products found" }),
          /* @__PURE__ */ jsx("p", { className: "text-gray-500 mb-6 max-w-sm", children: "We couldn't find any products matching your current filters." }),
          /* @__PURE__ */ jsx("button", { onClick: clearFilters, className: "px-6 py-3 bg-black text-white font-bold rounded-xl shadow-sm hover:translate-y(-1px) transition-all", children: "Clear Filters" })
        ] }) : /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-8 lg:gap-y-12", children: products.map((product) => /* @__PURE__ */ jsx(ProductCard, { product, activeCategory: activeQueryKey === "category" ? activeQueryValue : void 0 }, product.id)) }),
        hasMore && products.length > 0 && /* @__PURE__ */ jsx("div", { ref: loader, className: "w-full flex justify-center py-12 mt-4 text-gray-400", children: loadingMore ? /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx("div", { className: "w-2 h-2 bg-black rounded-full animate-bounce [animation-delay:-0.3s]" }),
          /* @__PURE__ */ jsx("div", { className: "w-2 h-2 bg-black rounded-full animate-bounce [animation-delay:-0.15s]" }),
          /* @__PURE__ */ jsx("div", { className: "w-2 h-2 bg-black rounded-full animate-bounce" })
        ] }) : /* @__PURE__ */ jsx("span", { className: "text-sm font-medium", children: "Scroll to load more" }) })
      ] })
    ] })
  ] });
}
function ProductListing(props) {
  return /* @__PURE__ */ jsx(Suspense, { fallback: /* @__PURE__ */ jsx("div", { className: "flex justify-center items-center h-64", children: /* @__PURE__ */ jsx("div", { className: "w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin" }) }), children: /* @__PURE__ */ jsx(ProductListingInner, { ...props }) });
}
function CategoryPage({ category }) {
  const { app_url } = usePage().props;
  const baseUrl = app_url || "https://dopestyle.in";
  if (!category) return null;
  const getCategorySchema = () => {
    return {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": category.name,
      "description": category.description || `Shop the latest ${category.name}`,
      "url": `${baseUrl}/category/${category.slug}`
    };
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: category.name }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getCategorySchema()) })
    ] }),
    /* @__PURE__ */ jsx(
      ProductListing,
      {
        title: category.name,
        queryKey: "category",
        queryValue: category.slug
      }
    )
  ] });
}
const __vite_glob_0_6 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: CategoryPage
}, Symbol.toStringTag, { value: "Module" }));
function Inp({ label, value, onChange, placeholder, req, readOnly }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1", children: [
    /* @__PURE__ */ jsxs("label", { className: "text-[10px] font-bold text-gray-500 uppercase tracking-wider", children: [
      label,
      req && /* @__PURE__ */ jsx("span", { className: "text-red-500 ml-0.5", children: "*" })
    ] }),
    /* @__PURE__ */ jsx(
      "input",
      {
        value,
        onChange: (e) => onChange(e.target.value),
        placeholder,
        readOnly,
        className: "w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all read-only:bg-gray-100 read-only:text-gray-600 read-only:cursor-not-allowed read-only:focus:ring-0 read-only:focus:border-gray-200"
      }
    )
  ] });
}
function CheckoutAddress({ selectedId, onChange }) {
  const { user } = useAuthStore();
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [saving, setSaving] = useState(false);
  const [err, setErr] = useState("");
  const [form, setForm] = useState({ name: "", phone: "", line1: "", line2: "", pincode: "", state: "", city: "", district: "", country: "India" });
  const [locked, setLocked] = useState({ city: false, district: false, state: false, country: true });
  const [activeCountryId, setActiveCountryId] = useState(null);
  useEffect(() => {
    api.get("/api/localization/countries").then((res) => {
      if (res.data && res.data.length > 0) {
        setActiveCountryId(res.data[0].id);
        setForm((p) => ({ ...p, country: res.data[0].name }));
      }
    }).catch(() => {
    });
  }, []);
  useEffect(() => {
    const fetchPincode = async () => {
      if (form.pincode.length === 6 && activeCountryId) {
        try {
          const res = await api.get(`/api/localization/postal-code/${activeCountryId}/${form.pincode}`);
          if (res.data.found) {
            setForm((p) => ({
              ...p,
              city: res.data.city || p.city,
              district: res.data.district || p.district,
              state: res.data.state || p.state
            }));
            setLocked((p) => ({
              ...p,
              city: !!res.data.city,
              district: !!res.data.district,
              state: !!res.data.state
            }));
          } else {
            setLocked((p) => ({ ...p, city: false, district: false, state: false }));
          }
        } catch (e) {
          setLocked((p) => ({ ...p, city: false, district: false, state: false }));
        }
      } else if (form.pincode.length < 6) {
        setLocked((p) => ({ ...p, city: false, district: false, state: false }));
      }
    };
    fetchPincode();
  }, [form.pincode]);
  const [countryCode, setCountryCode] = useState("+91");
  const f = (k, v) => setForm((p) => ({ ...p, [k]: v }));
  const load = async () => {
    if (!user) return;
    setLoading(true);
    try {
      const r = await api.get("/api/account/addresses");
      const list = r.data;
      setAddresses(list);
      const def = list.find((a) => a.is_default) || (list.length === 1 ? list[0] : null);
      if (def && !selectedId) onChange(def);
    } catch {
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => {
    load();
  }, [user]);
  const save = async () => {
    if (!form.name || !form.phone || !form.line1 || !form.city || !form.state || !form.pincode) {
      setErr("Please fill all required fields.");
      return;
    }
    setSaving(true);
    setErr("");
    let finalPhone = form.phone.trim();
    if (finalPhone.startsWith(countryCode)) {
      finalPhone = finalPhone.substring(countryCode.length);
    }
    const rawCode = countryCode.replace("+", "");
    if (finalPhone.startsWith(rawCode)) {
      finalPhone = finalPhone.substring(rawCode.length);
    }
    finalPhone = `${countryCode}${finalPhone}`;
    try {
      await api.post("/api/account/addresses", { ...form, phone: finalPhone });
      setShowForm(false);
      setForm({ name: "", phone: "", line1: "", line2: "", pincode: "", state: "", city: "", district: "", country: form.country });
      setLocked({ city: false, district: false, state: false, country: true });
      await load();
    } catch (e) {
      setErr(e.response?.data?.message || "Could not save.");
    } finally {
      setSaving(false);
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
    loading && /* @__PURE__ */ jsx("div", { className: "h-20 bg-gray-50 rounded-2xl animate-pulse" }),
    !loading && addresses.map((addr) => /* @__PURE__ */ jsxs(
      "button",
      {
        onClick: () => onChange(addr),
        type: "button",
        className: `w-full text-left p-4 rounded-2xl border-2 transition-all ${selectedId === addr.id ? "border-gray-900 bg-gray-50" : "border-gray-100 hover:border-gray-300 bg-white"}`,
        children: [
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("p", { className: "text-sm font-bold text-gray-900", children: [
                addr.name,
                " ",
                /* @__PURE__ */ jsxs("span", { className: "text-gray-400 font-normal", children: [
                  "· ",
                  addr.phone
                ] })
              ] }),
              /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500 mt-1 leading-relaxed", children: [
                addr.address_line1,
                addr.address_line2 ? `, ${addr.address_line2}` : "",
                ", ",
                addr.city,
                addr.district ? `, ${addr.district}` : "",
                ", ",
                addr.state,
                " – ",
                addr.zip_code,
                /* @__PURE__ */ jsx("span", { className: "block mt-1 text-gray-500", children: addr.country })
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: `w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 ml-4 mt-0.5 transition-all ${selectedId === addr.id ? "border-gray-900 bg-gray-900" : "border-gray-300"}`, children: selectedId === addr.id && /* @__PURE__ */ jsx(Check, { className: "w-3 h-3 text-white", strokeWidth: 3 }) })
          ] }),
          addr.is_default && /* @__PURE__ */ jsx("span", { className: "mt-2 inline-block text-[9px] font-black uppercase tracking-widest bg-gray-900 text-white px-2 py-0.5 rounded-full", children: "Default" })
        ]
      },
      addr.id
    )),
    showForm && /* @__PURE__ */ jsxs("div", { className: "p-4 rounded-2xl border border-gray-200 bg-gray-50 space-y-3", children: [
      /* @__PURE__ */ jsx("p", { className: "text-xs font-black text-gray-900 uppercase tracking-wider", children: "New Delivery Address" }),
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 gap-3", children: [
        /* @__PURE__ */ jsx(Inp, { label: "Full Name", value: form.name, onChange: (v) => f("name", v), placeholder: "Recipient", req: true }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1", children: [
          /* @__PURE__ */ jsxs("label", { className: "text-[10px] font-bold text-gray-500 uppercase tracking-wider", children: [
            "Phone",
            /* @__PURE__ */ jsx("span", { className: "text-red-500 ml-0.5", children: "*" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex bg-white rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-gray-900 focus-within:border-gray-900 border border-gray-200 transition-all", children: [
            /* @__PURE__ */ jsx(CountryCodePicker, { value: countryCode, onChange: setCountryCode }),
            /* @__PURE__ */ jsx(
              "input",
              {
                value: form.phone,
                onChange: (e) => f("phone", e.target.value),
                placeholder: "10-digit",
                className: "flex-1 bg-transparent px-3.5 py-2.5 text-sm focus:outline-none"
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "Address Line 1", value: form.line1, onChange: (v) => f("line1", v), placeholder: "House, Street", req: true }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "Address Line 2", value: form.line2, onChange: (v) => f("line2", v), placeholder: "Area, Landmark (optional)" }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "Pincode", value: form.pincode, onChange: (v) => f("pincode", v), placeholder: "6-digit pincode", req: true }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "State", value: form.state, onChange: (v) => f("state", v), placeholder: "State", req: true, readOnly: locked.state }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "City", value: form.city, onChange: (v) => f("city", v), placeholder: "City", req: true, readOnly: locked.city }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "District", value: form.district, onChange: (v) => f("district", v), placeholder: "District (optional)", readOnly: locked.district }) }),
        /* @__PURE__ */ jsx("div", { className: "col-span-1", children: /* @__PURE__ */ jsx(Inp, { label: "Country", value: form.country, onChange: (v) => f("country", v), placeholder: "Country", req: true, readOnly: locked.country }) })
      ] }),
      err && /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 flex items-center gap-1", children: [
        /* @__PURE__ */ jsx(AlertCircle, { className: "w-3.5 h-3.5" }),
        err
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
        /* @__PURE__ */ jsx("button", { onClick: save, disabled: saving, className: "flex-1 bg-black text-white text-xs font-bold uppercase tracking-wider py-2.5 rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50", children: saving ? "Saving…" : "Save & Use This Address" }),
        /* @__PURE__ */ jsx("button", { onClick: () => {
          setShowForm(false);
          setErr("");
        }, className: "text-xs text-gray-500 font-medium px-4 py-2.5 rounded-xl hover:bg-gray-100 transition-all", children: "Cancel" })
      ] })
    ] }),
    !showForm && user && /* @__PURE__ */ jsxs(
      "button",
      {
        onClick: () => setShowForm(true),
        type: "button",
        className: "w-full flex items-center justify-center gap-2 border border-dashed border-gray-300 hover:border-black text-gray-500 hover:text-black text-xs font-bold uppercase tracking-wider py-3 rounded-2xl transition-all",
        children: [
          /* @__PURE__ */ jsx(Plus, { className: "w-4 h-4" }),
          " Add New Address"
        ]
      }
    )
  ] });
}
function CouponModal({ open, onClose, coupons, onApply, applying }) {
  if (!open) return null;
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/30 backdrop-blur-[2px]", onClick: onClose }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-sm rounded-2xl shadow-xl z-10 overflow-hidden", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-5 py-4 border-b border-gray-100", children: [
        /* @__PURE__ */ jsx("p", { className: "font-bold text-gray-900 text-sm", children: "Coupons & Offers" }),
        /* @__PURE__ */ jsx("button", { onClick: onClose, className: "p-1.5 hover:bg-gray-100 rounded-lg transition-colors", children: /* @__PURE__ */ jsx(X, { size: 16 }) })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "p-4 max-h-[55vh] overflow-y-auto space-y-2.5", children: coupons.length === 0 ? /* @__PURE__ */ jsx("p", { className: "text-center text-gray-400 py-8 text-sm", children: "No active coupons right now." }) : coupons.map((c) => /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between p-3.5 border border-gray-100 rounded-xl hover:border-gray-300 transition-all bg-gray-50", children: [
        /* @__PURE__ */ jsxs("div", { className: "min-w-0 mr-3", children: [
          /* @__PURE__ */ jsx("span", { className: "inline-block bg-gray-900 text-white text-[9px] font-black px-2 py-0.5 rounded tracking-widest mb-1", children: c.code }),
          /* @__PURE__ */ jsx("p", { className: "text-xs font-semibold text-gray-800 leading-snug", children: c.name }),
          c.min_cart_value && /* @__PURE__ */ jsxs("p", { className: "text-[10px] text-gray-400 mt-0.5", children: [
            "Min: ",
            formatPrice(c.min_cart_value)
          ] })
        ] }),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => onApply(c.code),
            disabled: applying,
            className: "shrink-0 text-[10px] font-black uppercase tracking-wider text-black border border-black px-3 py-1.5 rounded-lg hover:bg-black hover:text-white transition-all disabled:opacity-40",
            children: "Apply"
          }
        )
      ] }, c.id)) })
    ] })
  ] });
}
function CartRow({ item, update, remove }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex gap-4 py-5 border-b border-gray-100 last:border-0", children: [
    /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, className: "relative w-20 h-24 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100", children: item.image && /* @__PURE__ */ jsx("img", { src: item.image, alt: item.name, fill: true, className: "object-cover", unoptimized: true }) }),
    /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start gap-2", children: [
        /* @__PURE__ */ jsx(Link, { href: `/product/${item.slug}`, children: /* @__PURE__ */ jsx("h3", { className: "text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-snug line-clamp-2", children: item.name }) }),
        /* @__PURE__ */ jsx("button", { onClick: () => remove(item.skuId), className: "text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-0.5", children: /* @__PURE__ */ jsx(Trash2, { size: 15 }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-2 flex-wrap", children: [
        item.colorName && /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-1.5 text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          /* @__PURE__ */ jsx("span", { className: "w-2.5 h-2.5 rounded-full border border-white shadow-sm", style: { backgroundColor: item.colorHex || "#aaa" } }),
          item.colorName
        ] }),
        item.sizeName && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full", children: [
          "Size ",
          item.sizeName
        ] }),
        item.deliveryDate && /* @__PURE__ */ jsxs("span", { className: "text-[10px] font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full border border-green-100", children: [
          "Delivered by: ",
          item.deliveryDate
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-3", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 border border-gray-200 rounded-lg", children: [
          /* @__PURE__ */ jsx("button", { onClick: () => update(item.skuId, Math.max(1, item.quantity - 1)), className: "px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500", children: /* @__PURE__ */ jsx(Minus, { size: 12, strokeWidth: 3 }) }),
          /* @__PURE__ */ jsx("span", { className: "text-sm font-bold text-gray-900 w-7 text-center", children: item.quantity }),
          /* @__PURE__ */ jsx("button", { onClick: () => update(item.skuId, item.quantity + 1), className: "px-2.5 py-1.5 hover:bg-gray-50 transition-colors text-gray-500", children: /* @__PURE__ */ jsx(Plus, { size: 12, strokeWidth: 3 }) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-end", children: [
          /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: formatPrice(item.price * item.quantity) }),
          item.mrp && item.mrp > item.price ? /* @__PURE__ */ jsx("p", { className: "text-[10px] text-gray-400 line-through mt-0.5", children: formatPrice(item.mrp * item.quantity) }) : null
        ] })
      ] })
    ] })
  ] });
}
function Field({ label, value, onChange, placeholder, type = "text", span = false, readOnly = false }) {
  return /* @__PURE__ */ jsxs("div", { className: span ? "" : "", children: [
    /* @__PURE__ */ jsx("label", { className: "block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1", children: label }),
    /* @__PURE__ */ jsx(
      "input",
      {
        type,
        value,
        onChange: (e) => onChange(e.target.value),
        placeholder,
        readOnly,
        className: "w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-200 transition-all read-only:bg-gray-50 read-only:text-gray-500 read-only:cursor-not-allowed read-only:focus:ring-0 read-only:focus:border-gray-200"
      }
    )
  ] });
}
function CheckoutPage() {
  const { settings: sharedSettings } = usePage().props;
  sharedSettings?.store_name || "Store";
  const cart = useCartStore();
  const { user } = useAuthStore();
  const [mounted, setMounted] = useState(false);
  const [couponInput, setCouponInput] = useState("");
  const [publicCoupons, setPublicCoupons] = useState([]);
  const [couponModal, setCouponModal] = useState(false);
  const [applying, setApplying] = useState(false);
  const [couponCode, setCouponCode] = useState("");
  const [couponErr, setCouponErr] = useState("");
  useEffect(() => {
    if (mounted && cart.appliedCoupon && cart.items.length > 0) {
      const revalidate = async () => {
        try {
          const res = await api.post("/api/coupons/apply", {
            code: cart.appliedCoupon.code,
            cart: {
              subtotal: cart.items.reduce((s, i) => s + Number(i.price) * i.quantity, 0),
              items: cart.items.map((i) => ({
                product_id: i.productId,
                price: i.price,
                original_price: i.mrp || i.price,
                quantity: i.quantity
              }))
            }
          });
          if (res.data.success) {
            if (res.data.data.discount_amount !== cart.appliedCoupon.discountAmount) {
              cart.setAppliedCoupon({
                code: cart.appliedCoupon.code,
                discountAmount: res.data.data.discount_amount
              });
            }
          } else {
            cart.setAppliedCoupon(null);
          }
        } catch (e) {
          cart.setAppliedCoupon(null);
        }
      };
      revalidate();
    }
  }, [mounted, cart.items.length, cart.items.reduce((acc, i) => acc + i.quantity, 0)]);
  const [selectedAddr, setSelectedAddr] = useState(null);
  const [guest, setGuest] = useState({ name: "", email: "", phone: "", line1: "", line2: "", city: "", district: "", state: "", zip: "", country: "India" });
  const [locked, setLocked] = useState({ city: false, district: false, state: false, country: true });
  const [activeCountryId, setActiveCountryId] = useState(null);
  const [guestCountryCode, setGuestCountryCode] = useState("+91");
  const g = (k, v) => setGuest((p) => ({ ...p, [k]: v }));
  const [placing, setPlacing] = useState(false);
  const [orderErr, setOrderErr] = useState("");
  const [orderUUID, setOrderUUID] = useState("");
  const [settings, setSettings] = useState(null);
  const [paymentMethod, setPaymentMethod] = useState("prepaid");
  const [paymentModal, setPaymentModal] = useState(false);
  const [gcInput, setGcInput] = useState("");
  const [gcApplied, setGcApplied] = useState(null);
  const [gcValidating, setGcValidating] = useState(false);
  const [gcErr, setGcErr] = useState("");
  useEffect(() => {
    setMounted(true);
    if (cart.items && cart.items.length > 0) {
      trackInitiateCheckout(cart.totalPrice, cart.items);
    }
    api.get("/api/settings").then((r) => setSettings(r.data)).catch(() => {
    });
    api.get("/api/localization/countries").then((res) => {
      if (res.data && res.data.length > 0) {
        setActiveCountryId(res.data[0].id);
        setGuest((p) => ({ ...p, country: res.data[0].name }));
      }
    }).catch(() => {
    });
    api.get("/api/coupons/public").then(async (r) => {
      setPublicCoupons(r.data.checkout_coupons || []);
      const magicCoupons = r.data.magic_coupons || [];
      const state = useCartStore.getState();
      if (!state.appliedCoupon && magicCoupons.length > 0 && state.items.length > 0) {
        for (const mc of magicCoupons) {
          try {
            const sub = state.items.reduce((s, i) => s + i.price * i.quantity, 0);
            const items = state.items.map((i) => ({ product_id: i.productId, price: i.price, original_price: i.price, quantity: i.quantity }));
            const applyRes = await api.post("/api/coupons/apply", { code: mc.code, cart: { subtotal: sub, items } });
            if (applyRes.data.success) {
              state.setAppliedCoupon({ code: applyRes.data.data.coupon.code, discountAmount: applyRes.data.data.discount_amount });
              break;
            }
          } catch (e) {
          }
        }
      }
    }).catch(() => {
    });
  }, []);
  useEffect(() => {
    if (guest.email && guest.email.includes("@")) {
      cart.setGuestEmail(guest.email);
    }
  }, [guest.email]);
  useEffect(() => {
    const fetchPincode = async () => {
      if (guest.zip.length === 6 && activeCountryId) {
        try {
          const res = await api.get(`/api/localization/postal-code/${activeCountryId}/${guest.zip}`);
          if (res.data.found) {
            setGuest((p) => ({
              ...p,
              city: res.data.city || p.city,
              district: res.data.district || p.district,
              state: res.data.state || p.state
            }));
            setLocked((p) => ({
              ...p,
              city: !!res.data.city,
              district: !!res.data.district,
              state: !!res.data.state
            }));
          } else {
            setLocked((p) => ({ ...p, city: false, district: false, state: false }));
          }
        } catch (e) {
          setLocked((p) => ({ ...p, city: false, district: false, state: false }));
        }
      } else if (guest.zip.length < 6) {
        setLocked((p) => ({ ...p, city: false, district: false, state: false }));
      }
    };
    fetchPincode();
  }, [guest.zip, activeCountryId]);
  const handleApplyCoupon = async (code) => {
    const c = (code || couponInput).trim().toUpperCase();
    if (!c) return;
    setApplying(true);
    setCouponErr("");
    const sub = cart.items.reduce((s, i) => s + i.price * i.quantity, 0);
    try {
      const r = await api.post("/api/coupons/apply", {
        code: c,
        cart: { subtotal: sub, items: cart.items.map((i) => ({ product_id: i.productId, price: i.price, original_price: i.price, quantity: i.quantity })) }
      });
      if (r.data.success) {
        cart.setAppliedCoupon({ code: r.data.data.coupon.code, discountAmount: r.data.data.discount_amount });
        setCouponModal(false);
        setCouponInput("");
      } else {
        setCouponErr(r.data.message);
      }
    } catch (e) {
      setCouponErr(e.response?.data?.message || "Invalid coupon.");
    } finally {
      setApplying(false);
    }
  };
  const handleValidateGiftCard = async () => {
    const c = gcInput.trim().toUpperCase();
    if (!c) return;
    setGcValidating(true);
    setGcErr("");
    try {
      const r = await api.post("/api/gift-cards/validate", { code: c });
      if (r.data.success) {
        setGcApplied({ code: c, balance: r.data.remaining_amount });
        setGcInput("");
      } else {
        setGcErr(r.data.message);
      }
    } catch (e) {
      setGcErr(e.response?.data?.message || "Invalid gift card.");
    } finally {
      setGcValidating(false);
    }
  };
  const handlePlaceOrder = async () => {
    setOrderErr("");
    let addrPayload, custPayload;
    if (user && selectedAddr) {
      addrPayload = { line1: selectedAddr.address_line1, line2: selectedAddr.address_line2 || "", city: selectedAddr.city, state: selectedAddr.state, zip: selectedAddr.zip_code };
      custPayload = { name: selectedAddr.name, email: user.email, phone: selectedAddr.phone };
    } else {
      const { name, email, phone, line1, city, state, zip } = guest;
      if (!name || !email || !phone || !line1 || !city || !state || !zip) {
        setOrderErr("Please fill all delivery fields.");
        return;
      }
      let finalPhone = phone.trim();
      if (finalPhone.startsWith(guestCountryCode)) {
        finalPhone = finalPhone.substring(guestCountryCode.length);
      }
      const rawCode = guestCountryCode.replace("+", "");
      if (finalPhone.startsWith(rawCode)) {
        finalPhone = finalPhone.substring(rawCode.length);
      }
      finalPhone = `${guestCountryCode}${finalPhone}`;
      addrPayload = { line1, line2: guest.line2, city, district: guest.district, state, zip, country: guest.country };
      custPayload = { name, email, phone: finalPhone };
    }
    setPlacing(true);
    try {
      const r = await api.post("/api/checkout", {
        customer: custPayload,
        address: addrPayload,
        payment_method: paymentMethod,
        coupon_code: cart.appliedCoupon?.code || null,
        gift_card_code: gcApplied?.code || null,
        items: cart.items.map((i) => ({ sku_id: i.skuId, quantity: i.quantity, image: i.image }))
      });
      if (r.data.success) {
        const orderUUID2 = r.data.order_uuid;
        const upfrontAmount = r.data.upfront_amount || 0;
        if (paymentMethod === "cod" && upfrontAmount <= 0 || total <= 0) {
          setOrderUUID(orderUUID2);
          cart.clearCart();
          window.location.href = `/checkout/thank-you/${orderUUID2}`;
          return;
        }
        try {
          const initRes = await api.post("/api/payment/initiate", { order_uuid: orderUUID2 });
          const options = {
            key: initRes.data.key,
            amount: initRes.data.amount,
            currency: "INR",
            name: initRes.data.name,
            description: initRes.data.description,
            order_id: initRes.data.order_id,
            handler: async (response) => {
              try {
                const verifyRes = await api.post("/api/payment/verify", {
                  ...response,
                  order_uuid: orderUUID2
                });
                if (verifyRes.data.success) {
                  setOrderUUID(orderUUID2);
                  cart.clearCart();
                  window.location.href = `/checkout/thank-you/${orderUUID2}`;
                } else {
                  setOrderErr("Payment verification failed. Please contact support.");
                  try {
                    await api.post("/api/payment/failed", { order_uuid: orderUUID2 });
                  } catch (e) {
                  }
                }
              } catch (err) {
                setOrderErr(err.response?.data?.message || "Verification failed.");
                try {
                  await api.post("/api/payment/failed", { order_uuid: orderUUID2 });
                } catch (e) {
                }
              }
            },
            prefill: initRes.data.prefill,
            theme: { color: "#000000" },
            modal: {
              ondismiss: async () => {
                setOrderErr("Payment cancelled.");
                setPlacing(false);
                try {
                  await api.post("/api/payment/failed", { order_uuid: orderUUID2 });
                } catch (e) {
                }
              }
            }
          };
          const rzp = new window.Razorpay(options);
          rzp.open();
        } catch (err) {
          setOrderErr(err.response?.data?.message || "Could not initiate payment.");
          setPlacing(false);
          try {
            await api.post("/api/payment/failed", { order_uuid: orderUUID2 });
          } catch (e) {
          }
        }
      } else {
        setOrderErr(r.data.message);
        setPlacing(false);
      }
    } catch (e) {
      setOrderErr(e.response?.data?.message || "Something went wrong. Please try again.");
      setPlacing(false);
    }
  };
  if (!mounted) return /* @__PURE__ */ jsx("div", { className: "min-h-[60vh]" });
  if (orderUUID) return /* @__PURE__ */ jsxs("div", { className: "max-w-md mx-auto px-4 py-28 text-center", children: [
    /* @__PURE__ */ jsx("div", { className: "w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-6", children: /* @__PURE__ */ jsx(Check, { className: "w-8 h-8 text-green-500" }) }),
    /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold text-gray-900 mb-2", children: "Order Confirmed!" }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Thank you for your purchase." }),
    /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 font-mono mb-10", children: orderUUID }),
    /* @__PURE__ */ jsxs("div", { className: "flex gap-3 justify-center", children: [
      /* @__PURE__ */ jsxs(Link, { href: "/orders", className: "bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all flex items-center gap-2", children: [
        "My Orders ",
        /* @__PURE__ */ jsx(ArrowRight, { size: 15 })
      ] }),
      /* @__PURE__ */ jsx(Link, { href: "/shop", className: "border border-gray-200 text-gray-600 text-sm font-semibold px-6 py-3 rounded-xl hover:border-gray-400 transition-all", children: "Keep Shopping" })
    ] })
  ] });
  if (cart.items.length === 0) return /* @__PURE__ */ jsxs("div", { className: "max-w-sm mx-auto px-4 py-28 text-center", children: [
    /* @__PURE__ */ jsx(ShoppingBag, { className: "w-12 h-12 text-gray-200 mx-auto mb-5" }),
    /* @__PURE__ */ jsx("h1", { className: "text-xl font-bold text-gray-900 mb-2", children: "Your bag is empty" }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-400 mb-8", children: "Add items to get started." }),
    /* @__PURE__ */ jsxs(Link, { href: "/shop", className: "inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all", children: [
      "Browse Shop ",
      /* @__PURE__ */ jsx(ArrowRight, { size: 15 })
    ] })
  ] });
  const mrpTotal = cart.items.reduce((s, i) => s + Math.max(Number(i.mrp) || 0, Number(i.price)) * i.quantity, 0);
  const subtotal = cart.items.reduce((s, i) => s + Number(i.price) * i.quantity, 0);
  const mrpDiscount = mrpTotal > subtotal ? mrpTotal - subtotal : 0;
  const discount = cart.appliedCoupon?.discountAmount || 0;
  const subtotalAfterDiscount = Math.max(0, subtotal - discount);
  let taxAmount = 0;
  const taxBreakdown = {};
  const isTaxEnabled = settings?.is_tax_enabled == "1";
  const taxLabel = settings?.tax_label || "Tax";
  const taxInclusive = settings?.tax_inclusion === "include";
  cart.items.forEach((item) => {
    const itemTotal = item.price * item.quantity;
    const itemDiscount = subtotal > 0 ? itemTotal / subtotal * discount : 0;
    const itemFinal = itemTotal - itemDiscount;
    let itemTax = 0;
    let trueItemFinal = itemFinal;
    if (isTaxEnabled) {
      let taxRate = 0;
      if (item.tax_class && settings?.taxes) {
        const t = settings.taxes.find((t2) => t2.id === item.tax_class);
        if (t) taxRate = parseFloat(t.rate);
      }
      if (taxRate > 0) {
        if (taxInclusive) {
          trueItemFinal = itemFinal / (1 + taxRate / 100);
          itemTax = itemFinal - trueItemFinal;
        } else {
          trueItemFinal = itemFinal;
          itemTax = itemFinal * (taxRate / 100);
        }
        const rateKey = taxRate.toString();
        taxBreakdown[rateKey] = (taxBreakdown[rateKey] || 0) + itemTax;
        taxAmount += itemTax;
      }
    }
  });
  const calcShipping = (rule) => {
    if (!rule) return 0;
    if (rule.type === "free") return 0;
    if (rule.type === "flat") return parseFloat(rule.fee) || 0;
    if (rule.type === "conditional") {
      return subtotalAfterDiscount >= (parseFloat(rule.threshold) || 0) ? 0 : parseFloat(rule.fee) || 0;
    }
    if (rule.type === "tiered") {
      const tiers = rule.tiers || [];
      for (const t of tiers) {
        if (subtotalAfterDiscount <= (parseFloat(t.up_to) || 0)) {
          return parseFloat(t.fee) || 0;
        }
      }
      return tiers.length > 0 ? parseFloat(tiers[tiers.length - 1].fee) || 0 : 0;
    }
    return 0;
  };
  const codApplicableCharge = calcShipping(settings?.shipping_rules?.cod);
  let shipping = 0;
  let shippingType = "Calculated at next step";
  let prepaidDiscount = 0;
  const activeRule = paymentMethod === "prepaid" ? settings?.shipping_rules?.prepaid : settings?.shipping_rules?.cod;
  if (activeRule) {
    if (activeRule.type === "free") {
      shipping = 0;
      shippingType = "Free";
    } else if (activeRule.type === "flat") {
      shipping = parseFloat(activeRule.fee) || 0;
      shippingType = shipping === 0 ? "Free" : formatPrice(shipping);
    } else if (activeRule.type === "conditional") {
      if (subtotalAfterDiscount >= (parseFloat(activeRule.threshold) || 0)) {
        shipping = 0;
        shippingType = "Free";
      } else {
        shipping = parseFloat(activeRule.fee) || 0;
        shippingType = shipping === 0 ? "Free" : formatPrice(shipping);
      }
    } else if (activeRule.type === "tiered") {
      const tiers = activeRule.tiers || [];
      let matchedFee = 0;
      let applied = false;
      for (const t of tiers) {
        if (subtotalAfterDiscount <= (parseFloat(t.up_to) || 0)) {
          matchedFee = parseFloat(t.fee) || 0;
          applied = true;
          break;
        }
      }
      if (!applied && tiers.length > 0) {
        matchedFee = parseFloat(tiers[tiers.length - 1].fee) || 0;
      }
      shipping = matchedFee;
      shippingType = shipping === 0 ? "Free" : formatPrice(shipping);
    }
    if (paymentMethod === "prepaid") {
      if (activeRule.discount_type === "percent") {
        prepaidDiscount = subtotalAfterDiscount * (parseFloat(activeRule.discount_value) || 0) / 100;
      } else if (activeRule.discount_type === "flat") {
        prepaidDiscount = parseFloat(activeRule.discount_value) || 0;
      }
    }
  }
  let trueShipping = shipping;
  if (isTaxEnabled && shipping > 0) {
    const shippingTaxRate = parseFloat(settings?.shipping_tax_rate || "18");
    if (shippingTaxRate > 0) {
      let shippingTax = 0;
      if (taxInclusive) {
        trueShipping = shipping / (1 + shippingTaxRate / 100);
        shippingTax = shipping - trueShipping;
      } else {
        trueShipping = shipping;
        shippingTax = shipping * (shippingTaxRate / 100);
      }
      const rateKey = shippingTaxRate.toString();
      taxBreakdown[rateKey] = (taxBreakdown[rateKey] || 0) + shippingTax;
      taxAmount += shippingTax;
    }
  }
  const gcDiscount = gcApplied ? Math.min(gcApplied.balance, taxInclusive ? subtotal - discount + shipping - prepaidDiscount : subtotal - discount + shipping + taxAmount - prepaidDiscount) : 0;
  const total = taxInclusive ? Math.max(0, subtotal - discount + shipping - prepaidDiscount - gcDiscount) : Math.max(0, subtotal - discount + shipping + taxAmount - prepaidDiscount - gcDiscount);
  const totalSavings = mrpDiscount + discount + prepaidDiscount + gcDiscount;
  const codRule = settings?.shipping_rules?.cod;
  const codMinOrderAmount = parseFloat(codRule?.min_order_amount) || 0;
  const isCodAvailable = codMinOrderAmount === 0 || subtotalAfterDiscount >= codMinOrderAmount;
  let codUpfrontAmount = 0;
  if (codRule && codRule.upfront_type !== "none") {
    if (codRule.upfront_type === "fee_only") {
      codUpfrontAmount = codApplicableCharge;
    } else if (codRule.upfront_type === "tiered") {
      const upfrontTiers = codRule.upfront_tiers || [];
      for (const t of upfrontTiers) {
        if (total <= (parseFloat(t.up_to) || 0)) {
          codUpfrontAmount = parseFloat(t.fee) || 0;
          break;
        }
      }
    }
    codUpfrontAmount = Math.min(codUpfrontAmount, total);
  }
  return /* @__PURE__ */ jsxs("div", { className: "max-w-6xl mx-auto px-4 sm:px-6 py-12 md:py-16", children: [
    /* @__PURE__ */ jsx(Head, { title: "Checkout" }),
    placing && /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-50 flex items-center justify-center p-4", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-white/80 backdrop-blur-sm" }),
      /* @__PURE__ */ jsxs("div", { className: "relative bg-white border border-gray-100 p-8 rounded-3xl shadow-2xl flex flex-col items-center max-w-sm w-full mx-auto text-center z-10 animate-in fade-in zoom-in-95 duration-200", children: [
        /* @__PURE__ */ jsxs("div", { className: "relative w-16 h-16 mb-6", children: [
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 border-4 border-gray-100 rounded-full" }),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 border-4 border-black rounded-full border-t-transparent animate-spin" })
        ] }),
        /* @__PURE__ */ jsx("h3", { className: "text-xl font-bold text-gray-900 mb-2", children: "Processing Order" }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 leading-relaxed", children: "Please wait while we secure your items and send your order confirmations... Do not close this window." })
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "mb-10", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold text-gray-900", children: "Checkout" }),
      /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-400 mt-1", children: [
        cart.items.length,
        " ",
        cart.items.length === 1 ? "item" : "items",
        " in your bag"
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col lg:flex-row gap-10 items-start", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex-1 space-y-10 min-w-0", children: [
        /* @__PURE__ */ jsx("section", { children: /* @__PURE__ */ jsx("div", { className: "bg-white border-y sm:border sm:rounded-2xl px-4 sm:px-5 divide-y divide-gray-50 -mx-4 sm:mx-0", children: cart.items.map((item) => /* @__PURE__ */ jsx(CartRow, { item, update: cart.updateQuantity, remove: cart.removeItem }, item.skuId)) }) }),
        !user && /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-sm -mx-4 sm:mx-0", children: [
          /* @__PURE__ */ jsx("h3", { className: "text-base font-bold text-gray-900 mb-2", children: "Already have an account?" }),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: () => openAuthModal("login"),
              className: "inline-flex items-center justify-center px-8 py-3 bg-black text-white text-sm font-semibold rounded-xl hover:bg-gray-800 transition-colors w-full sm:w-auto",
              children: "Sign In to Checkout"
            }
          ),
          /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mt-3 max-w-xs mx-auto leading-relaxed", children: "To manage your order properly and get your saved address here, login now." })
        ] }),
        /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsxs("h2", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5", children: [
            /* @__PURE__ */ jsx(MapPin, { size: 12 }),
            " Delivery Address"
          ] }),
          user ? /* @__PURE__ */ jsx("div", { className: "bg-white border border-gray-100 rounded-2xl p-5", children: /* @__PURE__ */ jsx(CheckoutAddress, { selectedId: selectedAddr?.id ?? null, onChange: setSelectedAddr }) }) : /* @__PURE__ */ jsx("div", { className: "bg-white border border-gray-100 rounded-2xl p-5 space-y-4", children: /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 gap-3", children: [
            /* @__PURE__ */ jsx(Field, { label: "Full Name", value: guest.name, onChange: (v) => g("name", v), placeholder: "Your name" }),
            /* @__PURE__ */ jsxs("div", { className: "", children: [
              /* @__PURE__ */ jsx("label", { className: "block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1", children: "Phone" }),
              /* @__PURE__ */ jsxs("div", { className: "flex bg-white rounded-lg overflow-hidden focus-within:ring-1 focus-within:ring-gray-200 focus-within:border-gray-500 border border-gray-200 transition-all", children: [
                /* @__PURE__ */ jsx(CountryCodePicker, { value: guestCountryCode, onChange: setGuestCountryCode }),
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    value: guest.phone,
                    onChange: (e) => g("phone", e.target.value),
                    placeholder: "10-digit",
                    className: "flex-1 bg-transparent px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none"
                  }
                )
              ] })
            ] }),
            /* @__PURE__ */ jsx(Field, { label: "Email", value: guest.email, onChange: (v) => g("email", v), placeholder: "you@email.com", type: "email", span: true }),
            /* @__PURE__ */ jsx(Field, { label: "Address Line 1", value: guest.line1, onChange: (v) => g("line1", v), placeholder: "House, Street", span: true }),
            /* @__PURE__ */ jsx(Field, { label: "Address Line 2", value: guest.line2, onChange: (v) => g("line2", v), placeholder: "Area, Landmark (optional)", span: true }),
            /* @__PURE__ */ jsx(Field, { label: "Pincode", value: guest.zip, onChange: (v) => g("zip", v), span: true }),
            /* @__PURE__ */ jsx(Field, { label: "State", value: guest.state, onChange: (v) => g("state", v), readOnly: locked.state }),
            /* @__PURE__ */ jsx(Field, { label: "City", value: guest.city, onChange: (v) => g("city", v), readOnly: locked.city }),
            /* @__PURE__ */ jsx(Field, { label: "District", value: guest.district, onChange: (v) => g("district", v), readOnly: locked.district, placeholder: "District (optional)" }),
            /* @__PURE__ */ jsx(Field, { label: "Country", value: guest.country, onChange: (v) => g("country", v), readOnly: locked.country })
          ] }) })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "w-full lg:w-[340px] shrink-0 space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-5", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-4", children: [
            /* @__PURE__ */ jsx(Tag, { size: 14, className: "text-gray-500" }),
            /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold text-gray-700", children: "Apply Coupon" })
          ] }),
          cart.appliedCoupon ? /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between bg-green-50 border border-green-100 rounded-xl px-4 py-3", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "text-xs font-black text-green-700 tracking-widest uppercase", children: cart.appliedCoupon.code }),
              /* @__PURE__ */ jsxs("p", { className: "text-xs text-green-600 mt-0.5", children: [
                "−",
                formatPrice(discount),
                " saved"
              ] })
            ] }),
            /* @__PURE__ */ jsx("button", { onClick: () => cart.setAppliedCoupon(null), className: "text-green-400 hover:text-green-700 transition-colors p-1", children: /* @__PURE__ */ jsx(X, { size: 15 }) })
          ] }) : /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
              /* @__PURE__ */ jsx(
                "input",
                {
                  value: couponInput,
                  onChange: (e) => setCouponInput(e.target.value.toUpperCase()),
                  onKeyDown: (e) => e.key === "Enter" && handleApplyCoupon(""),
                  placeholder: "Coupon code",
                  className: "flex-1 border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-300 uppercase font-mono tracking-wider focus:outline-none focus:border-gray-500 transition-all"
                }
              ),
              /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: () => handleApplyCoupon(""),
                  disabled: !couponInput || applying,
                  className: "bg-gray-900 text-white text-xs font-bold px-4 rounded-lg hover:bg-black transition-all disabled:opacity-30",
                  children: applying ? "…" : "Apply"
                }
              )
            ] }),
            couponErr && /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 flex items-center gap-1", children: [
              /* @__PURE__ */ jsx(AlertCircle, { size: 11 }),
              couponErr
            ] }),
            /* @__PURE__ */ jsxs("button", { onClick: () => setCouponModal(true), className: "w-full flex items-center justify-between text-xs text-gray-500 hover:text-gray-800 py-2 px-1 transition-colors", children: [
              /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-1.5", children: [
                /* @__PURE__ */ jsx(Ticket, { size: 13 }),
                "View available coupons"
              ] }),
              /* @__PURE__ */ jsx(ChevronRight, { size: 13 })
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-5", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-4", children: [
            /* @__PURE__ */ jsx(Gift, { size: 14, className: "text-gray-500" }),
            /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold text-gray-700", children: "Gift Card" })
          ] }),
          gcApplied ? /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between bg-violet-50 border border-violet-100 rounded-xl px-4 py-3", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "text-xs font-black text-violet-700 tracking-widest uppercase", children: gcApplied.code }),
              /* @__PURE__ */ jsxs("p", { className: "text-xs text-violet-500 mt-0.5", children: [
                "−",
                formatPrice(gcDiscount),
                " applied · ₹",
                (gcApplied.balance - gcDiscount).toFixed(0),
                " left on card"
              ] })
            ] }),
            /* @__PURE__ */ jsx("button", { onClick: () => setGcApplied(null), className: "text-violet-300 hover:text-violet-700 transition-colors p-1", children: /* @__PURE__ */ jsx(X, { size: 15 }) })
          ] }) : /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
              /* @__PURE__ */ jsx(
                "input",
                {
                  value: gcInput,
                  onChange: (e) => setGcInput(e.target.value.toUpperCase()),
                  onKeyDown: (e) => e.key === "Enter" && handleValidateGiftCard(),
                  placeholder: "Gift card code",
                  className: "flex-1 border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-300 uppercase font-mono tracking-wider focus:outline-none focus:border-gray-500 transition-all"
                }
              ),
              /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: handleValidateGiftCard,
                  disabled: !gcInput || gcValidating,
                  className: "bg-violet-700 text-white text-xs font-bold px-4 rounded-lg hover:bg-violet-800 transition-all disabled:opacity-30",
                  children: gcValidating ? "…" : "Apply"
                }
              )
            ] }),
            gcErr && /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 flex items-center gap-1", children: [
              /* @__PURE__ */ jsx(AlertCircle, { size: 11 }),
              gcErr
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-5", children: [
          /* @__PURE__ */ jsx("p", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4", children: "Order Summary" }),
          /* @__PURE__ */ jsxs("div", { className: "space-y-2.5 text-sm", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600", children: [
              /* @__PURE__ */ jsx("span", { children: "MRP Total" }),
              /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: formatPrice(mrpTotal) })
            ] }),
            mrpDiscount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
              /* @__PURE__ */ jsx("span", { children: "Discount on MRP" }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(mrpDiscount)
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600 font-medium", children: [
              /* @__PURE__ */ jsx("span", { children: "Cart Subtotal" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(subtotal) })
            ] }),
            discount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
              /* @__PURE__ */ jsxs("span", { children: [
                "Coupon Discount ",
                cart.appliedCoupon?.code && `(${cart.appliedCoupon.code})`
              ] }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(discount)
              ] })
            ] }),
            prepaidDiscount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
              /* @__PURE__ */ jsx("span", { children: "Prepaid Discount" }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(prepaidDiscount)
              ] })
            ] }),
            gcDiscount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-violet-600", children: [
              /* @__PURE__ */ jsxs("span", { children: [
                "Gift Card (",
                gcApplied?.code,
                ")"
              ] }),
              /* @__PURE__ */ jsxs("span", { children: [
                "−",
                formatPrice(gcDiscount)
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
              /* @__PURE__ */ jsx("span", { children: "Shipping" }),
              /* @__PURE__ */ jsx("span", { children: shippingType === "Free" ? "Free" : formatPrice(shipping) })
            ] }),
            isTaxEnabled && settings?.show_tax_in_cart_checkout !== "0" && /* @__PURE__ */ jsx(Fragment, { children: Object.entries(taxBreakdown).map(([rate, amount]) => /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
              /* @__PURE__ */ jsxs("span", { children: [
                taxLabel,
                " @ ",
                rate,
                "% ",
                taxInclusive ? "(Included)" : "(Excluded)"
              ] }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(amount) })
            ] }, rate)) }),
            totalSavings > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600 font-medium pt-1", children: [
              /* @__PURE__ */ jsx("span", { children: "Total Savings" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(totalSavings) })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "h-px bg-gray-100 my-1" }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between font-bold text-gray-900 text-base", children: [
              /* @__PURE__ */ jsx("span", { children: "Total" }),
              /* @__PURE__ */ jsx("span", { children: formatPrice(total) })
            ] }),
            paymentMethod === "cod" && codUpfrontAmount > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-2 p-3 bg-orange-50 border border-orange-100 rounded-lg space-y-2", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-sm text-orange-800 font-semibold", children: [
                /* @__PURE__ */ jsx("span", { children: "To Pay Now (Upfront)" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice(codUpfrontAmount) })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-xs text-orange-700", children: [
                /* @__PURE__ */ jsx("span", { children: "To Pay on Delivery" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice(Math.max(0, total - codUpfrontAmount)) })
              ] })
            ] })
          ] }),
          orderErr && /* @__PURE__ */ jsxs("div", { className: "mt-4 p-3 bg-red-50 border border-red-100 rounded-xl flex items-start gap-2 text-xs text-red-600", children: [
            /* @__PURE__ */ jsx(AlertCircle, { size: 13, className: "mt-0.5 shrink-0" }),
            " ",
            orderErr
          ] }),
          /* @__PURE__ */ jsx("div", { className: "mt-5 border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-gray-400 transition-colors bg-gray-50/50", onClick: () => setPaymentModal(true), children: /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5", children: "Payment Method" }),
              /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: paymentMethod === "prepaid" ? "Online Payment / UPI" : "Cash on Delivery (COD)" })
            ] }),
            /* @__PURE__ */ jsx("span", { className: "text-xs font-bold text-gray-500 underline underline-offset-2", children: "Change" })
          ] }) }),
          /* @__PURE__ */ jsxs(
            "button",
            {
              onClick: handlePlaceOrder,
              disabled: placing || !!user && !selectedAddr,
              className: "mt-5 w-full bg-gray-900 text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-black transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed",
              children: [
                placing ? "Placing Order…" : "Place Order",
                !placing && /* @__PURE__ */ jsx(ArrowRight, { size: 16 })
              ]
            }
          ),
          user && !selectedAddr && /* @__PURE__ */ jsx("p", { className: "text-center text-[10px] text-gray-400 mt-3", children: "Select a delivery address above to continue" })
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsx(CouponModal, { open: couponModal, onClose: () => setCouponModal(false), coupons: publicCoupons, onApply: handleApplyCoupon, applying }),
    paymentModal && /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/30 backdrop-blur-[2px]", onClick: () => setPaymentModal(false) }),
      /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-md rounded-2xl shadow-xl z-10 overflow-hidden", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-5 py-4 border-b border-gray-100", children: [
          /* @__PURE__ */ jsx("p", { className: "font-bold text-gray-900 text-sm", children: "Select Payment Method" }),
          /* @__PURE__ */ jsx("button", { onClick: () => setPaymentModal(false), className: "p-1.5 hover:bg-gray-100 rounded-lg transition-colors", children: /* @__PURE__ */ jsx(X, { size: 16 }) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "p-5 space-y-3", children: [
          /* @__PURE__ */ jsx("label", { className: `block border rounded-xl p-4 cursor-pointer transition-all ${paymentMethod === "prepaid" ? "border-black bg-gray-50" : "border-gray-200 hover:border-gray-300"}`, children: /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
            /* @__PURE__ */ jsx("input", { type: "radio", name: "payment_method", value: "prepaid", checked: paymentMethod === "prepaid", onChange: () => {
              setPaymentMethod("prepaid");
              setPaymentModal(false);
            }, className: "w-4 h-4 text-black focus:ring-black" }),
            /* @__PURE__ */ jsxs("div", { className: "flex-1", children: [
              /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: "Online Payment / UPI / Cards" }),
              /* @__PURE__ */ jsx("p", { className: "text-xs text-green-600 font-medium mt-0.5", children: "Get extra discounts on prepaid orders" })
            ] })
          ] }) }),
          settings?.shipping_rules?.cod && /* @__PURE__ */ jsx("label", { className: `block border rounded-xl p-4 transition-all ${!isCodAvailable ? "opacity-50 cursor-not-allowed bg-gray-50 border-gray-100" : paymentMethod === "cod" ? "border-black bg-gray-50 cursor-pointer" : "border-gray-200 hover:border-gray-300 cursor-pointer"}`, children: /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
            /* @__PURE__ */ jsx("input", { type: "radio", name: "payment_method", value: "cod", disabled: !isCodAvailable, checked: paymentMethod === "cod", onChange: () => {
              if (isCodAvailable) {
                setPaymentMethod("cod");
                setPaymentModal(false);
              }
            }, className: "w-4 h-4 text-black focus:ring-black disabled:opacity-50" }),
            /* @__PURE__ */ jsxs("div", { className: "flex-1", children: [
              /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: "Cash on Delivery (COD)" }),
              !isCodAvailable ? /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 font-medium mt-0.5", children: [
                "Available for orders above ",
                formatPrice(codMinOrderAmount)
              ] }) : codUpfrontAmount > 0 ? /* @__PURE__ */ jsxs(Fragment, { children: [
                /* @__PURE__ */ jsxs("p", { className: "text-xs text-orange-500 font-medium mt-0.5", children: [
                  "Partial upfront payment of ",
                  formatPrice(codUpfrontAmount),
                  " required online to place COD order"
                ] }),
                (!codRule || codRule.upfront_refundable != "1") && /* @__PURE__ */ jsx("p", { className: "text-[11px] text-red-500 mt-1 italic font-semibold", children: "Note: Upfront shipping charges are non-refundable in the event of order cancellation or return." })
              ] }) : codApplicableCharge > 0 ? /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 font-medium mt-0.5", children: [
                "Extra ",
                formatPrice(codApplicableCharge),
                " charge applicable"
              ] }) : /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 font-medium mt-0.5", children: "Pay at your doorstep" })
            ] })
          ] }) })
        ] })
      ] })
    ] })
  ] });
}
const __vite_glob_0_7 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: CheckoutPage
}, Symbol.toStringTag, { value: "Module" }));
function ThankYouPage({ order }) {
  const { width, height } = useWindowSize();
  const [showConfetti, setShowConfetti] = useState(true);
  useEffect(() => {
    const timer = setTimeout(() => setShowConfetti(false), 5e3);
    return () => clearTimeout(timer);
  }, []);
  useEffect(() => {
    if (order && order.id) {
      const firedKey = `purchase_fired_${order.id}`;
      if (!sessionStorage.getItem(firedKey)) {
        trackPurchase(
          order.order_number || order.uuid,
          parseFloat(order.total_amount || "0"),
          order.items || []
        );
        sessionStorage.setItem(firedKey, "true");
      }
    }
  }, [order]);
  const formatPrice2 = (price) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 0
    }).format(price);
  };
  if (!order) return /* @__PURE__ */ jsx("div", { className: "min-h-screen flex items-center justify-center bg-gray-50", children: /* @__PURE__ */ jsx("div", { className: "w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin" }) });
  const safeSubtotal = order.items.reduce((acc, item) => acc + parseFloat(item.price || "0") * item.quantity, 0);
  const totalAmount = parseFloat(order.total_amount || "0");
  const shippingAmount = parseFloat(order.shipping_amount || "0");
  const discountAmount = parseFloat(order.discount_amount || "0");
  const taxAmount = parseFloat(order.tax_amount || "0");
  const isTaxIncluded = Math.abs(totalAmount + discountAmount - (safeSubtotal + shippingAmount)) < 0.1;
  let taxBreakdown = {};
  try {
    taxBreakdown = typeof order.tax_breakdown === "string" ? JSON.parse(order.tax_breakdown) : order.tax_breakdown || {};
  } catch (e) {
    taxBreakdown = {};
  }
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gray-50 font-sans py-12 md:py-20", children: [
    /* @__PURE__ */ jsx(Head, { title: "Order Confirmed | Dope Style" }),
    showConfetti && /* @__PURE__ */ jsx(
      Confetti,
      {
        width,
        height,
        recycle: false,
        numberOfPieces: 300,
        gravity: 0.15,
        colors: ["#000000", "#ffffff", "#4ade80", "#fbbf24", "#f87171"],
        style: { zIndex: 100 }
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: "max-w-3xl mx-auto px-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center mb-8", children: [
        /* @__PURE__ */ jsx(CheckCircle, { className: "w-16 h-16 text-emerald-500 mx-auto mb-4" }),
        /* @__PURE__ */ jsx("h1", { className: "text-3xl font-extrabold text-gray-900 mb-2", children: "Order Confirmed!" }),
        /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-lg", children: "Thank you for your purchase." })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8", children: [
        /* @__PURE__ */ jsx("div", { className: "p-6 md:p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center", children: /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("p", { className: "text-xs font-bold text-gray-400 uppercase tracking-widest mb-1", children: "Order Number" }),
          /* @__PURE__ */ jsx("p", { className: "text-base font-mono font-bold text-gray-900", children: order.order_number || order.uuid.split("-")[0] })
        ] }) }),
        /* @__PURE__ */ jsxs("div", { className: "p-6 md:p-8", children: [
          /* @__PURE__ */ jsxs("h2", { className: "text-lg font-bold text-gray-900 mb-6 flex items-center gap-2", children: [
            /* @__PURE__ */ jsx(Package, { className: "w-5 h-5 text-gray-400" }),
            "Items Ordered"
          ] }),
          /* @__PURE__ */ jsx("div", { className: "space-y-6 mb-8", children: order.items.map((item) => {
            const imgUrl = item.image_url || item.product?.featured_image || item.sku?.product?.featured_image;
            return /* @__PURE__ */ jsxs("div", { className: "flex gap-4", children: [
              /* @__PURE__ */ jsx("div", { className: "w-20 h-28 bg-gray-50 rounded-xl overflow-hidden shrink-0 border border-gray-100", children: imgUrl ? /* @__PURE__ */ jsx(
                "img",
                {
                  src: imgUrl,
                  alt: item.product_name || item.product?.name,
                  className: "w-full h-full object-cover object-top"
                }
              ) : /* @__PURE__ */ jsx("div", { className: "w-full h-full flex items-center justify-center text-[10px] text-gray-400 font-medium", children: "No Image" }) }),
              /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0 flex flex-col py-1", children: [
                /* @__PURE__ */ jsx("h3", { className: "text-sm font-bold text-gray-900 leading-tight mb-1", children: item.product_name || item.product?.name }),
                (item.variant_name || item.sku?.color || item.sku?.size) && /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-500 mb-2", children: item.variant_name || [
                  item.sku?.color?.name,
                  item.sku?.size?.name ? `Size: ${item.sku.size.name}` : ""
                ].filter(Boolean).join(" | ") }),
                /* @__PURE__ */ jsxs("div", { className: "text-sm text-gray-500 flex items-center justify-between mt-auto", children: [
                  /* @__PURE__ */ jsxs("span", { children: [
                    "Qty: ",
                    item.quantity
                  ] }),
                  /* @__PURE__ */ jsx("span", { className: "font-bold text-gray-900", children: formatPrice2(parseFloat(item.price || "0") * item.quantity) })
                ] })
              ] })
            ] }, item.id);
          }) }),
          /* @__PURE__ */ jsx("hr", { className: "border-gray-100 mb-8" }),
          /* @__PURE__ */ jsx("div", { className: "space-y-4 max-w-sm ml-auto", children: /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 sm:p-8", children: [
            /* @__PURE__ */ jsx("h2", { className: "text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4", children: "Order Summary" }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-2.5 text-sm", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600", children: [
                /* @__PURE__ */ jsx("span", { children: "MRP Total" }),
                /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: formatPrice2(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0)) })
              ] }),
              order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) > safeSubtotal && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                /* @__PURE__ */ jsx("span", { children: "Discount on MRP" }),
                /* @__PURE__ */ jsxs("span", { children: [
                  "−",
                  formatPrice2(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal)
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-600 font-medium", children: [
                /* @__PURE__ */ jsx("span", { children: "Cart Subtotal" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice2(safeSubtotal) })
              ] }),
              parseFloat(order.coupon_discount_amount || "0") > 0 || parseFloat(order.prepaid_discount_amount || "0") > 0 || parseFloat(order.gift_card_discount_amount || "0") > 0 ? /* @__PURE__ */ jsxs(Fragment, { children: [
                parseFloat(order.coupon_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                  /* @__PURE__ */ jsxs("span", { children: [
                    "Coupon Discount ",
                    order.coupon_code ? `(${order.coupon_code})` : ""
                  ] }),
                  /* @__PURE__ */ jsxs("span", { children: [
                    "−",
                    formatPrice2(parseFloat(order.coupon_discount_amount || "0"))
                  ] })
                ] }),
                parseFloat(order.prepaid_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                  /* @__PURE__ */ jsx("span", { children: "Prepaid Discount" }),
                  /* @__PURE__ */ jsxs("span", { children: [
                    "−",
                    formatPrice2(parseFloat(order.prepaid_discount_amount || "0"))
                  ] })
                ] }),
                parseFloat(order.gift_card_discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                  /* @__PURE__ */ jsx("span", { children: "Gift Card Applied" }),
                  /* @__PURE__ */ jsxs("span", { children: [
                    "−",
                    formatPrice2(parseFloat(order.gift_card_discount_amount || "0"))
                  ] })
                ] })
              ] }) : parseFloat(order.discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600", children: [
                /* @__PURE__ */ jsx("span", { children: "Coupon Discount" }),
                /* @__PURE__ */ jsxs("span", { children: [
                  "−",
                  formatPrice2(parseFloat(order.discount_amount || "0"))
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
                /* @__PURE__ */ jsx("span", { children: "Shipping" }),
                /* @__PURE__ */ jsx("span", { children: parseFloat(order.shipping_amount || "0") === 0 ? "Free" : formatPrice2(parseFloat(order.shipping_amount || "0")) })
              ] }),
              Object.keys(taxBreakdown).length > 0 ? Object.entries(taxBreakdown).map(([rate, amount]) => /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
                /* @__PURE__ */ jsxs("span", { children: [
                  "Tax @ ",
                  rate,
                  "% ",
                  isTaxIncluded ? "(Included)" : "(Excluded)"
                ] }),
                /* @__PURE__ */ jsx("span", { children: formatPrice2(amount) })
              ] }, rate)) : taxAmount > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500 text-xs", children: [
                /* @__PURE__ */ jsxs("span", { children: [
                  "Tax ",
                  isTaxIncluded ? "(Included)" : "(Excluded)"
                ] }),
                /* @__PURE__ */ jsx("span", { children: formatPrice2(taxAmount) })
              ] }),
              order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal + parseFloat(order.discount_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-green-600 font-medium pt-1", children: [
                /* @__PURE__ */ jsx("span", { children: "Total Savings" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice2(order.items.reduce((acc, item) => acc + parseFloat(item.sku?.mrp || item.price || "0") * item.quantity, 0) - safeSubtotal + parseFloat(order.discount_amount || "0")) })
              ] }),
              /* @__PURE__ */ jsx("div", { className: "h-px bg-gray-100 my-1" }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between font-bold text-gray-900 text-base", children: [
                /* @__PURE__ */ jsx("span", { children: "Total" }),
                /* @__PURE__ */ jsx("span", { children: formatPrice2(totalAmount) })
              ] }),
              parseFloat(order.upfront_amount || "0") > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-2 p-3 bg-orange-50 border border-orange-100 rounded-lg space-y-2", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-sm text-orange-800 font-semibold", children: [
                  /* @__PURE__ */ jsx("span", { children: "Upfront (Non Refundable)" }),
                  /* @__PURE__ */ jsx("span", { children: formatPrice2(parseFloat(order.upfront_amount)) })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-xs text-orange-700 font-bold", children: [
                  /* @__PURE__ */ jsx("span", { children: "Due on Delivery" }),
                  /* @__PURE__ */ jsx("span", { children: formatPrice2(totalAmount - parseFloat(order.upfront_amount)) })
                ] })
              ] })
            ] })
          ] }) })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 sm:grid-cols-2 gap-4", children: [
        /* @__PURE__ */ jsxs(
          Link,
          {
            href: "/orders",
            className: "w-full flex items-center justify-center gap-2 bg-white border border-gray-200 text-gray-900 font-bold py-3.5 px-6 rounded-xl hover:bg-gray-50 transition-colors shadow-sm",
            children: [
              /* @__PURE__ */ jsx(Truck, { className: "w-4 h-4" }),
              "Track Order"
            ]
          }
        ),
        /* @__PURE__ */ jsxs(
          Link,
          {
            href: "/shop",
            className: "w-full flex items-center justify-center gap-2 bg-black text-white font-bold py-3.5 px-6 rounded-xl hover:bg-gray-800 transition-colors shadow-sm",
            children: [
              "Continue Shopping ",
              /* @__PURE__ */ jsx(ArrowRight, { className: "w-4 h-4" })
            ]
          }
        )
      ] })
    ] })
  ] });
}
const __vite_glob_0_8 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ThankYouPage
}, Symbol.toStringTag, { value: "Module" }));
function HeroSlider({ data, sectionSettings }) {
  const id = useId();
  const sectionId = id.replace(/:/g, "");
  if (!data?.slides || data.slides.length === 0) return null;
  const heightClass = {
    auto: "",
    small: "h-[300px] md:h-[400px]",
    medium: "h-[400px] md:h-[600px]",
    large: "h-[500px] md:h-[700px] lg:h-[800px]",
    fullscreen: "h-screen"
  };
  const heightSetting = sectionSettings?.section_height || data.section_height || "large";
  const isAutoHeight = heightSetting === "auto";
  const selectedHeight = heightClass[heightSetting] ?? heightClass.large;
  const fitClass = { cover: "object-cover", contain: "object-contain", fill: "object-fill" };
  const selectedFit = fitClass[data.image_fit || "cover"] || "object-cover";
  return /* @__PURE__ */ jsxs("div", { className: `w-full relative group/hero ${selectedHeight}`, children: [
    /* @__PURE__ */ jsx(
      Swiper,
      {
        modules: [Navigation, Pagination, Autoplay],
        spaceBetween: 0,
        slidesPerView: 1,
        autoHeight: isAutoHeight,
        loop: true,
        navigation: {
          prevEl: `.prev-${sectionId}`,
          nextEl: `.next-${sectionId}`
        },
        pagination: {
          clickable: true,
          bulletActiveClass: "!bg-white !w-8",
          bulletClass: "swiper-pagination-bullet !bg-white/30 !opacity-100 !transition-all !duration-500 !rounded-full"
        },
        autoplay: { delay: 5e3, disableOnInteraction: false },
        className: `w-full ${isAutoHeight ? "[&_.swiper-wrapper]:!items-start" : "h-full"}`,
        children: data.slides.map((slide, index) => {
          const SlideContent = /* @__PURE__ */ jsxs("div", { className: `relative w-full ${isAutoHeight ? "" : "h-full"}`, children: [
            /* @__PURE__ */ jsx(
              "img",
              {
                src: slide.image || "/placeholder.jpg",
                alt: slide.title || "Banner",
                className: `w-full block ${isAutoHeight ? "h-auto object-cover" : "h-full " + selectedFit}`
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-r from-black/40 via-black/10 to-transparent flex items-center px-6 md:px-20 lg:px-32", children: /* @__PURE__ */ jsxs("div", { className: "max-w-3xl text-white", children: [
              /* @__PURE__ */ jsx("div", { className: "space-y-6 mb-10", children: slide.title && /* @__PURE__ */ jsx("h2", { className: "text-4xl md:text-8xl font-heading font-black leading-[0.95] animate-in fade-in slide-in-from-left-12 duration-1000", children: slide.title }) }),
              slide.subtitle && /* @__PURE__ */ jsx("p", { className: "text-base md:text-xl mb-12 text-white/80 font-medium leading-relaxed max-w-lg animate-in fade-in slide-in-from-left-12 duration-1000 delay-200", children: slide.subtitle })
            ] }) })
          ] });
          return /* @__PURE__ */ jsx(SwiperSlide, { className: `relative w-full ${isAutoHeight ? "" : "h-full"}`, children: slide.link ? /* @__PURE__ */ jsx(Link, { href: slide.link, className: `block w-full ${isAutoHeight ? "" : "h-full"}`, children: SlideContent }) : SlideContent }, index);
        })
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: "hidden md:block", children: [
      /* @__PURE__ */ jsx("button", { className: `prev-${sectionId} absolute left-8 top-1/2 -translate-y-1/2 z-20 w-14 h-14 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white opacity-0 group-hover/hero:opacity-100 transition-all duration-500 hover:bg-white hover:text-black shadow-2xl disabled:hidden`, children: /* @__PURE__ */ jsx("svg", { className: "w-6 h-6", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2.5", d: "M15 19l-7-7 7-7" }) }) }),
      /* @__PURE__ */ jsx("button", { className: `next-${sectionId} absolute right-8 top-1/2 -translate-y-1/2 z-20 w-14 h-14 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white opacity-0 group-hover/hero:opacity-100 transition-all duration-500 hover:bg-white hover:text-black shadow-2xl disabled:hidden`, children: /* @__PURE__ */ jsx("svg", { className: "w-6 h-6", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2.5", d: "M9 5l7 7-7 7" }) }) })
    ] })
  ] });
}
function ProductCarousel({ data, isFluid, settings }) {
  const id = useId();
  const sectionId = id.replace(/:/g, "");
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    const apiUrl = "/api";
    let fetchUrl = `${apiUrl}/products?collection=${data.collection || ""}&limit=${data.limit || 8}`;
    fetch(fetchUrl).then((res) => res.json()).then((data2) => {
      setProducts(data2.data || []);
      setLoading(false);
    }).catch((err) => {
      console.error("Failed to fetch products", err);
      setLoading(false);
    });
  }, [data.collection]);
  if (loading) return /* @__PURE__ */ jsx("div", { className: "py-12 text-center", children: "Loading products..." });
  if (products.length === 0) return null;
  return /* @__PURE__ */ jsx("section", { className: "bg-white overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: isFluid ? "w-full px-4 md:px-8" : "container mx-auto px-4", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-end justify-between mb-10", children: [
      /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
        data.title && /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-4xl font-heading font-bold text-gray-900 tracking-tight", children: data.title }),
        data.subtitle && /* @__PURE__ */ jsx("p", { className: "text-sm md:text-base text-gray-500 font-medium max-w-xl", children: data.subtitle })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "hidden md:flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("button", { className: `prev-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M15 19l-7-7 7-7" }) }) }),
        /* @__PURE__ */ jsx("button", { className: `next-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 5l7 7-7 7" }) }) })
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx(
      Swiper,
      {
        modules: [Navigation],
        navigation: {
          prevEl: `.prev-${sectionId}`,
          nextEl: `.next-${sectionId}`
        },
        spaceBetween: 24,
        slidesPerView: 1.2,
        breakpoints: {
          640: { slidesPerView: 2 },
          768: { slidesPerView: 3 },
          1024: { slidesPerView: 4 }
        },
        className: "pb-8",
        children: products.slice(0, data.limit || 8).map((product) => {
          const rawImage = product.image || (product.media && product.media.length > 0 ? product.media[0].original_url : null);
          const cleanImage = rawImage;
          const rawHoverImage = product.hover_image || null;
          const cleanHoverImage = rawHoverImage;
          const mappedProduct = {
            id: product.id,
            name: product.name,
            slug: product.slug,
            brand: product.brand || null,
            price: product.price || 0,
            price_formatted: product.formatted_price || `$${product.price}`,
            mrp: product.mrp || product.price || 0,
            discount_percentage: product.discount_percentage || 0,
            image: cleanImage,
            hover_image: cleanHoverImage,
            category: product.category?.name || "Apparel",
            is_new: product.is_new || false
          };
          return /* @__PURE__ */ jsx(SwiperSlide, { children: /* @__PURE__ */ jsx("div", { className: "px-2 pt-2 pb-6", children: /* @__PURE__ */ jsx(ProductCard, { product: mappedProduct }) }) }, product.id);
        })
      }
    ) })
  ] }) });
}
function TextBlock({ data, isFluid }) {
  if (!data.content) return null;
  return /* @__PURE__ */ jsx("section", { className: "bg-white", children: /* @__PURE__ */ jsx(
    "div",
    {
      className: `${isFluid ? "w-full" : "container mx-auto"} px-4 prose max-w-4xl [&>*:first-child]:mt-0 [&>*:last-child]:mb-0`,
      dangerouslySetInnerHTML: { __html: data.content }
    }
  ) });
}
function ImageGrid({ data, isFluid }) {
  if (!data?.images || data.images.length === 0) return null;
  const columnClassMap = {
    "2": "grid-cols-2 lg:grid-cols-2",
    "3": "grid-cols-2 lg:grid-cols-3",
    "4": "grid-cols-2 lg:grid-cols-4",
    "5": "grid-cols-2 md:grid-cols-3 lg:grid-cols-5",
    "6": "grid-cols-2 md:grid-cols-3 lg:grid-cols-6"
  };
  const gridsClass = columnClassMap[data.columns || "4"] || "grid-cols-2 lg:grid-cols-4";
  const enableHoverZoom = data.hover_animation !== "none";
  const containerClass = isFluid ? "px-4 md:px-8 w-full" : "px-4 md:px-8 max-w-7xl mx-auto";
  return /* @__PURE__ */ jsxs("section", { className: containerClass, children: [
    data.title && /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row justify-between items-end mb-8 md:mb-12 gap-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "max-w-2xl", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-4xl font-bold tracking-tight text-gray-900", children: data.title }),
        data.text && /* @__PURE__ */ jsx("p", { className: "mt-4 text-gray-500 text-lg", children: data.text })
      ] }),
      data.cta_text && data.cta_link && /* @__PURE__ */ jsx(Link, { href: data.cta_link, className: "inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-[var(--secondary)] bg-[var(--primary)] hover:opacity-90 transition-opacity shrink-0", children: data.cta_text })
    ] }),
    /* @__PURE__ */ jsx("div", { className: `grid gap-4 md:gap-6 ${gridsClass}`, children: data.images.map((item, idx) => {
      const ctaStyle = item.cta_style || "pill_overlay";
      const targetAttr = item.target === "_blank" ? "_blank" : "_self";
      const relAttr = item.target === "_blank" ? "noopener noreferrer" : void 0;
      const hasLink = !!item.link;
      const imageBlock = /* @__PURE__ */ jsxs("div", { className: "relative aspect-square md:aspect-[4/5] overflow-hidden rounded-2xl group/card cursor-pointer bg-gray-100 shadow-sm border border-gray-100", children: [
        /* @__PURE__ */ jsx(
          "img",
          {
            src: item.image,
            alt: item.alt || `Grid image ${idx + 1}`,
            className: `w-full h-full object-cover ${enableHoverZoom ? "group-hover/card:scale-105 transition-transform duration-500" : ""}`
          }
        ),
        item.cta_text && ctaStyle === "pill_overlay" && /* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 via-black/10 to-transparent flex justify-center items-end opacity-0 group-hover/card:opacity-100 transition-opacity duration-300", children: /* @__PURE__ */ jsx("span", { className: "inline-block px-5 py-2 bg-[var(--primary)] text-[var(--secondary)] font-bold rounded-full text-sm shadow-lg", children: item.cta_text }) }),
        item.cta_text && ctaStyle === "bar_overlay" && /* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 flex items-center justify-center py-3 px-4 bg-[var(--primary)]/90 backdrop-blur-sm", children: /* @__PURE__ */ jsx("span", { className: "text-[var(--secondary)] font-bold text-sm tracking-wide", children: item.cta_text }) })
      ] });
      const wrapped = (children) => hasLink ? /* @__PURE__ */ jsx(Link, { href: item.link, className: "block", target: targetAttr, rel: relAttr, children }) : /* @__PURE__ */ jsx("div", { className: "block", children });
      if (item.cta_text && (ctaStyle === "below_button" || ctaStyle === "underline_text")) {
        const belowEl = ctaStyle === "below_button" ? /* @__PURE__ */ jsx("div", { className: "mt-3", children: hasLink ? /* @__PURE__ */ jsx(
          Link,
          {
            href: item.link,
            target: targetAttr,
            rel: relAttr,
            className: "block w-full text-center py-2.5 px-4 bg-[var(--primary)] text-[var(--secondary)] font-bold rounded-xl text-sm hover:opacity-90 transition-opacity",
            children: item.cta_text
          }
        ) : /* @__PURE__ */ jsx("span", { className: "block w-full text-center py-2.5 px-4 bg-[var(--primary)] text-[var(--secondary)] font-bold rounded-xl text-sm", children: item.cta_text }) }) : /* @__PURE__ */ jsx("div", { className: "mt-2 text-center", children: hasLink ? /* @__PURE__ */ jsx(
          Link,
          {
            href: item.link,
            target: targetAttr,
            rel: relAttr,
            className: "text-sm font-semibold text-[var(--primary)] underline underline-offset-4 hover:opacity-70 transition-opacity",
            children: item.cta_text
          }
        ) : /* @__PURE__ */ jsx("span", { className: "text-sm font-semibold text-[var(--primary)] underline underline-offset-4", children: item.cta_text }) });
        return /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
          wrapped(imageBlock),
          belowEl
        ] }, idx);
      }
      return /* @__PURE__ */ jsx("div", { children: wrapped(imageBlock) }, idx);
    }) })
  ] });
}
function ImageBanner({ data, sectionSettings }) {
  if (!data?.image) return null;
  const fitClass = {
    cover: "object-cover",
    contain: "object-contain",
    fill: "object-fill",
    auto: "object-none"
  };
  const selectedFit = fitClass[data.object_fit || "cover"] || "object-cover";
  const positionClass = {
    center: "absolute inset-0 flex items-center justify-center text-center px-4",
    bottom: "absolute inset-x-0 bottom-0 p-8 pt-24 text-center bg-gradient-to-t from-black/80 to-transparent",
    top: "absolute inset-x-0 top-0 p-8 pb-24 text-center bg-gradient-to-b from-black/80 to-transparent",
    left: "absolute inset-y-0 left-0 p-8 pr-24 flex items-center justify-start text-left bg-gradient-to-r from-black/80 to-transparent w-full md:w-2/3 lg:w-1/2",
    right: "absolute inset-y-0 right-0 p-8 pl-24 flex items-center justify-end text-right bg-gradient-to-l from-black/80 to-transparent w-full md:w-2/3 lg:w-1/2",
    below: "relative p-8 text-center bg-white"
  };
  const selectedPosition = positionClass[data.text_position || "center"] || positionClass.center;
  const heightClass = {
    auto: "",
    small: "h-[300px] md:h-[400px]",
    medium: "h-[400px] md:h-[600px]",
    large: "h-[500px] md:h-[700px] lg:h-[800px]",
    fullscreen: "h-screen"
  };
  const defaultHeight = data.text_position === "below" ? "small" : "large";
  const heightSetting = sectionSettings?.section_height || data.section_height || defaultHeight;
  const isAutoHeight = heightSetting === "auto";
  const selectedHeight = heightClass[heightSetting] ?? heightClass[defaultHeight];
  const customTextColor = sectionSettings?.text_color ? sectionSettings.text_color : void 0;
  const isExternal = data.link_url && (data.link_url.startsWith("http://") || data.link_url.startsWith("https://"));
  const LinkWrapper = ({ children }) => {
    if (!data.link_url) return /* @__PURE__ */ jsx(Fragment, { children });
    if (isExternal) {
      return /* @__PURE__ */ jsx("a", { href: data.link_url, target: "_blank", rel: "noopener noreferrer", className: "block w-full h-full cursor-pointer group", children });
    }
    return /* @__PURE__ */ jsx(Link, { href: data.link_url, className: "block w-full h-full cursor-pointer group", children });
  };
  return /* @__PURE__ */ jsx("section", { className: "w-full relative", children: /* @__PURE__ */ jsxs(LinkWrapper, { children: [
    /* @__PURE__ */ jsxs("div", { className: `w-full relative ${selectedHeight}`, children: [
      /* @__PURE__ */ jsx(
        "img",
        {
          src: data.image,
          alt: "Banner",
          className: `w-full block ${isAutoHeight ? "h-auto object-cover" : "h-full " + selectedFit}`
        }
      ),
      data.text && data.text_position !== "below" && /* @__PURE__ */ jsx("div", { className: selectedPosition, children: /* @__PURE__ */ jsx("div", { className: "max-w-3xl mx-auto w-full", children: /* @__PURE__ */ jsx(
        "p",
        {
          className: "text-3xl md:text-5xl font-bold leading-tight drop-shadow-lg",
          style: { color: customTextColor || "white" },
          children: data.text
        }
      ) }) })
    ] }),
    data.text && data.text_position === "below" && /* @__PURE__ */ jsx("div", { className: selectedPosition, children: /* @__PURE__ */ jsx("div", { className: "max-w-4xl mx-auto", children: /* @__PURE__ */ jsx(
      "p",
      {
        className: "text-2xl md:text-3xl font-medium leading-relaxed",
        style: { color: customTextColor || "#111827" },
        children: data.text
      }
    ) }) })
  ] }) });
}
function HorizontalScrollCards({ data, isFluid }) {
  const id = useId();
  const sectionId = id.replace(/:/g, "");
  if (!data?.cards || data.cards.length === 0) return null;
  const enableHoverZoom = data.hover_animation !== "none";
  return /* @__PURE__ */ jsx("section", { className: "bg-white overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: isFluid ? "w-full px-4 md:px-8" : "container mx-auto px-4", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-end justify-between mb-10", children: [
      /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
        data.title && /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-4xl font-heading font-bold text-gray-900 tracking-tight", children: data.title }),
        data.subtitle && /* @__PURE__ */ jsx("p", { className: "text-sm md:text-base text-gray-500 font-medium max-w-xl", children: data.subtitle })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "hidden md:flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("button", { className: `prev-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M15 19l-7-7 7-7" }) }) }),
        /* @__PURE__ */ jsx("button", { className: `next-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 5l7 7-7 7" }) }) })
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx(
      Swiper,
      {
        modules: [Navigation],
        navigation: {
          prevEl: `.prev-${sectionId}`,
          nextEl: `.next-${sectionId}`
        },
        spaceBetween: 16,
        slidesPerView: 2.2,
        breakpoints: {
          640: { slidesPerView: 3.2 },
          768: { slidesPerView: 4 },
          1024: { slidesPerView: 5 }
        },
        className: "!overflow-visible",
        children: data.cards.map((card, idx) => /* @__PURE__ */ jsx(SwiperSlide, { children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col group/card bg-transparent pb-4", children: [
          card.image && /* @__PURE__ */ jsx("div", { className: "w-full overflow-hidden bg-gray-100 rounded-xl", style: { aspectRatio: "3/4" }, children: /* @__PURE__ */ jsx(
            "img",
            {
              src: card.image,
              alt: card.headline || "Card image",
              className: `w-full h-full object-cover ${enableHoverZoom ? "group-hover/card:scale-105 transition-transform duration-500" : ""}`
            }
          ) }),
          /* @__PURE__ */ jsxs("div", { className: "pt-3 flex flex-col gap-1 flex-1", children: [
            card.headline && /* @__PURE__ */ jsx("h3", { className: "text-xs font-extrabold tracking-tight text-gray-900 group-hover/card:text-black transition-colors uppercase", children: card.headline }),
            card.paragraph && /* @__PURE__ */ jsx("p", { className: "text-[11px] leading-relaxed text-gray-500 line-clamp-2", children: card.paragraph }),
            card.cta_text && card.cta_link && /* @__PURE__ */ jsx("div", { className: "mt-1", children: /* @__PURE__ */ jsx(
              Link,
              {
                href: card.cta_link,
                className: "text-[10px] font-bold uppercase tracking-widest text-black border-b border-black/10 hover:border-black transition-all pb-0.5",
                children: card.cta_text
              }
            ) })
          ] })
        ] }) }, idx))
      }
    ) })
  ] }) });
}
function ProductHorizontalScroll({ data, isFluid, settings }) {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const scrollContainerRef = useRef(null);
  const scroll = (direction) => {
    if (scrollContainerRef.current) {
      const scrollAmount = window.innerWidth < 768 ? window.innerWidth * 0.7 : 300;
      scrollContainerRef.current.scrollBy({
        left: direction === "left" ? -scrollAmount : scrollAmount,
        behavior: "smooth"
      });
    }
  };
  useEffect(() => {
    let isMounted = true;
    const fetchProducts = async () => {
      try {
        const apiUrl = "/api";
        let fetchUrl = `${apiUrl}/products?collection=${data.collection || ""}&limit=${data.limit || 8}`;
        if (data.product_slugs && data.product_slugs.length > 0) {
          fetchUrl = `${apiUrl}/products?slugs=${data.product_slugs.join(",")}`;
        }
        const res = await fetch(fetchUrl, {
          cache: "no-store"
        });
        const responseData = await res.json();
        if (isMounted) {
          if (Array.isArray(responseData)) {
            setProducts(responseData);
          } else if (responseData && Array.isArray(responseData.data)) {
            setProducts(responseData.data);
          }
          setLoading(false);
        }
      } catch (error) {
        console.error("Failed to fetch products for horizontal scroll output:", error);
        if (isMounted) setLoading(false);
      }
    };
    fetchProducts();
    return () => {
      isMounted = false;
    };
  }, [data.collection, data.limit, data.product_slugs?.join(",")]);
  if (loading) {
    return /* @__PURE__ */ jsxs("section", { className: `px-4 md:px-8${isFluid ? " w-full" : " max-w-7xl mx-auto"}`, children: [
      /* @__PURE__ */ jsx("div", { className: "h-10 w-64 bg-gray-200 animate-pulse rounded-lg mb-8" }),
      /* @__PURE__ */ jsxs("div", { className: "flex space-x-6 overflow-x-hidden", children: [
        /* @__PURE__ */ jsx("div", { className: "h-80 w-64 bg-gray-100 animate-pulse rounded-2xl shrink-0" }),
        /* @__PURE__ */ jsx("div", { className: "h-80 w-64 bg-gray-100 animate-pulse rounded-2xl shrink-0" })
      ] })
    ] });
  }
  if (products.length === 0) return null;
  const getDesktopGridClass = (length) => {
    if (length === 1) return "md:grid md:grid-cols-1 md:space-x-0 md:gap-6 md:overflow-visible";
    if (length === 2) return "md:grid md:grid-cols-2 md:space-x-0 md:gap-6 md:overflow-visible";
    if (length === 3) return "md:grid md:grid-cols-3 md:space-x-0 md:gap-6 md:overflow-visible";
    if (length === 4) return "md:grid md:grid-cols-4 md:space-x-0 md:gap-6 md:overflow-visible";
    return "";
  };
  return /* @__PURE__ */ jsxs("section", { className: `px-4 md:px-8${isFluid ? " w-full" : " max-w-7xl mx-auto"}`, children: [
    data.title && /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-end mb-8 md:mb-10 px-2", children: [
      /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-4xl font-bold tracking-tight", style: { color: settings?.text_color || "#111827" }, children: data.title }),
      /* @__PURE__ */ jsx(
        Link,
        {
          href: data.collection && data.collection !== "undefined" ? `/collections/${data.collection}` : "/shop",
          className: "hidden md:flex font-bold uppercase tracking-widest text-xs hover:opacity-70 transition-opacity",
          style: { color: settings?.primary_color || "#000" },
          children: "View All"
        }
      )
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "relative group/carousel", children: [
      /* @__PURE__ */ jsx(
        "button",
        {
          onClick: () => scroll("left"),
          className: `hidden md:flex absolute -left-4 top-[35%] -translate-y-1/2 z-10 w-12 h-12 rounded-full bg-white border border-gray-200 shadow-xl items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 hover:bg-gray-50 text-gray-800 ${products.length <= 4 ? "!hidden" : ""}`,
          "aria-label": "Scroll left",
          children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2.5, d: "M15 19l-7-7 7-7" }) })
        }
      ),
      /* @__PURE__ */ jsx(
        "button",
        {
          onClick: () => scroll("right"),
          className: `hidden md:flex absolute -right-4 top-[35%] -translate-y-1/2 z-10 w-12 h-12 rounded-full bg-white border border-gray-200 shadow-xl items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 hover:bg-gray-50 text-gray-800 ${products.length <= 4 ? "!hidden" : ""}`,
          "aria-label": "Scroll right",
          children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2.5, d: "M9 5l7 7-7 7" }) })
        }
      ),
      /* @__PURE__ */ jsx(
        "div",
        {
          ref: scrollContainerRef,
          className: `flex overflow-x-auto pb-4 space-x-6 snap-x snap-mandatory px-2 -mx-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] ${getDesktopGridClass(products.length)}`,
          children: products.map((product) => {
            const rawImage = product.image || product.thumbnail || (product.media && product.media.length > 0 ? product.media[0].original_url : null);
            const cleanImage = rawImage;
            const rawHoverImage = product.hover_image || null;
            const cleanHoverImage = rawHoverImage;
            const mappedProduct = {
              id: product.id,
              name: product.name,
              slug: product.slug,
              brand: product.brand || null,
              price: product.price || 0,
              price_formatted: product.formatted_price || `$${Number(product.price).toFixed(2)}`,
              mrp: product.compare_at_price || product.mrp || product.price || 0,
              discount_percentage: product.discount_percentage || 0,
              image: cleanImage,
              hover_image: cleanHoverImage,
              category: typeof product.category === "object" ? product.category?.name : product.category || "Apparel",
              is_new: product.is_new || false,
              colors: product.colors?.map((c) => ({ name: c.name, hex: c.hex || c.meta, meta: c.hex || c.meta })) || [],
              coupon_price: product.coupon_price || null
            };
            return /* @__PURE__ */ jsx(
              "div",
              {
                className: `snap-start shrink-0 w-[70vw] ${products.length <= 4 ? "md:w-full" : "md:w-[280px]"}`,
                children: /* @__PURE__ */ jsx(ProductCard, { product: mappedProduct })
              },
              product.id
            );
          })
        }
      ),
      (data.title || data.collection) && /* @__PURE__ */ jsx("div", { className: "mt-8 md:hidden px-2", children: /* @__PURE__ */ jsx(
        Link,
        {
          href: data.collection && data.collection !== "undefined" ? `/collections/${data.collection}` : "/shop",
          className: "flex w-full justify-center px-6 py-3 border rounded-xl font-bold transition-all",
          style: settings?.primary_color ? { borderColor: settings.primary_color, color: settings.primary_color } : { borderColor: "#d1d5db", color: "#374151" },
          children: "View All"
        }
      ) })
    ] })
  ] });
}
function ImageProductCarousel({ data, isFluid, settings, sectionTextColor }) {
  const sectionId = useId().replace(/:/g, "");
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    let isMounted = true;
    const fetchProducts = async () => {
      try {
        if (!data.product_slugs || data.product_slugs.length === 0) {
          setLoading(false);
          return;
        }
        const apiUrl = "/api";
        const res = await fetch(`${apiUrl}/products?slugs=${data.product_slugs.join(",")}`, {
          cache: "no-store"
        });
        const responseData = await res.json();
        if (isMounted) {
          if (Array.isArray(responseData)) {
            setProducts(responseData);
          } else if (responseData && Array.isArray(responseData.data)) {
            setProducts(responseData.data);
          }
          setLoading(false);
        }
      } catch (error) {
        console.error("Failed to fetch products for image product carousel:", error);
        if (isMounted) setLoading(false);
      }
    };
    fetchProducts();
    return () => {
      isMounted = false;
    };
  }, [data.product_slugs?.join(",")]);
  if (loading) {
    return /* @__PURE__ */ jsx("section", { className: `px-4 md:px-8${isFluid ? " w-full" : " max-w-7xl mx-auto"}`, children: /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 min-h-[500px]", children: [
      /* @__PURE__ */ jsx("div", { className: "bg-gray-100 animate-pulse rounded-2xl w-full h-[300px] lg:h-full" }),
      /* @__PURE__ */ jsx("div", { className: "bg-gray-100 animate-pulse rounded-2xl w-full h-[400px] lg:h-full" })
    ] }) });
  }
  if (!data.image && products.length === 0) return null;
  const objectFitClass = data.object_fit === "contain" ? "object-contain" : data.object_fit === "none" ? "object-none" : "object-cover";
  return /* @__PURE__ */ jsx("section", { className: `px-4 md:px-8${isFluid ? " w-full" : " max-w-7xl mx-auto"} overflow-hidden`, children: /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center", children: [
    /* @__PURE__ */ jsx("div", { className: `w-full relative rounded-3xl overflow-hidden bg-gray-50 flex items-center justify-center ${data.object_fit === "contain" ? "h-auto lg:h-full lg:min-h-[600px]" : "h-full min-h-[400px] lg:min-h-[600px]"}`, children: data.image ? /* @__PURE__ */ jsx(
      "img",
      {
        src: data.image,
        alt: "Featured",
        className: data.object_fit === "contain" ? "w-full h-auto block lg:absolute lg:inset-0 lg:h-full lg:object-contain" : `absolute inset-0 w-full h-full ${objectFitClass}`
      }
    ) : /* @__PURE__ */ jsx("span", { className: "text-gray-400 font-medium", children: "No Image Provided" }) }),
    /* @__PURE__ */ jsxs("div", { className: "w-full relative px-2", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-8", children: [
        /* @__PURE__ */ jsx(
          "h3",
          {
            className: `text-2xl font-bold tracking-tight ${sectionTextColor || data.text_color || settings?.text_color ? "" : "text-gray-900"}`,
            style: sectionTextColor || data.text_color || settings?.text_color ? { color: sectionTextColor || data.text_color || settings?.text_color } : void 0,
            children: "Featured Style"
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-2", children: [
          /* @__PURE__ */ jsx("button", { className: `prev-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white hover:border-black transition-all`, children: /* @__PURE__ */ jsx("svg", { className: "w-4 h-4", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M15 19l-7-7 7-7" }) }) }),
          /* @__PURE__ */ jsx("button", { className: `next-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white hover:border-black transition-all`, children: /* @__PURE__ */ jsx("svg", { className: "w-4 h-4", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 5l7 7-7 7" }) }) })
        ] })
      ] }),
      products.length > 0 ? /* @__PURE__ */ jsx(
        Swiper,
        {
          modules: [Navigation, Pagination],
          navigation: {
            prevEl: `.prev-${sectionId}`,
            nextEl: `.next-${sectionId}`
          },
          pagination: { clickable: true },
          spaceBetween: 30,
          slidesPerView: 1,
          loop: true,
          className: "pb-12",
          children: products.map((product) => {
            const rawImage = product.image || product.thumbnail || (product.media && product.media.length > 0 ? product.media[0].original_url : null);
            const cleanImage = rawImage;
            const rawHoverImage = product.hover_image || null;
            const cleanHoverImage = rawHoverImage;
            const mappedProduct = {
              id: product.id,
              name: product.name,
              slug: product.slug,
              brand: product.brand || null,
              price: product.price || 0,
              price_formatted: product.formatted_price || `$${Number(product.price).toFixed(2)}`,
              mrp: product.compare_at_price || product.mrp || product.price || 0,
              discount_percentage: product.discount_percentage || 0,
              image: cleanImage,
              hover_image: cleanHoverImage,
              category: typeof product.category === "object" ? product.category?.name : product.category || "Apparel",
              is_new: product.is_new || false
            };
            return /* @__PURE__ */ jsx(SwiperSlide, { children: /* @__PURE__ */ jsx("div", { className: "px-4 py-8 max-w-full sm:max-w-md mx-auto", children: /* @__PURE__ */ jsx(ProductCard, { product: mappedProduct }) }) }, product.id);
          })
        }
      ) : /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center p-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200", children: /* @__PURE__ */ jsx("span", { className: "text-gray-500 font-medium", children: "No products selected" }) })
    ] })
  ] }) });
}
function AnnouncementMarquee({ data, isFluid, sectionBg }) {
  if (!data?.items || data.items.length === 0) return null;
  const bg = sectionBg || data.bg_color || "#000000";
  const textColor = data.text_color || "#ffffff";
  const secondsPerItem = data.speed === "slow" ? 15 : data.speed === "fast" ? 5 : 10;
  const repeatedItems = [...data.items, ...data.items, ...data.items, ...data.items];
  const duration = `${repeatedItems.length * secondsPerItem}s`;
  const MarqueeBlock = () => /* @__PURE__ */ jsx("div", { className: "flex min-w-full shrink-0 items-center justify-around", style: { animation: `marquee-scroll ${duration} linear infinite` }, children: repeatedItems.map((item, idx) => /* @__PURE__ */ jsxs("span", { className: "inline-flex items-center shrink-0", children: [
    item.link ? /* @__PURE__ */ jsx("a", { href: item.link, className: "text-sm font-semibold tracking-wide hover:opacity-70 transition-opacity px-8", children: item.text }) : /* @__PURE__ */ jsx("span", { className: "text-sm font-semibold tracking-wide px-8", children: item.text }),
    /* @__PURE__ */ jsx("span", { className: "opacity-30 text-xs", children: "·" })
  ] }, idx)) });
  return /* @__PURE__ */ jsxs("div", { className: "w-full flex overflow-hidden py-3", style: { backgroundColor: bg, color: textColor }, children: [
    /* @__PURE__ */ jsx(MarqueeBlock, {}),
    /* @__PURE__ */ jsx(MarqueeBlock, {}),
    /* @__PURE__ */ jsx("style", { children: `
                @keyframes marquee-scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-100%); }
                }
            ` })
  ] });
}
function FeatureHighlights({ data, isFluid, sectionBg }) {
  if (!data?.items || data.items.length === 0) return null;
  const containerClass = isFluid ? "w-full px-4 md:px-8" : "max-w-7xl mx-auto px-4 md:px-8";
  const count = data.items.length;
  const gridCols = count <= 2 ? "grid-cols-2" : count === 3 ? "grid-cols-3" : "grid-cols-2 md:grid-cols-4";
  const textColor = data.text_color || "#000000";
  const labelColor = data.text_color ? textColor : void 0;
  const descColor = data.text_color ? textColor : void 0;
  return /* @__PURE__ */ jsx("section", { style: { backgroundColor: sectionBg || "#ffffff" }, children: /* @__PURE__ */ jsx("div", { className: containerClass, children: /* @__PURE__ */ jsx("div", { className: `grid ${gridCols} gap-6 md:gap-10`, children: data.items.map((item, idx) => /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center text-center gap-3", children: [
    /* @__PURE__ */ jsx("span", { className: "text-3xl md:text-4xl leading-none", children: item.icon }),
    /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("p", { className: `font-bold text-sm md:text-base ${labelColor ? "" : "text-gray-900"}`, style: labelColor ? { color: labelColor } : void 0, children: item.label }),
      (item.description || item.desc) && /* @__PURE__ */ jsx("p", { className: `text-xs md:text-sm mt-1 leading-relaxed ${descColor ? "opacity-80" : "text-gray-500"}`, style: descColor ? { color: descColor } : void 0, children: item.description || item.desc })
    ] })
  ] }, idx)) }) }) });
}
function CategoryGrid({ data, isFluid, sectionBg }) {
  const items = data?.categories || data?.items || [];
  if (items.length === 0) return null;
  const containerClass = isFluid ? "w-full px-4 md:px-8" : "max-w-7xl mx-auto px-4 md:px-8";
  const colMap = { "2": "grid-cols-2", "3": "grid-cols-2 md:grid-cols-3", "4": "grid-cols-2 md:grid-cols-4" };
  const colClass = colMap[data.columns || "4"] || "grid-cols-2 md:grid-cols-4";
  return /* @__PURE__ */ jsx("section", { className: "", style: { backgroundColor: sectionBg || "" }, children: /* @__PURE__ */ jsxs("div", { className: containerClass, children: [
    data.title && /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-4xl font-bold tracking-tight text-gray-900 mb-8 md:mb-12", children: data.title }),
    /* @__PURE__ */ jsx("div", { className: `grid gap-4 md:gap-6 ${colClass}`, children: items.map((item, idx) => /* @__PURE__ */ jsxs(
      Link,
      {
        href: item.link || "#",
        className: "group relative overflow-hidden rounded-2xl aspect-[3/4] bg-gray-100 block",
        children: [
          item.image && /* @__PURE__ */ jsx(
            "img",
            {
              src: item.image,
              alt: item.name || `Category ${idx + 1}`,
              className: "w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            }
          ),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent p-5 md:p-6", children: /* @__PURE__ */ jsx("p", { className: "text-white font-bold text-base md:text-xl tracking-tight", children: item.name }) })
        ]
      },
      idx
    )) })
  ] }) });
}
function SplitBanner({ data, isFluid, sectionBg }) {
  if (!data?.image && !data?.title) return null;
  const imageOnRight = data.image_side === "right";
  const fitClass = data.object_fit === "contain" ? "object-contain" : data.object_fit === "none" ? "object-none" : "object-cover";
  const textBg = sectionBg || data.text_bg || data.text_bg_color || "#ffffff";
  const textSide = /* @__PURE__ */ jsxs(
    "div",
    {
      className: "flex flex-col justify-center px-8 md:px-16 py-16 md:py-20",
      style: { backgroundColor: textBg },
      children: [
        data.badge && /* @__PURE__ */ jsx("span", { className: "inline-block text-xs font-bold uppercase tracking-widest text-[var(--primary)] border border-[var(--primary)]/30 px-3 py-1 rounded-full mb-6 w-fit", children: data.badge }),
        data.title && /* @__PURE__ */ jsx("h2", { className: "text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-gray-900 leading-tight mb-4", children: data.title }),
        data.subtitle && /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-lg leading-relaxed mb-8 max-w-md", children: data.subtitle }),
        data.cta_text && data.cta_link && /* @__PURE__ */ jsx(
          Link,
          {
            href: data.cta_link,
            className: "inline-flex items-center justify-center w-fit px-8 py-4 bg-[var(--primary)] text-[var(--secondary)] font-bold rounded-full hover:opacity-90 transition-opacity",
            children: data.cta_text
          }
        )
      ]
    }
  );
  const imageSide = /* @__PURE__ */ jsx("div", { className: "relative min-h-[380px] md:min-h-0 w-full", children: data.image ? /* @__PURE__ */ jsx("img", { src: data.image, alt: data.title || "Banner", className: `absolute inset-0 w-full h-full ${fitClass}` }) : /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gray-100" }) });
  return /* @__PURE__ */ jsx("section", { className: "w-full overflow-hidden", children: /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 min-h-[500px] md:min-h-[580px]", children: imageOnRight ? /* @__PURE__ */ jsxs(Fragment, { children: [
    textSide,
    imageSide
  ] }) : /* @__PURE__ */ jsxs(Fragment, { children: [
    imageSide,
    textSide
  ] }) }) });
}
function NewsletterSignup({ data, isFluid, sectionBg }) {
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const isDark = data?.bg_style === "dark";
  const hasBgImage = data?.bg_style === "image" && data?.bg_image;
  const [error, setError] = useState("");
  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email) return;
    setLoading(true);
    setError("");
    try {
      await api.post("/api/subscribe", { email });
      setSubmitted(true);
    } catch (err) {
      if (err.response?.status === 422) {
        if (err.response.data.errors?.email?.[0].includes("already")) {
          setError("This email is already subscribed.");
        } else {
          setError(err.response.data.errors?.email?.[0] || "Invalid email address.");
        }
      } else {
        setError("Something went wrong. Please try again.");
      }
    } finally {
      setLoading(false);
    }
  };
  return /* @__PURE__ */ jsxs(
    "section",
    {
      className: "relative w-full overflow-hidden",
      style: hasBgImage || sectionBg ? {} : { backgroundColor: isDark ? "#111111" : "#f9fafb" },
      children: [
        sectionBg && /* @__PURE__ */ jsx("div", { className: "absolute inset-0", style: { backgroundColor: sectionBg } }),
        hasBgImage && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("img", { src: data.bg_image, alt: "", className: "absolute inset-0 w-full h-full object-cover" }),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/50" })
        ] }),
        !hasBgImage && !sectionBg && /* @__PURE__ */ jsx("div", { className: "absolute inset-0", style: { backgroundColor: isDark ? "#111111" : "#f9fafb" } }),
        /* @__PURE__ */ jsxs("div", { className: "relative z-10 max-w-2xl mx-auto px-4 text-center", children: [
          data?.title && /* @__PURE__ */ jsx("h2", { className: `text-4xl md:text-5xl font-black tracking-tight mb-4 ${isDark || hasBgImage ? "text-white" : "text-gray-900"}`, children: data.title }),
          data?.description && /* @__PURE__ */ jsx("p", { className: `text-lg mb-10 leading-relaxed ${isDark || hasBgImage ? "text-white/70" : "text-gray-500"}`, children: data.description }),
          submitted ? /* @__PURE__ */ jsx("p", { className: `text-xl font-bold ${isDark || hasBgImage ? "text-white" : "text-[var(--primary)]"}`, children: "🎉 You're in! Thanks for subscribing." }) : /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "flex flex-col sm:flex-row gap-3 max-w-md mx-auto", children: [
              /* @__PURE__ */ jsx(
                "input",
                {
                  type: "email",
                  value: email,
                  onChange: (e) => setEmail(e.target.value),
                  placeholder: data?.placeholder || "Enter your email",
                  className: "flex-1 px-5 py-4 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] text-gray-900 text-sm bg-white",
                  required: true
                }
              ),
              /* @__PURE__ */ jsx(
                "button",
                {
                  type: "submit",
                  disabled: loading,
                  className: "px-8 py-4 bg-[var(--primary)] text-[var(--secondary)] font-bold rounded-full hover:opacity-90 transition-opacity text-sm shrink-0 disabled:opacity-60",
                  children: loading ? "..." : data?.button_text || "Subscribe"
                }
              )
            ] }),
            error && /* @__PURE__ */ jsx("p", { className: "text-red-500 text-sm mt-3", children: error })
          ] })
        ] })
      ]
    }
  );
}
function VideoBanner({ data, isFluid, sectionBg, sectionSettings }) {
  if (!data?.video_url) return null;
  const heightMap = { small: "h-[40vh]", medium: "h-[60vh]", large: "h-[80vh]", fullscreen: "h-screen" };
  const heightSetting = sectionSettings?.section_height || data.height || "medium";
  const heightClass = heightMap[heightSetting] || "h-[60vh]";
  const overlayOpacity = data.overlay_opacity ? Number(data.overlay_opacity) / 100 : 0.4;
  return /* @__PURE__ */ jsxs("section", { className: `relative w-full overflow-hidden ${heightClass}`, children: [
    /* @__PURE__ */ jsx(
      "video",
      {
        src: data.video_url,
        autoPlay: true,
        muted: true,
        loop: true,
        playsInline: true,
        className: "absolute inset-0 w-full h-full object-cover"
      }
    ),
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0", style: { backgroundColor: `rgba(0,0,0,${overlayOpacity})` } }),
    (data.title || data.subtitle || data.cta_text) && /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center justify-center z-10 px-4", children: /* @__PURE__ */ jsxs("div", { className: "text-center text-white max-w-4xl", children: [
      data.title && /* @__PURE__ */ jsx("h2", { className: "text-4xl md:text-6xl lg:text-7xl font-black tracking-tight mb-4 drop-shadow-lg leading-tight", children: data.title }),
      data.subtitle && /* @__PURE__ */ jsx("p", { className: "text-lg md:text-xl mb-10 opacity-90 drop-shadow-md", children: data.subtitle }),
      data.cta_text && data.cta_link && /* @__PURE__ */ jsx(
        Link,
        {
          href: data.cta_link,
          className: "inline-block px-10 py-4 bg-white text-black font-bold rounded-full hover:bg-gray-100 transition-colors text-base",
          children: data.cta_text
        }
      )
    ] }) })
  ] });
}
function TestimonialsSlider({ data, isFluid, sectionBg }) {
  const id = useId();
  const sectionId = id.replace(/:/g, "");
  const testimonials = data?.testimonials || data?.items || [];
  if (testimonials.length === 0) return null;
  return /* @__PURE__ */ jsx("section", { className: "overflow-hidden", style: { backgroundColor: sectionBg || "#fdfdfd" }, children: /* @__PURE__ */ jsxs("div", { className: isFluid ? "w-full px-4 md:px-8" : "container mx-auto px-4", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-end justify-between mb-12", children: [
      /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
        data.title && /* @__PURE__ */ jsx("h2", { className: "text-3xl md:text-5xl font-heading font-black text-gray-900 tracking-tight italic", children: data.title }),
        /* @__PURE__ */ jsx("div", { className: "h-1.5 w-20 bg-black rounded-full" })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "hidden md:flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("button", { className: `prev-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M15 19l-7-7 7-7" }) }) }),
        /* @__PURE__ */ jsx("button", { className: `next-${sectionId} w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-black hover:border-black hover:bg-black hover:text-white transition-all duration-300 shadow-sm disabled:opacity-30 disabled:cursor-not-allowed`, children: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M9 5l7 7-7 7" }) }) })
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx(
      Swiper,
      {
        modules: [Navigation, Pagination],
        navigation: {
          prevEl: `.prev-${sectionId}`,
          nextEl: `.next-${sectionId}`
        },
        pagination: {
          clickable: true,
          bulletActiveClass: "!bg-black !w-8",
          bulletClass: "swiper-pagination-bullet !bg-gray-200 !transition-all !duration-500 !rounded-full"
        },
        spaceBetween: 30,
        slidesPerView: 1.1,
        breakpoints: {
          640: { slidesPerView: 1.5 },
          768: { slidesPerView: 2.2 },
          1024: { slidesPerView: 3 }
        },
        className: "pb-16 !overflow-visible",
        children: testimonials.map((item, idx) => /* @__PURE__ */ jsx(SwiperSlide, { children: /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-[2rem] p-10 shadow-[0_20px_50px_rgba(0,0,0,0.03)] border border-gray-50 h-full flex flex-col group hover:shadow-xl hover:-translate-y-2 transition-all duration-500", children: [
          /* @__PURE__ */ jsx("div", { className: "flex mb-8", children: [1, 2, 3, 4, 5].map((star) => /* @__PURE__ */ jsx("svg", { className: `w-5 h-5 ${star <= (Number(item.rating) || 5) ? "text-black" : "text-gray-100"}`, fill: "currentColor", viewBox: "0 0 20 20", children: /* @__PURE__ */ jsx("path", { d: "M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" }) }, star)) }),
          /* @__PURE__ */ jsxs("blockquote", { className: "text-gray-700 text-lg md:text-xl font-heading font-medium leading-[1.6] flex-1 mb-10", children: [
            '"',
            item.quote,
            '"'
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4 mt-auto", children: [
            /* @__PURE__ */ jsxs("div", { className: "relative", children: [
              item.avatar ? /* @__PURE__ */ jsx("img", { src: item.avatar, alt: item.name, className: "w-14 h-14 rounded-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500" }) : /* @__PURE__ */ jsx("div", { className: "w-14 h-14 rounded-full bg-gray-900 flex items-center justify-center text-white text-lg font-black", children: item.name?.[0] || "?" }),
              /* @__PURE__ */ jsx("div", { className: "absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-4 border-white rounded-full shadow-sm" })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "font-heading font-black text-gray-900 text-base uppercase tracking-tight", children: item.name }),
              item.role && /* @__PURE__ */ jsx("p", { className: "text-gray-400 text-xs font-bold uppercase tracking-widest mt-0.5", children: item.role })
            ] })
          ] })
        ] }) }, idx))
      }
    ) })
  ] }) });
}
function useCountdown(endDate, timeZone) {
  const [time, setTime] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0, expired: false });
  useEffect(() => {
    const calc = () => {
      if (!endDate) return;
      let targetNowTime = Date.now();
      {
        try {
          const now = /* @__PURE__ */ new Date();
          const formatter = new Intl.DateTimeFormat("en-US", {
            timeZone,
            year: "numeric",
            month: "numeric",
            day: "numeric",
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
            hourCycle: "h23"
          });
          const parts = formatter.formatToParts(now);
          let y = 0, m = 0, d = 0, h = 0, min = 0, s = 0;
          for (const p of parts) {
            if (p.type === "year") y = parseInt(p.value);
            if (p.type === "month") m = parseInt(p.value) - 1;
            if (p.type === "day") d = parseInt(p.value);
            if (p.type === "hour") h = parseInt(p.value);
            if (p.type === "minute") min = parseInt(p.value);
            if (p.type === "second") s = parseInt(p.value);
          }
          targetNowTime = new Date(y, m, d, h, min, s).getTime();
        } catch (e) {
        }
      }
      let endStr = endDate;
      if (endStr.includes(" ") && !endStr.includes("T")) endStr = endStr.replace(" ", "T");
      if (endStr.endsWith("Z")) endStr = endStr.slice(0, -1);
      const endParts = endStr.split(/[-T: ]/);
      let endTime;
      if (endParts.length >= 6) {
        endTime = new Date(
          parseInt(endParts[0]),
          parseInt(endParts[1]) - 1,
          parseInt(endParts[2]),
          parseInt(endParts[3]),
          parseInt(endParts[4]),
          parseInt(endParts[5])
        ).getTime();
      } else {
        endTime = new Date(endStr).getTime();
      }
      const diff = endTime - targetNowTime;
      if (diff <= 0) {
        setTime({ days: 0, hours: 0, minutes: 0, seconds: 0, expired: true });
        return;
      }
      setTime({
        days: Math.floor(diff / 864e5),
        hours: Math.floor(diff % 864e5 / 36e5),
        minutes: Math.floor(diff % 36e5 / 6e4),
        seconds: Math.floor(diff % 6e4 / 1e3),
        expired: false
      });
    };
    calc();
    const t = setInterval(calc, 1e3);
    return () => clearInterval(t);
  }, [endDate, timeZone]);
  return time;
}
function CountdownTimer({ data, isFluid, sectionBg }) {
  const { settings } = usePage().props;
  const timeZone = settings?.time_zone || "UTC";
  const time = useCountdown(data?.end_date || "", timeZone);
  if (!data?.end_date || time.expired) return null;
  const isDark = data?.bg_style === "dark";
  const bg = sectionBg || (isDark ? "#111111" : "#ffffff");
  const textColor = isDark ? "text-white" : "text-gray-900";
  const mutedColor = isDark ? "text-white/50" : "text-gray-400";
  const boxBg = isDark ? "bg-white/10" : "bg-gray-50 border border-gray-100";
  const pad = (n) => String(n).padStart(2, "0");
  const units = [
    { label: "Days", value: pad(time.days) },
    { label: "Hours", value: pad(time.hours) },
    { label: "Mins", value: pad(time.minutes) },
    { label: "Secs", value: pad(time.seconds) }
  ];
  return /* @__PURE__ */ jsx("section", { className: "w-full", style: { backgroundColor: bg }, children: /* @__PURE__ */ jsxs("div", { className: "max-w-3xl mx-auto px-4 text-center", children: [
    data.title && /* @__PURE__ */ jsx("h2", { className: `text-3xl md:text-5xl font-black tracking-tight mb-3 ${textColor}`, children: data.title }),
    data.description && /* @__PURE__ */ jsx("p", { className: `text-base md:text-lg mb-10 ${mutedColor}`, children: data.description }),
    /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center gap-3 md:gap-6 mb-10", children: units.map((u) => /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center", children: [
      /* @__PURE__ */ jsx("div", { className: `${boxBg} rounded-2xl px-5 py-4 md:px-8 md:py-6 min-w-[70px] md:min-w-[110px]`, children: /* @__PURE__ */ jsx("span", { className: `text-4xl md:text-6xl font-black tabular-nums ${textColor}`, children: u.value }) }),
      /* @__PURE__ */ jsx("span", { className: `text-xs font-semibold uppercase tracking-widest mt-2 ${mutedColor}`, children: u.label })
    ] }, u.label)) }),
    data.cta_text && data.cta_link && /* @__PURE__ */ jsx(
      Link,
      {
        href: data.cta_link,
        className: `inline-block px-10 py-4 rounded-full font-bold transition-opacity hover:opacity-90 ${isDark ? "bg-white text-black" : "bg-[var(--primary)] text-[var(--secondary)]"}`,
        children: data.cta_text
      }
    )
  ] }) });
}
function Spacer({ data }) {
  const size = data?.size || "md";
  const paddingMap = {
    sm: "h-8 md:h-12",
    md: "h-16 md:h-24",
    lg: "h-24 md:h-32",
    xl: "h-32 md:h-48"
  };
  const heightClass = paddingMap[size] || paddingMap.md;
  return /* @__PURE__ */ jsx("div", { className: `w-full ${heightClass}` });
}
const sectionComponents = {
  hero_slider: HeroSlider,
  product_carousel: ProductCarousel,
  text_block: TextBlock,
  image_grid: ImageGrid,
  image_banner: ImageBanner,
  vertical_scroll_cards: HorizontalScrollCards,
  horizontal_scroll_cards: HorizontalScrollCards,
  product_vertical_scroll: ProductHorizontalScroll,
  product_horizontal_scroll: ProductHorizontalScroll,
  image_product_carousel: ImageProductCarousel,
  announcement_marquee: AnnouncementMarquee,
  feature_highlights: FeatureHighlights,
  category_grid: CategoryGrid,
  split_banner: SplitBanner,
  newsletter_signup: NewsletterSignup,
  video_banner: VideoBanner,
  testimonials_slider: TestimonialsSlider,
  countdown_timer: CountdownTimer,
  spacer: Spacer
};
const paddingYMap = {
  "0": "py-0",
  "4": "py-4 md:py-6",
  "8": "py-8 md:py-12",
  "12": "py-12 md:py-16",
  "16": "py-16 md:py-24",
  "24": "py-24 md:py-32",
  "32": "py-32 md:py-40"
};
const paddingXMap = {
  "0": "px-0",
  "4": "px-4 md:px-6",
  "8": "px-8 md:px-12",
  "12": "px-12 md:px-16",
  "16": "px-16 md:px-24",
  "24": "px-24 md:px-32",
  "32": "px-32 md:px-40"
};
const marginYMap = {
  "0": "my-0",
  "4": "my-4 md:my-6",
  "8": "my-8 md:my-12",
  "12": "my-12 md:my-16",
  "16": "my-16 md:my-24",
  "24": "my-24 md:my-32",
  "32": "my-32 md:my-40"
};
const marginXMap = {
  "0": "mx-0",
  "4": "mx-4 md:mx-6",
  "8": "mx-8 md:mx-12",
  "12": "mx-12 md:mx-16",
  "16": "mx-16 md:mx-24",
  "24": "mx-24 md:mx-32",
  "32": "mx-32 md:mx-40"
};
function PageRenderer({ content, layout = "default", settings = {} }) {
  if (!content || !Array.isArray(content)) return null;
  const pageOverride = layout || "default";
  const globalDefault = settings.default_page_layout || "contained";
  const isFluid = pageOverride === "fluid" || pageOverride === "default" && globalDefault === "fluid";
  return /* @__PURE__ */ jsx("div", { className: "w-full", children: content.map((section, index) => {
    if (section.type === "page_meta") return null;
    const Component = sectionComponents[section.type];
    if (!Component) {
      console.warn(`Unknown component type: ${section.type}`);
      return null;
    }
    const s = section.settings || {};
    const bgColor = s.bg_color || "";
    const textColor = s.text_color || "";
    const showMobile = s.show_mobile !== false;
    const showDesktop = s.show_desktop !== false;
    let visibilityClass = "";
    if (!showMobile && !showDesktop) visibilityClass = "hidden";
    else if (!showMobile) visibilityClass = "hidden md:block";
    else if (!showDesktop) visibilityClass = "block md:hidden";
    const innerPaddingYClass = paddingYMap[s.inner_padding_y ?? s.inner_padding] || "";
    const innerPaddingXClass = paddingXMap[s.inner_padding_x ?? s.inner_padding] || "";
    const outerMarginYClass = marginYMap[s.outer_margin_y ?? s.outer_padding] || "";
    const outerMarginXClass = marginXMap[s.outer_margin_x ?? s.outer_padding] || "";
    const sectionWidth = s.section_width && s.section_width !== "default" ? s.section_width : section.data?.section_width || "default";
    const isSectionFluid = section.type === "announcement_marquee" || sectionWidth === "full" || sectionWidth !== "contained" && isFluid;
    const sectionHeight = s.section_height || "auto";
    let heightClass = "";
    if (sectionHeight === "small") heightClass = "min-h-[400px] flex flex-col justify-center";
    else if (sectionHeight === "medium") heightClass = "min-h-[600px] flex flex-col justify-center";
    else if (sectionHeight === "large") heightClass = "min-h-[700px] flex flex-col justify-center";
    else if (sectionHeight === "fullscreen") heightClass = "min-h-[100vh] flex flex-col justify-center";
    const wrapperClass = [
      visibilityClass,
      isSectionFluid ? "w-full" : "max-w-7xl mx-auto",
      innerPaddingYClass,
      innerPaddingXClass,
      outerMarginYClass,
      outerMarginXClass,
      heightClass
    ].filter(Boolean).join(" ");
    const sectionStyle = {};
    if (bgColor) sectionStyle.backgroundColor = bgColor;
    if (textColor) sectionStyle.color = textColor;
    return /* @__PURE__ */ jsx(
      "div",
      {
        className: `${wrapperClass} empty:hidden`,
        style: Object.keys(sectionStyle).length > 0 ? sectionStyle : void 0,
        children: /* @__PURE__ */ jsx(
          Component,
          {
            data: section.data,
            isFluid: isSectionFluid,
            sectionBg: bgColor,
            sectionTextColor: textColor,
            sectionSettings: s,
            settings
          }
        )
      },
      index
    );
  }) });
}
function CmsPage({ page, content, layout }) {
  const { settings } = usePage().props;
  if (!page || !content) {
    return /* @__PURE__ */ jsxs("div", { className: "min-h-[70vh] flex flex-col items-center justify-center", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-3xl font-black text-gray-900 tracking-tight", children: "404 - Page Not Found" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-500 mt-4", children: "The page you are looking for does not exist." })
    ] });
  }
  const getPageSchema = () => {
    return {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": page.title,
      "description": page.meta_description || page.title,
      "dateModified": page.updated_at
    };
  };
  return /* @__PURE__ */ jsxs("main", { className: "min-h-screen bg-gray-50", children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: page.title || "Page" }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getPageSchema()) })
    ] }),
    /* @__PURE__ */ jsx(PageRenderer, { content, layout, settings })
  ] });
}
const __vite_glob_0_9 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: CmsPage
}, Symbol.toStringTag, { value: "Module" }));
function CollectionPage({ collection }) {
  const { app_url } = usePage().props;
  const baseUrl = app_url || "https://dopestyle.in";
  if (!collection) return null;
  const getCollectionSchema = () => {
    return {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": collection.name,
      "description": collection.description || `Shop the latest ${collection.name}`,
      "url": `${baseUrl}/collection/${collection.slug}`
    };
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: collection.name }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getCollectionSchema()) })
    ] }),
    /* @__PURE__ */ jsx(
      ProductListing,
      {
        title: collection.name,
        queryKey: "collection",
        queryValue: collection.slug
      }
    )
  ] });
}
const __vite_glob_0_10 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: CollectionPage
}, Symbol.toStringTag, { value: "Module" }));
function GiftCardOption({ card, onBuy, buying, settings }) {
  const isPremium = card.amount >= 1e3;
  const getCardTheme = (amount) => {
    if (amount >= 5e3) {
      return {
        bg: "bg-gradient-to-br from-[#1a1a1a] via-black to-[#1a1a1a] text-[#ffd700] border border-[#ffd700]/30 shadow-[0_20px_50px_rgba(255,215,0,0.15)]",
        glow: "bg-[#ffd700]/10",
        iconBg: "bg-[#ffd700]/10 border-[#ffd700]/20 text-[#ffd700]",
        logo: "brightness-0 invert opacity-80 mix-blend-plus-lighter",
        label: "text-[#ffd700]/60",
        chip: "bg-[#ffd700]/10 border-[#ffd700]/20",
        badgeText: "ELITE"
      };
    }
    if (amount >= 3e3) {
      return {
        bg: "bg-gradient-to-br from-[#2c3e50] via-[#1a252f] to-[#111] text-[#fff] border border-[#34495e]",
        glow: "bg-blue-400/10",
        iconBg: "bg-white/10 border-white/20 text-white",
        logo: "brightness-0 invert opacity-70",
        label: "text-gray-400",
        chip: "bg-white/10 border-white/10",
        badgeText: "PREMIUM"
      };
    }
    if (amount >= 1e3) {
      return {
        bg: "bg-gradient-to-br from-[#434343] to-[#000000] text-white border border-gray-700",
        glow: "bg-white/10",
        iconBg: "bg-white/10 border-white/10 text-gray-200",
        logo: "brightness-0 invert opacity-60",
        label: "text-gray-400",
        chip: "bg-white/10 border-white/10",
        badgeText: "PLUS"
      };
    }
    return {
      bg: "bg-gray-50 text-gray-900 border border-gray-200",
      glow: "bg-black/5",
      iconBg: "bg-white border border-gray-200 text-gray-900",
      logo: "opacity-40",
      label: "text-gray-400",
      chip: "bg-gray-200/50 border-gray-200",
      badgeText: "CLASSIC"
    };
  };
  const theme = getCardTheme(card.amount);
  return /* @__PURE__ */ jsxs(
    "div",
    {
      className: `group relative bg-white rounded-[2.5rem] p-5 transition-all duration-500 hover:shadow-[0_40px_80px_rgba(0,0,0,0.08)] hover:-translate-y-2 border border-gray-100 ${buying ? "opacity-60 pointer-events-none" : ""}`,
      onClick: onBuy,
      children: [
        card.background_image ? /* @__PURE__ */ jsxs(
          "div",
          {
            className: "relative aspect-[1.6/1] w-full rounded-[2rem] overflow-hidden p-8 flex flex-col justify-between transition-all duration-700 group-hover:scale-[1.02] shadow-2xl text-white",
            style: { backgroundImage: `url(${card.background_image})`, backgroundSize: "cover", backgroundPosition: "center" },
            children: [
              /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/40 pointer-events-none" }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start relative z-10", children: [
                /* @__PURE__ */ jsx("div", { className: "w-12 h-12 rounded-2xl flex items-center justify-center bg-white/10 border border-white/20", children: /* @__PURE__ */ jsx(Gift, { className: "w-6 h-6" }) }),
                settings?.main_logo ? /* @__PURE__ */ jsx("img", { src: `/${settings.main_logo}`, alt: "Logo", className: "h-5 w-auto object-contain brightness-0 invert" }) : /* @__PURE__ */ jsx("span", { className: "text-[11px] font-black tracking-[0.4em] uppercase text-white/70", children: settings?.store_name || "VYORA" })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "relative z-10", children: [
                /* @__PURE__ */ jsx("p", { className: "text-[10px] font-black uppercase tracking-[0.2em] mb-2 text-white/60", children: "Gift Card Value" }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-baseline gap-1", children: [
                  /* @__PURE__ */ jsx("span", { className: "text-xl font-bold", children: "₹" }),
                  /* @__PURE__ */ jsx("p", { className: "text-5xl font-black tracking-tighter", children: card.amount.toLocaleString() })
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-end relative z-10", children: [
                /* @__PURE__ */ jsx("p", { className: "text-[9px] font-mono tracking-[0.2em] opacity-40 uppercase", children: "Authenticated Digital Asset" }),
                /* @__PURE__ */ jsx("div", { className: "w-14 h-8 rounded-lg border border-white/20" })
              ] })
            ]
          }
        ) : /* @__PURE__ */ jsxs("div", { className: `relative aspect-[1.6/1] w-full rounded-[2rem] overflow-hidden p-8 flex flex-col justify-between transition-all duration-700 group-hover:scale-[1.02] shadow-2xl ${theme.bg}`, children: [
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 opacity-10 bg-noise pointer-events-none mix-blend-overlay" }),
          /* @__PURE__ */ jsx("div", { className: `absolute -right-20 -top-20 w-64 h-64 rounded-full blur-[80px] pointer-events-none ${theme.glow}` }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start relative z-10", children: [
            /* @__PURE__ */ jsx("div", { className: `w-12 h-12 rounded-2xl flex items-center justify-center ${theme.iconBg}`, children: /* @__PURE__ */ jsx(Gift, { className: "w-6 h-6" }) }),
            settings?.main_logo ? /* @__PURE__ */ jsx("img", { src: `/${settings.main_logo}`, alt: "Logo", className: `h-5 w-auto object-contain ${theme.logo}` }) : /* @__PURE__ */ jsx("span", { className: `text-[11px] font-black tracking-[0.4em] uppercase ${theme.label}`, children: settings?.store_name || "VYORA" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "relative z-10", children: [
            /* @__PURE__ */ jsx("p", { className: `text-[10px] font-black uppercase tracking-[0.2em] mb-2 ${theme.label}`, children: "Gift Card Value" }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-baseline gap-1", children: [
              /* @__PURE__ */ jsx("span", { className: "text-xl font-bold", children: "₹" }),
              /* @__PURE__ */ jsx("p", { className: "text-5xl font-black tracking-tighter", children: card.amount.toLocaleString() })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-end relative z-10", children: [
            /* @__PURE__ */ jsx("p", { className: `text-[9px] font-mono tracking-[0.2em] opacity-40 uppercase`, children: "Authenticated Digital Asset" }),
            /* @__PURE__ */ jsx("div", { className: `w-14 h-8 rounded-lg border ${theme.chip}` })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "mt-8 px-2", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center mb-4", children: [
            /* @__PURE__ */ jsx("h3", { className: "text-base font-black uppercase tracking-widest text-gray-900", children: card.name || `Digital Voucher` }),
            isPremium && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-1.5 bg-black text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest", children: [
              /* @__PURE__ */ jsx(Sparkles, { className: "w-3 h-3 text-amber-400" }),
              " ",
              theme.badgeText
            ] })
          ] }),
          /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 mb-6 leading-relaxed line-clamp-2", children: card.description || "Instant digital delivery with a unique redemption code valid storewide." }),
          /* @__PURE__ */ jsx("div", { className: "space-y-3 mb-8", children: [
            "Instant Unique Code Delivery",
            "Share via WhatsApp or Email",
            card.validity_days ? `Valid for ${card.validity_days} days` : "No Expiry Date"
          ].map((text, i) => /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 text-[11px] text-gray-500 font-medium", children: [
            /* @__PURE__ */ jsx("div", { className: "w-4 h-4 rounded-full bg-emerald-50 flex items-center justify-center", children: /* @__PURE__ */ jsx(Check, { className: "w-2.5 h-2.5 text-emerald-500" }) }),
            text
          ] }, i)) }),
          /* @__PURE__ */ jsx(
            "button",
            {
              className: "w-full py-4 bg-black text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-800 transition-all flex items-center justify-center gap-3 shadow-xl shadow-gray-200 group/btn",
              disabled: buying,
              onClick: (e) => {
                e.stopPropagation();
                onBuy();
              },
              children: buying ? /* @__PURE__ */ jsx("div", { className: "w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin" }) : /* @__PURE__ */ jsxs(Fragment, { children: [
                "Purchase Card ",
                /* @__PURE__ */ jsx(ArrowRight, { className: "w-4 h-4 transition-transform group-hover/btn:translate-x-1" })
              ] })
            }
          )
        ] })
      ]
    }
  );
}
function GiftCardsPage() {
  const { props } = usePage();
  const settings = props.settings;
  const { user } = useAuthStore();
  const { openAuthModal: openAuthModal2 } = useUIStore();
  const [mounted, setMounted] = useState(false);
  const [cards, setCards] = useState([]);
  const [loading, setLoading] = useState(true);
  const [buying, setBuying] = useState(null);
  const [success, setSuccess] = useState("");
  useEffect(() => {
    setMounted(true);
    api.get("/api/gift-cards/purchasable").then((r) => setCards(r.data)).catch(() => {
    }).finally(() => setLoading(false));
  }, []);
  const handleBuy = async (card) => {
    if (!user) {
      openAuthModal2();
      return;
    }
    setBuying(card.id);
    try {
      const orderRes = await api.post("/api/payment/initiate", {
        type: "gift_card",
        template_id: card.id
      });
      if (!orderRes.data?.order_id) throw new Error("Could not initiate payment");
      const options = {
        key: orderRes.data.key,
        amount: orderRes.data.amount,
        currency: "INR",
        name: orderRes.data.name || "Vyora",
        description: orderRes.data.description,
        order_id: orderRes.data.order_id,
        handler: async (response) => {
          const verifyRes = await api.post("/api/payment/verify", {
            ...response,
            type: "gift_card",
            template_id: card.id
          });
          if (verifyRes.data.success) {
            const activateRes = await api.post("/api/gift-cards/activate", {
              template_id: card.id
            });
            if (activateRes.data.success) {
              setSuccess(`₹${card.amount} gift card added to your wallet!`);
              setCards((prev) => prev.map(
                (c) => c.id === card.id ? { ...c, purchased_count: c.purchased_count + 1 } : c
              ));
              window.scrollTo({ top: 0, behavior: "smooth" });
            }
          }
        },
        prefill: { name: user.name, email: user.email },
        theme: { color: "#000000" }
      };
      const rzp = new window.Razorpay(options);
      rzp.open();
    } catch (e) {
      alert("Payment failed. Please try again.");
    } finally {
      setBuying(null);
    }
  };
  if (!mounted) return null;
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gray-50/50 pb-20 selection:bg-black selection:text-white", children: [
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white overflow-hidden border-b border-gray-100", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute top-0 right-0 w-1/2 h-full bg-gray-50/50 -skew-x-12 translate-x-32 pointer-events-none" }),
      /* @__PURE__ */ jsx("div", { className: "absolute top-40 left-10 w-64 h-64 bg-black/5 rounded-full blur-[100px] pointer-events-none" }),
      /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto px-6 py-20 sm:py-32 relative z-10", children: /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-20 items-center", children: [
        /* @__PURE__ */ jsxs("div", { className: "max-w-2xl", children: [
          /* @__PURE__ */ jsxs("div", { className: "inline-flex items-center gap-3 bg-black text-white text-[11px] font-black uppercase tracking-[0.3em] px-6 py-2 rounded-full mb-10 animate-in fade-in slide-in-from-bottom-4 duration-700", children: [
            /* @__PURE__ */ jsx(Sparkles, { className: "w-4 h-4 text-amber-400" }),
            " Digital Masterpieces"
          ] }),
          /* @__PURE__ */ jsxs("h1", { className: "text-6xl sm:text-8xl font-black text-gray-900 tracking-tight leading-[0.9] mb-8 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100", children: [
            "Give the gift",
            /* @__PURE__ */ jsx("br", {}),
            /* @__PURE__ */ jsx("span", { className: "text-gray-300", children: "of style" })
          ] }),
          /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-lg leading-relaxed max-w-lg mb-12 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200", children: "Surprise your loved ones with a gift card. Each purchase generates a unique digital vault code you can redeem yourself or share instantly." }),
          /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row items-start sm:items-center gap-6 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300", children: [
            /* @__PURE__ */ jsxs(
              Link,
              {
                href: "/gift-cards/my-cards",
                className: "inline-flex items-center gap-4 bg-black text-white px-10 py-5 text-[11px] font-black uppercase tracking-[0.2em] rounded-[1.5rem] hover:bg-gray-800 transition-all shadow-2xl shadow-gray-200 group",
                children: [
                  /* @__PURE__ */ jsx(CreditCard, { className: "w-5 h-5 transition-transform group-hover:scale-110" }),
                  " Go to My Wallet"
                ]
              }
            ),
            /* @__PURE__ */ jsxs("div", { className: "flex -space-x-3", children: [
              [1, 2, 3, 4].map((i) => /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-full border-2 border-white bg-gray-100 overflow-hidden", children: /* @__PURE__ */ jsx("img", { src: `https://api.dicebear.com/7.x/avataaars/svg?seed=${i + 10}`, alt: "User" }) }, i)),
              /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-full border-2 border-white bg-gray-900 flex items-center justify-center text-[10px] text-white font-black", children: "+2k" })
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "hidden lg:block relative perspective-1000", children: /* @__PURE__ */ jsxs("div", { className: "relative w-full aspect-[1.6/1] bg-black rounded-[3rem] p-12 shadow-2xl animate-float rotate-3 hover:rotate-0 transition-transform duration-700 cursor-pointer overflow-hidden group", children: [
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-noise opacity-20" }),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-br from-white/10 to-transparent" }),
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start relative z-10", children: [
            /* @__PURE__ */ jsx("div", { className: "w-16 h-16 rounded-[1.5rem] bg-white/10 backdrop-blur-xl border border-white/20 flex items-center justify-center", children: /* @__PURE__ */ jsx(Gift, { className: "w-8 h-8 text-white" }) }),
            /* @__PURE__ */ jsxs("div", { className: "text-right", children: [
              /* @__PURE__ */ jsx("p", { className: "text-white/40 text-[10px] font-black tracking-[0.4em] mb-1", children: "AUTHENTIC" }),
              /* @__PURE__ */ jsx("p", { className: "text-white text-xs font-black tracking-widest", children: "DS-VC-2026" })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "mt-12 relative z-10", children: [
            /* @__PURE__ */ jsx("p", { className: "text-white/30 text-[10px] font-black tracking-[0.4em] mb-2 uppercase", children: "Voucher Value" }),
            /* @__PURE__ */ jsx("p", { className: "text-white text-7xl font-black tracking-tighter", children: "₹5,000" })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "absolute bottom-12 right-12 w-20 h-12 rounded-xl border border-white/20 bg-white/5 backdrop-blur-md" })
        ] }) })
      ] }) })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-6", children: [
      success && /* @__PURE__ */ jsxs("div", { className: "mt-12 p-8 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-[2.5rem] flex flex-col md:flex-row items-center justify-between gap-6 animate-in zoom-in-95 duration-500 shadow-xl shadow-emerald-500/5", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-6", children: [
          /* @__PURE__ */ jsx("div", { className: "w-16 h-16 rounded-[1.8rem] bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-2xl shadow-emerald-200", children: /* @__PURE__ */ jsx(Check, { className: "w-8 h-8" }) }),
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("p", { className: "font-black text-2xl tracking-tight leading-none mb-1", children: success }),
            /* @__PURE__ */ jsx("p", { className: "text-emerald-600 text-sm font-medium", children: "Your unique code is now active in your wallet." })
          ] })
        ] }),
        /* @__PURE__ */ jsx(
          Link,
          {
            href: "/gift-cards/my-cards",
            className: "bg-emerald-800 text-white px-10 py-4 text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-900 transition-all shrink-0 shadow-lg",
            children: "View Card in Vault"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "py-20", children: [
        /* @__PURE__ */ jsxs("div", { className: "mb-16 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-100 pb-10", children: [
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("h2", { className: "text-[11px] font-black uppercase tracking-[0.3em] text-black mb-3", children: "Denominations" }),
            /* @__PURE__ */ jsx("p", { className: "text-gray-400 text-sm font-medium", children: "Select a card value — instant unique code delivery" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-8", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(Zap, { className: "w-4 h-4 text-amber-400" }),
              /* @__PURE__ */ jsx("span", { className: "text-[10px] font-black uppercase tracking-widest text-gray-500", children: "Instant Delivery" })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(Smartphone, { className: "w-4 h-4 text-blue-400" }),
              /* @__PURE__ */ jsx("span", { className: "text-[10px] font-black uppercase tracking-widest text-gray-500", children: "Digital Vault" })
            ] })
          ] })
        ] }),
        loading ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10", children: [1, 2, 3].map((i) => /* @__PURE__ */ jsx("div", { className: "h-[500px] rounded-[2.5rem] bg-white border border-gray-100 animate-pulse" }, i)) }) : cards.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-40 bg-white rounded-[3rem] border border-dashed border-gray-200", children: [
          /* @__PURE__ */ jsx(Gift, { className: "w-16 h-16 text-gray-200 mx-auto mb-6" }),
          /* @__PURE__ */ jsx("p", { className: "text-gray-400 font-black uppercase tracking-widest", children: "No denominations available" })
        ] }) : /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10", children: cards.map((card) => /* @__PURE__ */ jsx(
          GiftCardOption,
          {
            card,
            buying: buying === card.id,
            onBuy: () => handleBuy(card),
            settings
          },
          card.id
        )) })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-4 gap-8 py-24 border-t border-gray-100", children: [
        { icon: /* @__PURE__ */ jsx(CreditCard, { className: "w-6 h-6" }), title: "Encrypted Codes", desc: "Every card has a unique 12-digit code." },
        { icon: /* @__PURE__ */ jsx(Check, { className: "w-6 h-6" }), title: "Valid Storewide", desc: "Use it on any product across the shop." },
        { icon: /* @__PURE__ */ jsx(Heart, { className: "w-6 h-6" }), title: "Easy Sharing", desc: "Gift via WhatsApp or Email instantly." },
        { icon: /* @__PURE__ */ jsx(ShoppingBag, { className: "w-6 h-6" }), title: "No Expiry", desc: "Our cards never expire. Take your time." }
      ].map((feature, i) => /* @__PURE__ */ jsxs("div", { className: "bg-white p-8 rounded-[2rem] border border-gray-50 hover:shadow-xl hover:shadow-gray-100 transition-all duration-500", children: [
        /* @__PURE__ */ jsx("div", { className: "w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mb-6", children: feature.icon }),
        /* @__PURE__ */ jsx("h4", { className: "text-[11px] font-black uppercase tracking-widest mb-3 text-gray-900", children: feature.title }),
        /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 leading-relaxed font-medium", children: feature.desc })
      ] }, i)) }),
      /* @__PURE__ */ jsxs("div", { className: "bg-black rounded-[3rem] p-12 sm:p-20 text-center text-white overflow-hidden relative shadow-2xl shadow-gray-200 mb-20 group", children: [
        /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-noise opacity-10 pointer-events-none" }),
        /* @__PURE__ */ jsx("div", { className: "absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-[100px] pointer-events-none transition-transform duration-1000 group-hover:scale-150" }),
        /* @__PURE__ */ jsx("h3", { className: "text-4xl sm:text-6xl font-black mb-6 relative z-10 tracking-tight", children: "Bulk Gifting?" }),
        /* @__PURE__ */ jsx("p", { className: "text-gray-400 max-w-lg mx-auto mb-12 relative z-10 text-base leading-relaxed", children: "Reward your corporate team or clients with bulk digital gift cards. Contact our sales team for bespoke institutional solutions." }),
        /* @__PURE__ */ jsx("button", { className: "bg-white text-gray-900 px-12 py-5 text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl hover:bg-gray-100 transition-all relative z-10 shadow-2xl active:scale-95", children: "Inquire Now" })
      ] })
    ] })
  ] });
}
const __vite_glob_0_11 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: GiftCardsPage
}, Symbol.toStringTag, { value: "Module" }));
function ShareModal({ card, onClose, settings }) {
  const [codeCopied, setCodeCopied] = useState(false);
  const [showEmailInput, setShowEmailInput] = useState(false);
  const [email, setEmail] = useState("");
  const [sendingEmail, setSendingEmail] = useState(false);
  const [emailSent, setEmailSent] = useState(false);
  const [emailError, setEmailError] = useState("");
  const shopUrl = typeof window !== "undefined" ? `${window.location.origin}/shop` : "";
  const shareText = `🎁 I'm gifting you a ₹${card.amount.toLocaleString()} ${settings?.store_name || "Store"} Gift Card!

Use code: *${card.plain_code}* at checkout.

Shop now at: ${shopUrl}`;
  const copyCode = () => {
    navigator.clipboard.writeText(card.plain_code);
    setCodeCopied(true);
    setTimeout(() => setCodeCopied(false), 2e3);
  };
  const shareWhatsApp = () => {
    window.open(`https://wa.me/?text=${encodeURIComponent(shareText)}`, "_blank");
  };
  const sendEmail = async () => {
    if (!email) {
      setEmailError("Please enter an email address");
      return;
    }
    setSendingEmail(true);
    setEmailError("");
    try {
      await api.post("/api/gift-cards/share-email", { token: card.share_token, email });
      setEmailSent(true);
      setTimeout(() => {
        setShowEmailInput(false);
        setEmailSent(false);
        setEmail("");
      }, 3e3);
    } catch (e) {
      setEmailError(e.response?.data?.message || "Failed to send email.");
    } finally {
      setSendingEmail(false);
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300", onClick: onClose }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-6 py-5 border-b border-gray-100", children: [
        /* @__PURE__ */ jsx("h3", { className: "text-sm font-black text-gray-900 uppercase tracking-widest", children: "Share Gift Card" }),
        /* @__PURE__ */ jsx("button", { onClick: onClose, className: "w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors", children: /* @__PURE__ */ jsx(X, { className: "w-4 h-4 text-gray-400" }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "p-6 space-y-5", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("p", { className: "text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2", children: "Redemption Code" }),
          /* @__PURE__ */ jsxs("div", { className: "bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center justify-between", children: [
            /* @__PURE__ */ jsx("p", { className: "font-mono text-sm font-bold tracking-[0.15em] text-gray-900", children: card.plain_code }),
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: copyCode,
                className: `w-9 h-9 rounded-lg flex items-center justify-center transition-all ${codeCopied ? "bg-emerald-500 text-white" : "bg-white border border-gray-200 text-gray-500 hover:bg-gray-100"}`,
                children: codeCopied ? /* @__PURE__ */ jsx(Check, { className: "w-4 h-4" }) : /* @__PURE__ */ jsx(Copy, { className: "w-4 h-4" })
              }
            )
          ] })
        ] }),
        !showEmailInput ? /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-3", children: [
          /* @__PURE__ */ jsxs(
            "button",
            {
              onClick: shareWhatsApp,
              className: "flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-700",
              children: [
                /* @__PURE__ */ jsx(MessageCircle, { className: "w-3.5 h-3.5 text-emerald-500" }),
                "WhatsApp"
              ]
            }
          ),
          /* @__PURE__ */ jsxs(
            "button",
            {
              onClick: () => setShowEmailInput(true),
              className: "flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-700",
              children: [
                /* @__PURE__ */ jsx(Mail, { className: "w-3.5 h-3.5 text-blue-500" }),
                "Email"
              ]
            }
          )
        ] }) : /* @__PURE__ */ jsx("div", { className: "space-y-3 animate-in fade-in slide-in-from-bottom-2 duration-200", children: emailSent ? /* @__PURE__ */ jsxs("div", { className: "py-6 text-center", children: [
          /* @__PURE__ */ jsx("div", { className: "w-10 h-10 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3", children: /* @__PURE__ */ jsx(Check, { className: "w-5 h-5" }) }),
          /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: "Email sent!" }),
          /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-400 mt-1", children: [
            "Gift card delivered to ",
            email
          ] })
        ] }) : /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx(
            "input",
            {
              type: "email",
              value: email,
              onChange: (e) => setEmail(e.target.value),
              placeholder: "Recipient's email address",
              className: "w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black/10 focus:border-gray-300",
              onKeyDown: (e) => e.key === "Enter" && sendEmail(),
              autoFocus: true
            }
          ),
          emailError && /* @__PURE__ */ jsx("p", { className: "text-xs text-red-500", children: emailError }),
          /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-3", children: [
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: () => {
                  setShowEmailInput(false);
                  setEmailError("");
                },
                className: "flex items-center justify-center py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-500",
                children: "Back"
              }
            ),
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: sendEmail,
                disabled: sendingEmail,
                className: "flex items-center justify-center py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 disabled:opacity-40 transition-colors",
                children: sendingEmail ? "Sending…" : "Send"
              }
            )
          ] })
        ] }) }),
        !showEmailInput && /* @__PURE__ */ jsxs("p", { className: "text-center text-[9px] text-gray-300 uppercase tracking-widest font-bold pt-1", children: [
          "Secured · ",
          settings?.store_name || "Store",
          " Vault"
        ] })
      ] })
    ] })
  ] });
}
function UseModal({ card, onClose }) {
  const [codeCopied, setCodeCopied] = useState(false);
  const copyCode = () => {
    navigator.clipboard.writeText(card.plain_code);
    setCodeCopied(true);
    setTimeout(() => setCodeCopied(false), 2e3);
  };
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300", onClick: onClose }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-6 py-5 border-b border-gray-100", children: [
        /* @__PURE__ */ jsx("h3", { className: "text-sm font-black text-gray-900 uppercase tracking-widest", children: "Use Gift Card" }),
        /* @__PURE__ */ jsx("button", { onClick: onClose, className: "w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors", children: /* @__PURE__ */ jsx(X, { className: "w-4 h-4 text-gray-400" }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "p-6 space-y-5", children: [
        /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: "Copy the code below and paste it at checkout to apply your balance." }),
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("p", { className: "text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2", children: "Redemption Code" }),
          /* @__PURE__ */ jsxs("div", { className: "bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center justify-between", children: [
            /* @__PURE__ */ jsx("p", { className: "font-mono text-sm font-bold tracking-[0.15em] text-gray-900", children: card.plain_code }),
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: copyCode,
                className: `w-9 h-9 rounded-lg flex items-center justify-center transition-all ${codeCopied ? "bg-emerald-500 text-white" : "bg-white border border-gray-200 text-gray-500 hover:bg-gray-100"}`,
                children: codeCopied ? /* @__PURE__ */ jsx(Check, { className: "w-4 h-4" }) : /* @__PURE__ */ jsx(Copy, { className: "w-4 h-4" })
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsx(
          "a",
          {
            href: "/shop",
            className: "flex items-center justify-center gap-2.5 w-full py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 transition-colors",
            children: "Shop & Redeem Now"
          }
        ),
        /* @__PURE__ */ jsxs("p", { className: "text-center text-[9px] text-gray-300 uppercase tracking-widest font-bold", children: [
          "₹",
          Number(card.remaining_amount).toLocaleString(),
          " available balance"
        ] })
      ] })
    ] })
  ] });
}
function AssignModal({ card, onClose, onSuccess }) {
  const [identifier, setIdentifier] = useState("");
  const [foundUser, setFoundUser] = useState(null);
  const [searching, setSearching] = useState(false);
  const [assigning, setAssigning] = useState(false);
  const [confirmed, setConfirmed] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const lookup = async () => {
    if (!identifier.trim()) return;
    setSearching(true);
    setError("");
    setFoundUser(null);
    try {
      const r = await api.post("/api/gift-cards/lookup-user", { identifier });
      setFoundUser(r.data.user);
    } catch (e) {
      setError(e.response?.data?.message || "User not found.");
    } finally {
      setSearching(false);
    }
  };
  const assign = async () => {
    if (!confirmed || !foundUser) return;
    setAssigning(true);
    setError("");
    try {
      await api.post("/api/gift-cards/assign", { gift_card_id: card.id, recipient_id: foundUser.id });
      setSuccess(`Gift card sent to ${foundUser.name}!`);
      setTimeout(() => {
        onSuccess();
        onClose();
      }, 1500);
    } catch (e) {
      setError(e.response?.data?.message || "Transaction failed.");
    } finally {
      setAssigning(false);
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300", onClick: onClose }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-sm rounded-3xl shadow-xl z-10 overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-6 duration-300", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-6 py-5 border-b border-gray-100", children: [
        /* @__PURE__ */ jsx("h3", { className: "text-sm font-black text-gray-900 uppercase tracking-widest", children: "Transfer Gift Card" }),
        /* @__PURE__ */ jsx("button", { onClick: onClose, className: "w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors", children: /* @__PURE__ */ jsx(X, { className: "w-4 h-4 text-gray-400" }) })
      ] }),
      !success ? /* @__PURE__ */ jsx("div", { className: "p-6 space-y-4", children: !foundUser ? /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: "Enter the recipient's email or phone to find their account." }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
          /* @__PURE__ */ jsx(
            "input",
            {
              value: identifier,
              onChange: (e) => setIdentifier(e.target.value),
              onKeyDown: (e) => e.key === "Enter" && lookup(),
              placeholder: "Email or phone",
              className: "flex-1 bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black/10 focus:border-gray-300"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: lookup,
              disabled: searching || !identifier.trim(),
              className: "px-5 py-3 bg-black text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-800 disabled:opacity-30 transition-colors",
              children: searching ? "…" : "Find"
            }
          )
        ] }),
        error && /* @__PURE__ */ jsxs("p", { className: "text-xs text-red-500 flex items-center gap-1.5", children: [
          /* @__PURE__ */ jsx(AlertCircle, { className: "w-3.5 h-3.5 shrink-0" }),
          error
        ] })
      ] }) : /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsxs("div", { className: "bg-gray-50 border border-gray-100 rounded-xl p-4", children: [
          /* @__PURE__ */ jsx("p", { className: "text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1", children: "Sending to" }),
          /* @__PURE__ */ jsx("p", { className: "font-bold text-gray-900", children: foundUser.name }),
          /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500", children: foundUser.email })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl", children: [
          /* @__PURE__ */ jsx(AlertCircle, { className: "w-4 h-4 text-amber-500 shrink-0 mt-0.5" }),
          /* @__PURE__ */ jsxs("p", { className: "text-xs text-amber-700 leading-relaxed", children: [
            "This transfer is ",
            /* @__PURE__ */ jsx("strong", { children: "permanent" }),
            ". You will lose access to this gift card immediately."
          ] })
        ] }),
        /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-3 cursor-pointer", children: [
          /* @__PURE__ */ jsx("div", { className: `w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0 ${confirmed ? "bg-black border-black" : "border-gray-200"}`, children: confirmed && /* @__PURE__ */ jsx(Check, { className: "w-3 h-3 text-white" }) }),
          /* @__PURE__ */ jsx("input", { type: "checkbox", checked: confirmed, onChange: (e) => setConfirmed(e.target.checked), className: "hidden" }),
          /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-600 font-bold", children: "I confirm this transfer" })
        ] }),
        error && /* @__PURE__ */ jsx("p", { className: "text-xs text-red-500", children: error }),
        /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-3", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: () => setFoundUser(null),
              className: "flex items-center justify-center py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors text-gray-500",
              children: "Back"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: assign,
              disabled: !confirmed || assigning,
              className: "flex items-center justify-center py-3 px-4 rounded-xl bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-gray-800 disabled:opacity-40 transition-colors",
              children: assigning ? "Sending…" : "Transfer"
            }
          )
        ] })
      ] }) }) : /* @__PURE__ */ jsxs("div", { className: "p-8 text-center", children: [
        /* @__PURE__ */ jsx("div", { className: "w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4", children: /* @__PURE__ */ jsx(CheckCircle, { className: "w-6 h-6" }) }),
        /* @__PURE__ */ jsx("p", { className: "font-bold text-gray-900 mb-1", children: "Transfer Complete" }),
        /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: success })
      ] })
    ] })
  ] });
}
function GiftCardChip({ card, onShare, onAssign, onUse }) {
  return /* @__PURE__ */ jsxs("div", { className: `bg-white border border-gray-200 rounded-2xl p-6 transition-all hover:shadow-sm flex flex-col justify-between ${card.is_redeemable ? "opacity-100" : "opacity-60 grayscale"}`, children: [
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-6", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("div", { className: "w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 shrink-0", children: /* @__PURE__ */ jsx(Gift, { className: "w-5 h-5 text-gray-400" }) }),
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("p", { className: "text-sm font-bold text-gray-900", children: card.template_name ?? "Gift Card" }),
          /* @__PURE__ */ jsx("p", { className: "font-mono text-xs text-gray-500 mt-0.5", children: card.card_number })
        ] })
      ] }),
      /* @__PURE__ */ jsx("span", { className: `px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shrink-0 ${card.status === "active" ? "bg-emerald-50 text-emerald-700" : card.status === "partially_used" ? "bg-amber-50 text-amber-700" : "bg-gray-100 text-gray-500"}`, children: card.status_badge.label })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
      /* @__PURE__ */ jsx("p", { className: "text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1", children: "Available Balance" }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-baseline gap-1", children: [
        /* @__PURE__ */ jsx("span", { className: "text-lg font-bold text-gray-400", children: "₹" }),
        /* @__PURE__ */ jsx("p", { className: "text-3xl font-bold text-gray-900", children: card.remaining_amount.toLocaleString() })
      ] }),
      card.used_amount > 0 && /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("div", { className: "h-1.5 flex-1 bg-gray-100 rounded-full overflow-hidden", children: /* @__PURE__ */ jsx(
          "div",
          {
            className: "h-full bg-gray-900 rounded-full",
            style: { width: `${card.remaining_amount / card.amount * 100}%` }
          }
        ) }),
        /* @__PURE__ */ jsxs("p", { className: "text-[10px] text-gray-500 font-bold uppercase tracking-widest whitespace-nowrap", children: [
          "Used ₹",
          card.used_amount.toLocaleString()
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between pt-4 border-t border-gray-100 mt-auto", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-[11px] font-semibold text-gray-500 flex items-center gap-1.5", children: [
        /* @__PURE__ */ jsx(Clock, { className: "w-3.5 h-3.5" }),
        card.expires_at ? `Exp ${new Date(card.expires_at).toLocaleDateString()}` : "No Expiry"
      ] }),
      card.is_redeemable && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => onUse(card),
            className: "flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-all border border-gray-200",
            children: "Use"
          }
        ),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => onShare(card),
            className: "flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-all border border-gray-200",
            children: "Share"
          }
        ),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => onAssign(card),
            className: "flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider bg-black text-white px-4 py-2 rounded-xl hover:bg-gray-800 transition-all shadow-sm",
            children: "Transfer"
          }
        )
      ] })
    ] })
  ] });
}
function MyGiftCardsPage() {
  const { props } = usePage();
  const settings = props.settings;
  const { user } = useAuthStore();
  const [mounted, setMounted] = useState(false);
  const [cards, setCards] = useState([]);
  const [wallet, setWallet] = useState(null);
  const [loading, setLoading] = useState(true);
  const [shareCard, setShareCard] = useState(null);
  const [assignCard, setAssignCard] = useState(null);
  const [useCard, setUseCard] = useState(null);
  const load = async () => {
    setLoading(true);
    try {
      const [cardsRes, walletRes] = await Promise.all([
        api.get("/api/gift-cards/my-cards"),
        api.get("/api/gift-cards/wallet")
      ]);
      setCards(cardsRes.data);
      setWallet(walletRes.data);
    } catch {
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => {
    setMounted(true);
    if (user) load();
  }, [user]);
  if (!mounted) return null;
  if (!user) return /* @__PURE__ */ jsxs("div", { className: "min-h-screen flex flex-col items-center justify-center px-4 bg-gray-50/50", children: [
    /* @__PURE__ */ jsxs("div", { className: "w-24 h-24 bg-white rounded-[2.5rem] border border-gray-100 flex items-center justify-center mb-10 shadow-2xl shadow-gray-100 relative overflow-hidden group", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-noise opacity-5 pointer-events-none" }),
      /* @__PURE__ */ jsx(Gift, { className: "w-10 h-10 text-gray-200 group-hover:scale-110 transition-transform" })
    ] }),
    /* @__PURE__ */ jsx("h1", { className: "text-4xl font-black text-gray-900 mb-4 tracking-tight", children: "Identity Required" }),
    /* @__PURE__ */ jsx("p", { className: "text-gray-400 text-base font-medium mb-10", children: "Sign in to access your digital vault and assets." }),
    /* @__PURE__ */ jsxs(Link, { href: "/login", className: "bg-black text-white px-12 py-5 rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.3em] hover:bg-gray-800 transition-all flex items-center gap-4 shadow-2xl shadow-gray-200 active:scale-95", children: [
      "Authorize Access ",
      /* @__PURE__ */ jsx(ArrowRight, { className: "w-5 h-5" })
    ] })
  ] });
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-white pb-20 selection:bg-black selection:text-white", children: [
    /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 py-12", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h1", { className: "text-3xl font-bold text-gray-900 tracking-tight mb-2", children: "My Wallet" }),
          /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-sm", children: "Manage your gift cards, balances, and transfers." })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "shrink-0 flex items-center gap-4", children: /* @__PURE__ */ jsxs(
          Link,
          {
            href: "/gift-cards",
            className: "inline-flex items-center gap-2 bg-black text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-gray-800 transition-all",
            children: [
              /* @__PURE__ */ jsx(Gift, { className: "w-4 h-4" }),
              " Buy Gift Card"
            ]
          }
        ) })
      ] }),
      wallet && /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12", children: [
        { label: "Total Balance", value: `₹${wallet.total_balance.toLocaleString()}`, icon: /* @__PURE__ */ jsx(Wallet, { className: "w-4 h-4 text-gray-600" }) },
        { label: "Total Gifted", value: `₹${wallet.gifted_amount.toLocaleString()}`, icon: /* @__PURE__ */ jsx(History, { className: "w-4 h-4 text-gray-600" }) },
        { label: "Active Cards", value: wallet.active_cards, icon: /* @__PURE__ */ jsx(LayoutGrid, { className: "w-4 h-4 text-gray-600" }) }
      ].map((stat, i) => /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-2xl p-6 shadow-sm", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 mb-4", children: [
          /* @__PURE__ */ jsx("div", { className: "w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100", children: stat.icon }),
          /* @__PURE__ */ jsx("p", { className: "text-xs font-bold uppercase tracking-wider text-gray-500", children: stat.label })
        ] }),
        /* @__PURE__ */ jsx("p", { className: "text-3xl font-black text-gray-900", children: stat.value })
      ] }, i)) }),
      /* @__PURE__ */ jsxs("div", { className: "mb-12", children: [
        /* @__PURE__ */ jsx("div", { className: "mb-6 flex items-center justify-between border-b border-gray-100 pb-4", children: /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-lg font-bold text-gray-900", children: "Your Cards" }),
          /* @__PURE__ */ jsx("span", { className: "bg-gray-100 text-xs font-bold px-2.5 py-0.5 rounded-md text-gray-500", children: cards.length })
        ] }) }),
        loading ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-4", children: [1, 2].map((i) => /* @__PURE__ */ jsx("div", { className: "h-64 rounded-2xl bg-gray-50 border border-gray-100 animate-pulse" }, i)) }) : cards.length === 0 ? /* @__PURE__ */ jsxs("div", { className: "text-center py-20 bg-gray-50 border border-dashed border-gray-200 rounded-2xl", children: [
          /* @__PURE__ */ jsx(Gift, { className: "w-12 h-12 text-gray-300 mx-auto mb-4" }),
          /* @__PURE__ */ jsx("h3", { className: "text-lg font-bold text-gray-900 mb-2", children: "No Gift Cards" }),
          /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-sm mb-6 max-w-xs mx-auto", children: "You don't have any gift cards in your wallet yet." }),
          /* @__PURE__ */ jsxs(Link, { href: "/gift-cards", className: "inline-flex items-center gap-2 bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-xl hover:bg-gray-800 transition-all", children: [
            "Buy a Gift Card ",
            /* @__PURE__ */ jsx(ArrowRight, { className: "w-4 h-4" })
          ] })
        ] }) : /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-4", children: cards.map((card) => /* @__PURE__ */ jsx(
          GiftCardChip,
          {
            card,
            onShare: setShareCard,
            onAssign: setAssignCard,
            onUse: setUseCard
          },
          card.id
        )) })
      ] })
    ] }),
    shareCard && /* @__PURE__ */ jsx(ShareModal, { card: shareCard, onClose: () => setShareCard(null), settings }),
    assignCard && /* @__PURE__ */ jsx(AssignModal, { card: assignCard, onClose: () => setAssignCard(null), onSuccess: load }),
    useCard && /* @__PURE__ */ jsx(UseModal, { card: useCard, onClose: () => setUseCard(null) })
  ] });
}
const __vite_glob_0_12 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: MyGiftCardsPage
}, Symbol.toStringTag, { value: "Module" }));
function GiftCardSharePage() {
  const { props } = usePage();
  const token = props.token;
  const settings = props.settings;
  const [info, setInfo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [codeCopied, setCodeCopied] = useState(false);
  useEffect(() => {
    if (!token) return;
    api.get(`/api/gift-cards/share/${token}`).then((r) => setInfo(r.data)).catch((e) => setInfo({ success: false, message: e.response?.data?.message || "Invalid link." })).finally(() => setLoading(false));
  }, [token]);
  const copyCode = () => {
    if (!info?.plain_code) return;
    navigator.clipboard.writeText(info.plain_code);
    setCodeCopied(true);
    setTimeout(() => setCodeCopied(false), 2500);
  };
  if (loading) {
    return /* @__PURE__ */ jsx("div", { className: "min-h-screen bg-gray-50 flex items-center justify-center", children: /* @__PURE__ */ jsx("div", { className: "w-16 h-16 rounded-[1.5rem] bg-white border border-gray-100 flex items-center justify-center shadow-sm animate-pulse", children: /* @__PURE__ */ jsx(Gift, { className: "w-8 h-8 text-gray-200" }) }) });
  }
  if (!info || !info.success) {
    return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 text-center", children: [
      /* @__PURE__ */ jsx("div", { className: "w-20 h-20 rounded-[2rem] bg-white border border-red-100 flex items-center justify-center mb-6 shadow-sm", children: /* @__PURE__ */ jsx(AlertCircle, { className: "w-10 h-10 text-red-300" }) }),
      /* @__PURE__ */ jsx("h1", { className: "text-2xl font-black text-gray-900 mb-2", children: "Invalid Gift Link" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-400 text-sm mb-8", children: info?.message || "This link is invalid or has expired." }),
      /* @__PURE__ */ jsx(Link, { href: "/gift-cards", className: "bg-black text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all", children: "Browse Gift Cards" })
    ] });
  }
  const isRedeemable = info.is_redeemable && info.status !== "used" && info.status !== "withdrawn";
  const getCardTheme = (amount) => {
    if (amount >= 5e3) {
      return {
        bg: "bg-gradient-to-br from-[#1a1a1a] via-black to-[#1a1a1a] text-[#ffd700] border border-[#ffd700]/30 shadow-[0_20px_50px_rgba(255,215,0,0.15)]",
        glow: "bg-[#ffd700]/10",
        iconBg: "bg-[#ffd700]/10 border-[#ffd700]/20 text-[#ffd700]",
        logo: "brightness-0 invert opacity-80 mix-blend-plus-lighter",
        label: "text-[#ffd700]/60",
        chip: "bg-[#ffd700]/10 border-[#ffd700]/20"
      };
    }
    if (amount >= 3e3) {
      return {
        bg: "bg-gradient-to-br from-[#2c3e50] via-[#1a252f] to-[#111] text-[#fff] border border-[#34495e]",
        glow: "bg-blue-400/10",
        iconBg: "bg-white/10 border-white/20 text-white",
        logo: "brightness-0 invert opacity-70",
        label: "text-gray-400",
        chip: "bg-white/10 border-white/10"
      };
    }
    if (amount >= 1e3) {
      return {
        bg: "bg-gradient-to-br from-[#434343] to-[#000000] text-white border border-gray-700",
        glow: "bg-white/10",
        iconBg: "bg-white/10 border-white/10 text-gray-200",
        logo: "brightness-0 invert opacity-60",
        label: "text-gray-400",
        chip: "bg-white/10 border-white/10"
      };
    }
    return {
      bg: "bg-gray-50 text-gray-900 border border-gray-200",
      glow: "bg-black/5",
      iconBg: "bg-white border border-gray-200 text-gray-900",
      logo: "opacity-40",
      label: "text-gray-400",
      chip: "bg-gray-200/50 border-gray-200"
    };
  };
  const ogImage = settings?.favicon ? `/${settings.favicon}` : settings?.main_logo ? `/${settings.main_logo}` : "/favicon.ico";
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gray-50 pb-20", children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: `Gift Card | ${settings?.store_name || "Store"}` }),
      /* @__PURE__ */ jsx("meta", { property: "og:title", content: `You received a Gift Card!` }),
      /* @__PURE__ */ jsx("meta", { property: "og:description", content: `Open to view and redeem your gift card at ${settings?.store_name || "our store"}.` }),
      /* @__PURE__ */ jsx("meta", { property: "og:image", content: ogImage })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "max-w-xl mx-auto px-4 pt-16", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center mb-10", children: [
        /* @__PURE__ */ jsxs("div", { className: "inline-flex items-center gap-2 bg-white border border-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-[0.2em] px-5 py-2 rounded-full shadow-sm mb-4", children: [
          /* @__PURE__ */ jsx(Gift, { className: "w-3 h-3 text-gray-400" }),
          "A gift from ",
          info.purchased_by
        ] }),
        /* @__PURE__ */ jsxs("h1", { className: "text-3xl sm:text-4xl font-black text-gray-900 tracking-tight", children: [
          "You've received a",
          /* @__PURE__ */ jsx("br", {}),
          /* @__PURE__ */ jsx("span", { className: "text-gray-400", children: "gift card" })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: `relative rounded-[2rem] p-8 shadow-2xl shadow-gray-200 mb-8 overflow-hidden ${getCardTheme(Number(info.amount)).bg}`, children: [
        /* @__PURE__ */ jsx("div", { className: "absolute inset-0 opacity-10 bg-noise pointer-events-none mix-blend-overlay" }),
        /* @__PURE__ */ jsx("div", { className: `absolute -right-12 -top-12 w-48 h-48 rounded-full blur-[40px] pointer-events-none ${getCardTheme(Number(info.amount)).glow}` }),
        /* @__PURE__ */ jsx("div", { className: `absolute -left-8 -bottom-8 w-36 h-36 rounded-full blur-[40px] pointer-events-none ${getCardTheme(Number(info.amount)).glow}` }),
        /* @__PURE__ */ jsxs("div", { className: "relative z-10", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-8", children: [
            /* @__PURE__ */ jsx("div", { className: `w-12 h-12 rounded-2xl flex items-center justify-center ${getCardTheme(Number(info.amount)).iconBg}`, children: /* @__PURE__ */ jsx(Gift, { className: "w-6 h-6" }) }),
            settings?.main_logo ? /* @__PURE__ */ jsx("img", { src: `/${settings.main_logo}`, alt: "Logo", className: `h-5 w-auto object-contain ${getCardTheme(Number(info.amount)).logo}` }) : /* @__PURE__ */ jsx("span", { className: `text-[10px] font-black tracking-[0.3em] uppercase ${getCardTheme(Number(info.amount)).label}`, children: settings?.store_name || "VYORA" })
          ] }),
          /* @__PURE__ */ jsx("p", { className: `text-[10px] uppercase tracking-widest mb-1 ${getCardTheme(Number(info.amount)).label}`, children: "Gift Card Value" }),
          /* @__PURE__ */ jsxs("p", { className: "text-5xl font-black tracking-tight mb-6", children: [
            "₹",
            Number(info.amount).toLocaleString()
          ] }),
          Number(info.remaining_amount) < Number(info.amount) && /* @__PURE__ */ jsxs("p", { className: "text-[11px] text-amber-400 mb-4 font-medium", children: [
            "₹",
            Number(info.remaining_amount).toLocaleString(),
            " remaining (₹",
            (Number(info.amount) - Number(info.remaining_amount)).toLocaleString(),
            " used)"
          ] }),
          /* @__PURE__ */ jsxs("div", { className: `flex items-center justify-between pt-4 border-t mt-6 ${Number(info.amount) >= 1e3 ? "border-white/10" : "border-gray-200"}`, children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: `text-[9px] uppercase tracking-widest mb-1 ${getCardTheme(Number(info.amount)).label}`, children: info.template_name }),
              /* @__PURE__ */ jsx("p", { className: `font-mono text-[11px] tracking-[0.2em] ${getCardTheme(Number(info.amount)).label}`, children: info.card_number })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "text-right", children: info.expires_at ? /* @__PURE__ */ jsxs("div", { className: `flex items-center gap-1.5 text-[10px] ${getCardTheme(Number(info.amount)).label}`, children: [
              /* @__PURE__ */ jsx(Clock, { className: "w-3 h-3" }),
              /* @__PURE__ */ jsxs("span", { children: [
                "Exp ",
                new Date(info.expires_at).toLocaleDateString("en-IN", { day: "2-digit", month: "short", year: "numeric" })
              ] })
            ] }) : /* @__PURE__ */ jsx("p", { className: `text-[10px] ${getCardTheme(Number(info.amount)).label}`, children: "No Expiry" }) })
          ] })
        ] })
      ] }),
      isRedeemable ? /* @__PURE__ */ jsxs("div", { className: "bg-white border border-gray-100 rounded-[2rem] p-8 mb-6 shadow-sm", children: [
        /* @__PURE__ */ jsx("p", { className: "text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 text-center", children: "Your Redemption Code" }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
          /* @__PURE__ */ jsx("div", { className: "flex-1 bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4", children: /* @__PURE__ */ jsx("p", { className: "font-mono text-xl font-black tracking-[0.2em] text-gray-900 text-center", children: info.plain_code }) }),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: copyCode,
              className: `w-14 h-14 rounded-2xl flex items-center justify-center transition-all shadow-sm ${codeCopied ? "bg-emerald-500 text-white" : "bg-gray-900 text-white hover:bg-gray-700"}`,
              children: codeCopied ? /* @__PURE__ */ jsx(CheckCircle, { className: "w-5 h-5" }) : /* @__PURE__ */ jsx(Copy, { className: "w-5 h-5" })
            }
          )
        ] }),
        codeCopied && /* @__PURE__ */ jsx("p", { className: "text-center text-emerald-600 text-xs font-bold mt-3 animate-in fade-in", children: "✓ Copied to clipboard" }),
        /* @__PURE__ */ jsxs("p", { className: "text-[11px] text-gray-400 text-center mt-4 leading-relaxed", children: [
          "Enter this code at checkout to redeem your ₹",
          Number(info.remaining_amount).toLocaleString(),
          " balance."
        ] })
      ] }) : /* @__PURE__ */ jsxs("div", { className: "bg-gray-100 border border-gray-200 rounded-[2rem] p-8 mb-6 text-center", children: [
        /* @__PURE__ */ jsx(AlertCircle, { className: "w-10 h-10 text-gray-300 mx-auto mb-3" }),
        /* @__PURE__ */ jsx("p", { className: "font-black text-gray-500 uppercase tracking-widest text-sm", children: info.status === "used" ? "This card has been fully redeemed" : "This card is no longer active" })
      ] }),
      isRedeemable && /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
        /* @__PURE__ */ jsxs(
          Link,
          {
            href: "/shop",
            className: "w-full flex items-center justify-center gap-3 bg-black text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all shadow-xl shadow-gray-100",
            children: [
              /* @__PURE__ */ jsx(ShoppingBag, { className: "w-4 h-4" }),
              " Shop & Redeem Now"
            ]
          }
        ),
        /* @__PURE__ */ jsx("p", { className: "text-center text-[11px] text-gray-400 font-medium", children: "You can also enter the code manually at checkout" })
      ] })
    ] })
  ] });
}
const __vite_glob_0_13 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: GiftCardSharePage
}, Symbol.toStringTag, { value: "Module" }));
function Home({ page, content, layout }) {
  const { settings, app_url } = usePage().props;
  const baseUrl = app_url || "https://dopestyle.in";
  const storeName = settings?.general?.store_name || "Dope Style";
  if (!page || !content) {
    return /* @__PURE__ */ jsxs("div", { className: "flex min-h-screen flex-col items-center justify-center p-24", children: [
      /* @__PURE__ */ jsx(Head, { title: "Welcome to Our Store" }),
      /* @__PURE__ */ jsx("h1", { className: "text-4xl font-bold mb-4", children: "Welcome to Our Store" }),
      /* @__PURE__ */ jsx("p", { className: "text-xl text-gray-600", children: "We are setting things up. Please check back later!" })
    ] });
  }
  const getHomeSchema = () => {
    return {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": `${baseUrl}/#website`,
          "url": baseUrl,
          "name": storeName,
          "potentialAction": {
            "@type": "SearchAction",
            "target": `${baseUrl}/search?q={search_term_string}`,
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": `${baseUrl}/#organization`,
          "name": storeName,
          "url": baseUrl,
          "logo": settings?.general?.favicon ? `${baseUrl}/${settings.general.favicon}` : `${baseUrl}/favicon.ico`
        }
      ]
    };
  };
  return /* @__PURE__ */ jsxs("main", { className: "min-h-screen bg-gray-50", children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: page.title || "Home" }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getHomeSchema()) })
    ] }),
    /* @__PURE__ */ jsx(PageRenderer, { content, layout, settings })
  ] });
}
const __vite_glob_0_14 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Home
}, Symbol.toStringTag, { value: "Module" }));
function LegalPage({ page }) {
  if (!page) {
    return /* @__PURE__ */ jsxs("div", { className: "min-h-[70vh] flex flex-col items-center justify-center", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-3xl font-black text-gray-900 tracking-tight", children: "404 - Page Not Found" }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-500 mt-4", children: "The policy page you are looking for does not exist." })
    ] });
  }
  const formattedDate = new Date(page.updated_at).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
  const getArticleSchema = () => {
    return {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": page.title,
      "dateModified": page.updated_at,
      "author": {
        "@type": "Organization",
        "name": "Dope Style"
      }
    };
  };
  return /* @__PURE__ */ jsxs("main", { className: "min-h-screen bg-white py-16 md:py-24", children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: page.meta_title || page.title }),
      page.meta_description && /* @__PURE__ */ jsx("meta", { name: "description", content: page.meta_description }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getArticleSchema()) })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "max-w-4xl mx-auto px-4 sm:px-6 lg:px-8", children: [
      /* @__PURE__ */ jsxs("header", { className: "mb-12 border-b border-gray-100 pb-8 text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-4", children: page.title }),
        /* @__PURE__ */ jsxs("p", { className: "text-sm font-semibold text-gray-500 uppercase tracking-widest", children: [
          "Last Updated: ",
          formattedDate
        ] })
      ] }),
      /* @__PURE__ */ jsx(
        "div",
        {
          className: "prose prose-lg max-w-none text-gray-700 \n                        prose-headings:font-bold prose-headings:text-gray-900 \n                        prose-a:text-black prose-a:font-semibold hover:prose-a:text-gray-700\n                        prose-strong:text-gray-900 prose-strong:font-bold\n                        prose-p:leading-relaxed",
          dangerouslySetInnerHTML: { __html: page.content }
        }
      )
    ] })
  ] });
}
const __vite_glob_0_15 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: LegalPage
}, Symbol.toStringTag, { value: "Module" }));
function ProductDetailClient({ product, policies = {}, coupons = [] }) {
  const { openAuthModal: openAuthModal2, openQuickView } = useUIStore();
  const { auth, settings } = usePage().props;
  const megaDealBgFrom = settings?.mega_deal_bg_from || "#2c2c2c";
  const megaDealBgTo = settings?.mega_deal_bg_to || "#2c2c2c";
  const megaDealTextColor = settings?.mega_deal_text_color || "#ffffff";
  settings?.mega_deal_subtext_color || "#9ca3af";
  const megaDealIcon = settings?.mega_deal_icon || "⚡";
  const megaDealBadge = settings?.mega_deal_badge || "Get at";
  const wishlist = useWishlistStore();
  const wishlisted = wishlist.isInWishlist(product.id);
  useEffect(() => {
    trackViewContent(product);
  }, [product.id]);
  const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL || "http://127.0.0.1:8000";
  const sanitizeUrl = (url) => {
    if (!url) return null;
    let path = url.replace(backendUrl, "").replace("http://localhost:8000", "").replace("http://127.0.0.1:8000", "");
    if (path.startsWith("http://") || path.startsWith("https://")) {
      return path;
    }
    if (!path.startsWith("/")) path = `/${path}`;
    return path;
  };
  const cleanMasterImage = sanitizeUrl(product.image);
  const cleanMasterVideo = sanitizeUrl(product.video);
  const uniqueImages = useMemo(() => {
    const map = /* @__PURE__ */ new Map();
    if (cleanMasterVideo) {
      map.set(cleanMasterVideo, { id: "master_video", url: cleanMasterVideo, color_id: null });
    }
    if (cleanMasterImage) {
      map.set(cleanMasterImage, { id: "master_image", url: cleanMasterImage, color_id: null });
    }
    product.images?.forEach((img) => {
      const sanitized = sanitizeUrl(img.url);
      if (sanitized && !map.has(sanitized)) {
        map.set(sanitized, { ...img, url: sanitized });
      }
    });
    return Array.from(map.values());
  }, [product.image, product.video, product.images]);
  const colors = useMemo(() => {
    const all = /* @__PURE__ */ new Map();
    product.variants.forEach((v) => {
      const c = v.attributes.find((a) => a.name === "Color");
      if (c && !all.has(c.value)) all.set(c.value, c);
    });
    return Array.from(all.values());
  }, [product.variants]);
  const sizes = useMemo(() => {
    const all = /* @__PURE__ */ new Set();
    const codeMap = /* @__PURE__ */ new Map();
    product.variants.forEach((v) => {
      const s = v.attributes.find((a) => a.name === "Size");
      if (s) {
        all.add(s.value);
        codeMap.set(s.value, s.code || s.value);
      }
    });
    const availableInChart = product.size_chart?.measurements?.rows?.map((r) => r.size_code.toUpperCase()) || null;
    const order = ["XS", "S", "M", "L", "XL", "XXL", "2XL", "3XL", "4XL", "5XL"];
    return Array.from(all).filter((size) => {
      const code = codeMap.get(size) || size;
      return !availableInChart || availableInChart.includes(code.toUpperCase());
    }).sort((a, b) => {
      const codeA = codeMap.get(a) || a;
      const codeB = codeMap.get(b) || b;
      let iA = order.indexOf(codeA.toUpperCase());
      let iB = order.indexOf(codeB.toUpperCase());
      return (iA !== -1 ? iA : 99) - (iB !== -1 ? iB : 99);
    });
  }, [product.variants, product.size_chart]);
  const handleSizeSelect = (size) => {
    if (!selectedColor) {
      const firstGoodColor = product.variants.find(
        (v) => v.attributes.find((a) => a.name === "Size")?.value === size && v.stock > 0
      )?.attributes.find((a) => a.name === "Color")?.value;
      if (firstGoodColor) {
        setSelectedColor(firstGoodColor);
      }
    }
    setSelectedSize(size);
  };
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [pincode, setPincode] = useState(auth?.user?.default_pincode || "");
  const [pincodeResult, setPincodeResult] = useState(null);
  const [isCheckingPincode, setIsCheckingPincode] = useState(false);
  const [actionsInView, setActionsInView] = useState(true);
  useEffect(() => {
    const target = document.getElementById("actions-container");
    if (!target) return;
    const observer = new IntersectionObserver(
      ([entry]) => {
        setActionsInView(entry.isIntersecting);
      },
      { root: null, threshold: 0 }
    );
    observer.observe(target);
    return () => observer.disconnect();
  }, []);
  const checkPincode = async (code) => {
    if (!code || code.trim().length < 3) return;
    setIsCheckingPincode(true);
    try {
      const res = await axios.post("/api/check-delivery", { pincode: code.trim() });
      setPincodeResult(res.data);
    } catch (e) {
      const msg = e.response?.data?.message || "Could not verify PIN code.";
      setPincodeResult({ available: false, message: msg });
    } finally {
      setIsCheckingPincode(false);
    }
  };
  useEffect(() => {
    if (auth?.user?.default_pincode) {
      checkPincode(auth.user.default_pincode);
    }
  }, [auth?.user?.default_pincode]);
  const displayedImages = useMemo(() => {
    if (!selectedColor) return uniqueImages;
    const colorObj = colors.find((c) => c.value === selectedColor);
    if (!colorObj || !colorObj.id) return uniqueImages;
    const filtered = uniqueImages.filter((img) => img.color_id && img.color_id.toString() === colorObj.id.toString());
    return filtered.length > 0 ? filtered : uniqueImages;
  }, [uniqueImages, selectedColor, colors]);
  const [openAccordion, setOpenAccordion] = useState("description");
  const [showSizeChart, setShowSizeChart] = useState(false);
  const [sizeChartTab, setSizeChartTab] = useState("chart");
  const [showReviewModal, setShowReviewModal] = useState(false);
  const activeColorImage = useMemo(() => {
    if (!selectedColor) return displayedImages[0] || null;
    const colorObj = colors.find((c) => c.value === selectedColor);
    if (!colorObj) return displayedImages[0] || null;
    return uniqueImages.find((img) => img.color_id && img.color_id.toString() === colorObj.id?.toString()) || displayedImages[0] || null;
  }, [selectedColor, colors, uniqueImages, displayedImages]);
  const sizeChartRows = useMemo(() => {
    const rows = product.size_chart?.measurements?.rows || [];
    const order = ["XS", "S", "M", "L", "XL", "XXL", "2XL", "3XL", "4XL", "5XL"];
    return [...rows].sort((a, b) => {
      let iA = order.indexOf(a.size_code.toUpperCase());
      let iB = order.indexOf(b.size_code.toUpperCase());
      return (iA !== -1 ? iA : 99) - (iB !== -1 ? iB : 99);
    });
  }, [product.size_chart]);
  const sizeChartHeaders = useMemo(() => {
    return product.size_chart?.measurements?.headers || [];
  }, [product.size_chart]);
  const currentVariant = useMemo(() => {
    if (!selectedColor || !selectedSize) return null;
    return product.variants.find(
      (v) => v.attributes.find((a) => a.name === "Color")?.value === selectedColor && v.attributes.find((a) => a.name === "Size")?.value === selectedSize
    );
  }, [selectedColor, selectedSize, product.variants]);
  const cart = useCartStore();
  const handleShare = async () => {
    const shareData = {
      title: product.name,
      text: `Check out ${product.name} on ${settings?.store_name || "VYORA"}!`,
      url: window.location.href
    };
    if (navigator.share) {
      try {
        await navigator.share(shareData);
      } catch (err) {
        console.error("Error sharing:", err);
      }
    } else {
      try {
        await navigator.clipboard.writeText(window.location.href);
        alert("Product link copied to clipboard!");
      } catch (err) {
        console.error("Failed to copy link:", err);
      }
    }
  };
  function addToCart() {
    if (!currentVariant) return openQuickView(product, "cart");
    const colorObj = colors.find((c) => c.value === selectedColor);
    const colorImg = colorObj ? product.images?.find((img) => img.color_id?.toString() === colorObj.id?.toString()) : null;
    cart.addItem({
      skuId: currentVariant.id,
      productId: product.id,
      name: product.name,
      slug: product.slug,
      variant: `${selectedColor} - ${selectedSize}`,
      price: currentVariant.price,
      mrp: Math.max(Number(currentVariant.mrp) || 0, Number(product.mrp) || 0, Number(currentVariant.price)),
      image: colorImg?.url || cleanMasterImage || "",
      quantity: 1,
      tax_class: product.tax_class,
      colorName: selectedColor || void 0,
      colorHex: colorObj?.meta || void 0,
      sizeName: selectedSize || void 0,
      size: selectedSize || void 0,
      deliveryDate: product.delivery_timeline?.formatted_date || void 0
    });
    trackAddToCart(product, 1);
  }
  function handleWishlistToggle() {
    if (!auth?.user) {
      openAuthModal2("login");
      return;
    }
    if (wishlisted) {
      wishlist.removeItem(product.id);
    } else {
      const colorObj = colors.find((c) => c.value === selectedColor);
      let variantLabel = "";
      if (selectedColor && selectedSize) variantLabel = `${selectedColor} - ${selectedSize}`;
      else if (selectedColor) variantLabel = selectedColor;
      else if (selectedSize) variantLabel = selectedSize;
      wishlist.addItem({
        productId: product.id,
        skuId: currentVariant?.id,
        variant: variantLabel,
        colorName: selectedColor || void 0,
        colorHex: colorObj?.hex_code || void 0,
        sizeName: selectedSize || void 0,
        size: selectedSize || void 0,
        name: product.name,
        slug: product.slug,
        price: currentVariant ? currentVariant.price : product.price,
        mrp: currentVariant ? currentVariant.mrp : product.mrp,
        discount_percentage: product.discount_percentage,
        image: colorObj?.image || cleanMasterImage || "",
        brand: product.brand,
        category: product.category?.name || "",
        deliveryDate: product.delivery_timeline?.formatted_date || void 0
      });
      trackAddToWishlist(product);
    }
  }
  function buyNow() {
    if (!currentVariant) return openQuickView(product, "buy");
    const colorObj = colors.find((c) => c.value === selectedColor);
    const colorImg = colorObj ? product.images?.find((img) => img.color_id?.toString() === colorObj.id?.toString()) : null;
    cart.addItem({
      skuId: currentVariant.id,
      productId: product.id,
      name: product.name,
      slug: product.slug,
      variant: `${selectedColor} - ${selectedSize}`,
      price: currentVariant.price,
      mrp: Math.max(Number(currentVariant.mrp) || 0, Number(product.mrp) || 0, Number(currentVariant.price)),
      image: colorImg?.url || cleanMasterImage || "",
      quantity: 1,
      tax_class: product.tax_class,
      colorName: selectedColor || void 0,
      colorHex: colorObj?.meta || void 0,
      sizeName: selectedSize || void 0,
      size: selectedSize || void 0,
      deliveryDate: product.delivery_timeline?.formatted_date || void 0
    });
    trackAddToCart(product, 1);
    router.visit("/checkout");
  }
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(
      "div",
      {
        className: cn(
          "fixed bottom-[calc(3.5rem+env(safe-area-inset-bottom))] left-0 right-0 z-40 bg-white/90 backdrop-blur-md border-t border-gray-200 p-3 sm:hidden transition-transform duration-300 ease-in-out",
          actionsInView ? "translate-y-full opacity-0 pointer-events-none" : "translate-y-0 opacity-100"
        ),
        children: /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: buyNow,
              className: "flex-1 bg-black text-white py-3 px-2 rounded-xl font-bold uppercase tracking-widest text-xs hover:bg-gray-900 transition-colors shadow-lg shadow-black/20 active:scale-95",
              children: "Buy Now"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: addToCart,
              disabled: currentVariant && currentVariant.stock <= 0,
              className: "flex-1 bg-white border-2 border-black text-black py-3 px-2 rounded-xl font-bold uppercase tracking-widest text-xs hover:bg-gray-50 transition-colors active:scale-95 disabled:opacity-50",
              children: currentVariant && currentVariant.stock <= 0 ? "Out of Stock" : "Add to Cart"
            }
          )
        ] })
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: "w-full px-0 sm:px-6 lg:px-8 xl:px-12 grid grid-cols-1 md:grid-cols-12 gap-y-4 md:gap-y-10 md:gap-x-8 lg:gap-x-12", children: [
      /* @__PURE__ */ jsxs("div", { className: "md:col-span-7", children: [
        /* @__PURE__ */ jsx("div", { className: "hidden md:grid grid-cols-2 gap-1 sm:gap-2", children: displayedImages.map((img, idx) => /* @__PURE__ */ jsx("div", { className: cn(
          "relative bg-gray-50 overflow-hidden",
          idx === 0 && !selectedColor ? "col-span-2 md:h-[calc(100vh-4rem)]" : "col-span-1 aspect-[3/4]"
        ), children: img.url.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx(
          "video",
          {
            src: img.url,
            className: cn(idx === 0 && !selectedColor ? "object-contain" : "object-cover", "object-center w-full h-full absolute inset-0"),
            autoPlay: true,
            loop: true,
            muted: true,
            playsInline: true
          }
        ) : /* @__PURE__ */ jsx(
          "img",
          {
            src: img.url,
            alt: `${product.name} view ${idx + 1}`,
            className: cn(idx === 0 && !selectedColor ? "object-contain bg-gray-100/50" : "object-cover", "object-center w-full h-full absolute inset-0")
          }
        ) }, img.id || idx)) }),
        /* @__PURE__ */ jsx("div", { className: "md:hidden block", children: /* @__PURE__ */ jsx(
          Swiper,
          {
            modules: [Pagination],
            pagination: { clickable: true },
            spaceBetween: 0,
            slidesPerView: 1,
            className: "w-full aspect-[4/5] sm:rounded-xl overflow-hidden shadow-sm",
            children: displayedImages.map((img, idx) => /* @__PURE__ */ jsx(SwiperSlide, { className: "relative w-full h-full bg-gray-50", children: img.url.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx("video", { src: img.url, className: "w-full h-full object-contain bg-gray-100/50 object-center absolute inset-0", autoPlay: true, loop: true, muted: true, playsInline: true }) : /* @__PURE__ */ jsx("img", { src: img.url, alt: `${product.name} view ${idx + 1}`, className: "w-full h-full absolute inset-0 object-contain bg-gray-100/50 object-center" }) }, img.id || idx))
          }
        ) })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "md:col-span-5 relative px-4 md:px-0 lg:px-4 xl:pr-16 pt-0 md:pt-12", children: /* @__PURE__ */ jsxs("div", { className: "md:sticky md:top-24 flex flex-col gap-y-6 md:gap-y-8 pb-12", children: [
        /* @__PURE__ */ jsxs("div", { className: "order-1 md:order-1 -mt-1 md:-mt-2", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start gap-4", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              product.brand && /* @__PURE__ */ jsx("h2", { className: "text-xs font-bold tracking-widest text-gray-500 uppercase mb-2", children: product.brand }),
              /* @__PURE__ */ jsx("h1", { className: "text-2xl sm:text-3xl font-heading font-medium text-gray-900 mb-3 leading-tight", children: product.name })
            ] }),
            /* @__PURE__ */ jsx("button", { onClick: handleShare, className: "p-2 sm:p-2.5 mt-1 sm:mt-0 shrink-0 border border-gray-200 rounded-full hover:bg-gray-50 hover:border-gray-300 transition-colors", title: "Share this product", children: /* @__PURE__ */ jsx(Share2, { className: "w-4 h-4 sm:w-5 sm:h-5 text-gray-700" }) })
          ] }),
          product.reviews_summary && product.reviews_summary.total_reviews > 0 && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
            /* @__PURE__ */ jsx("div", { className: "flex text-black", children: [1, 2, 3, 4, 5].map((i) => /* @__PURE__ */ jsx(Star, { className: `w-4 h-4 ${product.reviews_summary.average_rating >= i ? "fill-current" : "text-gray-300 fill-current"}` }, i)) }),
            /* @__PURE__ */ jsxs(
              "span",
              {
                className: "text-sm font-medium text-gray-500 underline cursor-pointer hover:text-gray-900 decoration-1 underline-offset-4",
                onClick: () => document.getElementById("reviews-section")?.scrollIntoView({ behavior: "smooth" }),
                children: [
                  product.reviews_summary.total_reviews,
                  " ",
                  product.reviews_summary.total_reviews === 1 ? "Review" : "Reviews"
                ]
              }
            )
          ] })
        ] }),
        product.delivery_timeline && /* @__PURE__ */ jsxs("div", { className: "order-6 md:order-7 flex items-start gap-2 bg-gray-50/50 p-2.5 rounded-lg border border-gray-100", children: [
          /* @__PURE__ */ jsx(Truck, { className: "w-5 h-5 text-gray-700 shrink-0 mt-0.5" }),
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-900 font-medium", children: [
              "Delivered by ",
              /* @__PURE__ */ jsx("span", { className: "font-bold", children: product.delivery_timeline.formatted_date })
            ] }),
            product.delivery_timeline.show_to_user && /* @__PURE__ */ jsx("p", { className: "text-[11px] text-gray-500 mt-0.5", children: product.delivery_timeline.show_to_user })
          ] })
        ] }),
        (product.fit || product.fabric) && /* @__PURE__ */ jsxs("div", { className: "order-7 md:order-2 flex flex-wrap gap-2 -mt-2 md:-mt-5", children: [
          product.fit && /* @__PURE__ */ jsxs("div", { className: "inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-[11px] font-bold text-gray-700 ring-1 ring-inset ring-gray-200 uppercase tracking-widest", children: [
            "Fit: ",
            product.fit
          ] }),
          product.fabric && /* @__PURE__ */ jsxs("div", { className: "inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-[11px] font-bold text-gray-700 ring-1 ring-inset ring-gray-200 uppercase tracking-widest", children: [
            "Fabric: ",
            product.fabric
          ] })
        ] }),
        product.short_description && /* @__PURE__ */ jsx("div", { className: "order-9 md:order-9 text-sm text-gray-600 leading-relaxed", dangerouslySetInnerHTML: { __html: product.short_description } }),
        /* @__PURE__ */ jsxs("div", { className: "order-2 md:order-3 pt-2 md:pt-0 border-t border-gray-200 md:border-none", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-end gap-3", children: [
            /* @__PURE__ */ jsx("span", { className: "text-3xl font-heading font-extrabold text-gray-900 leading-none", children: formatPrice(currentVariant ? currentVariant.price : product.price) }),
            (currentVariant?.mrp || product.mrp) > (currentVariant?.price || product.price) && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 pb-0.5", children: [
              /* @__PURE__ */ jsx("span", { className: "text-lg font-medium text-gray-400 line-through leading-none", children: formatPrice(currentVariant ? currentVariant.mrp : product.mrp) }),
              /* @__PURE__ */ jsxs("span", { className: "text-xs font-black bg-red-50 text-red-600 px-2 py-1 rounded-md tracking-wide", children: [
                product.discount_percentage,
                "% OFF"
              ] })
            ] })
          ] }),
          /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mt-3 font-semibold tracking-wide uppercase", children: "Inclusive of all taxes and shipping" }),
          /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center gap-3 mt-3", children: [
            product.delivery_timeline && /* @__PURE__ */ jsxs("div", { className: "inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-100", children: [
              /* @__PURE__ */ jsx(Truck, { className: "w-3.5 h-3.5 text-gray-600" }),
              /* @__PURE__ */ jsxs("span", { className: "text-[11px] font-bold text-gray-800 tracking-wide uppercase", children: [
                "Delivered by ",
                product.delivery_timeline.formatted_date
              ] })
            ] }),
            product.coupon_price && /* @__PURE__ */ jsxs("div", { className: "inline-block text-[11px] text-gray-500 font-medium bg-green-50/50 px-2.5 py-1.5 rounded-md border border-green-100/50", children: [
              "Best Price ",
              /* @__PURE__ */ jsx("span", { className: "text-green-700 font-bold", children: formatPrice(product.coupon_price) }),
              " with coupon"
            ] })
          ] })
        ] }),
        coupons.length > 0 && /* @__PURE__ */ jsx(
          "div",
          {
            className: "order-3 md:order-4 rounded-2xl p-4 flex flex-col gap-4 shadow-xl border border-gray-700/50",
            style: { background: `linear-gradient(to right, ${megaDealBgFrom}, ${megaDealBgTo})` },
            children: [...coupons].sort((a, b) => {
              const sellingPrice = currentVariant?.price ?? product.price;
              const getDiscount = (c) => {
                if (c.type === "percentage") {
                  const savings = sellingPrice * parseFloat(c.discount_amount) / 100;
                  return c.max_discount_amount ? Math.min(savings, parseFloat(c.max_discount_amount)) : savings;
                }
                if (c.type === "fixed") return Math.min(parseFloat(c.discount_amount), sellingPrice);
                return 0;
              };
              return getDiscount(b) - getDiscount(a);
            }).map((coupon) => {
              const sellingPrice = currentVariant?.price ?? product.price;
              let discountedPrice = null;
              let savingsValue = 0;
              if (coupon.type === "percentage" && coupon.discount_amount) {
                savingsValue = sellingPrice * parseFloat(coupon.discount_amount) / 100;
                if (coupon.max_discount_amount && savingsValue > parseFloat(coupon.max_discount_amount)) {
                  savingsValue = parseFloat(coupon.max_discount_amount);
                }
                discountedPrice = sellingPrice - savingsValue;
                `${coupon.discount_amount}% off (${formatPrice(savingsValue)} saved)`;
              } else if (coupon.type === "fixed" && coupon.discount_amount) {
                savingsValue = Math.min(parseFloat(coupon.discount_amount), sellingPrice);
                discountedPrice = sellingPrice - savingsValue;
                `Flat ${formatPrice(savingsValue)} off`;
              } else if (coupon.type === "free_shipping") {
                discountedPrice = sellingPrice;
              } else if (coupon.type === "bogo" && coupon.bogo_buy_qty && coupon.bogo_get_qty) {
                `Buy ${coupon.bogo_buy_qty} Get ${coupon.bogo_get_qty} Free`;
                discountedPrice = sellingPrice;
              }
              return /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-3 group", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between gap-3", children: [
                  /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2.5 min-w-0", children: [
                    /* @__PURE__ */ jsx("span", { className: "text-xl leading-none shrink-0 flex items-center justify-center", children: megaDealIcon.startsWith("http") ? /* @__PURE__ */ jsx("img", { src: megaDealIcon, alt: "deal", className: "h-6 w-auto object-contain" }) : megaDealIcon }),
                    /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center gap-x-1.5 gap-y-0.5 min-w-0 relative group-hover:scale-105 transition-transform duration-300", children: [
                      /* @__PURE__ */ jsx(
                        "span",
                        {
                          className: "text-lg font-bold",
                          style: { color: megaDealTextColor },
                          children: megaDealBadge
                        }
                      ),
                      discountedPrice !== null && /* @__PURE__ */ jsxs(
                        "span",
                        {
                          className: "text-[22px] font-black tracking-tight truncate relative",
                          style: { color: megaDealTextColor },
                          children: [
                            /* @__PURE__ */ jsx("span", { className: "relative z-10", children: formatPrice(discountedPrice) }),
                            /* @__PURE__ */ jsx("svg", { className: "absolute -bottom-1 left-0 w-full h-1.5 text-yellow-400", preserveAspectRatio: "none", viewBox: "0 0 100 100", children: /* @__PURE__ */ jsx("path", { d: "M0,80 Q50,90 100,70", stroke: "currentColor", strokeWidth: "15", fill: "none", strokeLinecap: "round" }) })
                          ]
                        }
                      )
                    ] })
                  ] }),
                  /* @__PURE__ */ jsx("div", { className: "shrink-0", children: /* @__PURE__ */ jsxs("span", { className: "inline-block px-3 py-1.5 rounded-lg text-white text-xs font-bold whitespace-nowrap bg-[#2ecc71] shadow-sm", children: [
                    "Extra ",
                    formatPrice(savingsValue),
                    " Off"
                  ] }) })
                ] }),
                /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center w-full pt-1", children: /* @__PURE__ */ jsx("span", { className: "text-xs font-medium tracking-wide uppercase", style: { color: megaDealTextColor, opacity: 0.8 }, children: "With Pre-Applied Coupon" }) })
              ] }, coupon.id);
            })
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "order-4 md:order-5 flex flex-col gap-y-6 pt-4 md:pt-0 border-t border-gray-200 md:border-none", children: [
          colors.length > 0 && /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("div", { className: "flex justify-between items-center mb-3", children: /* @__PURE__ */ jsxs("h3", { className: "text-sm font-semibold text-gray-900", children: [
              "Color Variant: ",
              /* @__PURE__ */ jsx("span", { className: "text-gray-500 font-normal", children: selectedColor })
            ] }) }),
            /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-3", children: colors.map((color) => {
              const matchingImg = uniqueImages.find((img) => img.color_id && img.color_id.toString() === color.id?.toString()) || uniqueImages[0];
              return /* @__PURE__ */ jsxs(
                "button",
                {
                  onClick: () => setSelectedColor(selectedColor === color.value ? null : color.value),
                  className: cn(
                    "relative w-[4.5rem] h-[5.5rem] rounded-xl overflow-hidden transition-all group outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black",
                    selectedColor === color.value ? "ring-2 ring-offset-2 ring-black shadow-lg scale-105" : "border-2 border-transparent hover:border-gray-300 opacity-80 hover:opacity-100"
                  ),
                  children: [
                    matchingImg.url.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx("video", { src: matchingImg.url, className: "w-full h-full object-cover absolute inset-0", autoPlay: true, loop: true, muted: true, playsInline: true }) : /* @__PURE__ */ jsx("img", { src: matchingImg.url, alt: color.value, className: "w-full h-full absolute inset-0 object-cover" }),
                    /* @__PURE__ */ jsx("span", { className: "absolute inset-x-0 bottom-0 bg-black/60 pt-6 pb-1 flex items-center justify-center text-[9px] text-white font-bold tracking-wider opacity-0 group-hover:opacity-100 transition-opacity uppercase z-10 text-center leading-none", children: color.value })
                  ]
                },
                color.value
              );
            }) })
          ] }),
          sizes.length > 0 && /* @__PURE__ */ jsxs("div", { className: "pt-2", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center mb-3", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-sm font-semibold text-gray-900", children: "Select Size" }),
              product.size_chart && /* @__PURE__ */ jsxs("button", { onClick: () => setShowSizeChart(true), className: "text-xs font-bold text-gray-500 underline uppercase tracking-wider hover:text-black transition-colors underline-offset-4 flex items-center gap-1", children: [
                /* @__PURE__ */ jsx(Ruler, { className: "w-3 h-3" }),
                " Size Chart"
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2.5", children: sizes.map((size) => {
              const isAvailable = product.variants.some(
                (v) => (selectedColor ? v.attributes.find((a) => a.name === "Color")?.value === selectedColor : true) && v.attributes.find((a) => a.name === "Size")?.value === size && v.stock > 0
              );
              return /* @__PURE__ */ jsxs(
                "button",
                {
                  onClick: () => handleSizeSelect(size),
                  disabled: !isAvailable,
                  className: cn(
                    "w-[calc(25%-7.5px)] sm:w-16 py-3.5 flex items-center justify-center bg-white border rounded-xl text-sm font-bold transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-black",
                    selectedSize === size ? "border-black bg-black text-white shadow-md transform scale-[1.02]" : isAvailable ? "border-gray-200 text-gray-900 hover:border-black hover:bg-gray-50" : "border-gray-100 text-gray-300 cursor-not-allowed bg-gray-50 relative overflow-hidden"
                  ),
                  children: [
                    size,
                    !isAvailable && /* @__PURE__ */ jsx("span", { className: "absolute w-full border-t border-gray-300 -rotate-[35deg]", style: { top: "50%", left: "0" } })
                  ]
                },
                size
              );
            }) })
          ] })
        ] }),
        " ",
        /* @__PURE__ */ jsxs("div", { className: "order-5 md:order-6 flex flex-col sm:flex-row gap-3 -mt-2 md:-mt-4", id: "actions-container", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: buyNow,
              className: "flex-1 bg-black text-white py-4 px-2 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-gray-900 transition-colors shadow-xl shadow-black/20 active:scale-[0.98] outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black",
              children: "Buy It Now"
            }
          ),
          /* @__PURE__ */ jsxs("div", { className: "flex gap-3 flex-1", children: [
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: addToCart,
                disabled: currentVariant && currentVariant.stock <= 0,
                className: "flex-1 bg-white border-2 border-black text-black py-4 px-2 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-not-allowed active:scale-[0.98] outline-none",
                children: currentVariant && currentVariant.stock <= 0 ? "Out of Stock" : "Add to Cart"
              }
            ),
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: handleWishlistToggle,
                className: `w-14 sm:w-16 shrink-0 flex items-center justify-center border-2 rounded-xl transition-all active:scale-95 outline-none ${wishlisted ? "border-red-200 bg-red-50 text-red-500" : "border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200 hover:bg-red-50"}`,
                children: /* @__PURE__ */ jsx(Heart, { className: `w-5 h-5 sm:w-6 sm:h-6 ${wishlisted ? "fill-red-500" : ""}` })
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "order-8 md:order-8 bg-gray-50 p-5 rounded-xl border border-gray-100 space-y-3 mt-4 md:mt-8", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 text-sm font-bold text-gray-900 uppercase tracking-widest", children: [
            /* @__PURE__ */ jsx(Truck, { className: "w-4 h-4" }),
            /* @__PURE__ */ jsx("span", { children: "Check Delivery Pincode" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex shadow-sm rounded-lg overflow-hidden", children: [
            /* @__PURE__ */ jsx(
              "input",
              {
                type: "text",
                placeholder: "Enter PIN Code",
                value: pincode,
                onChange: (e) => setPincode(e.target.value),
                onKeyDown: (e) => e.key === "Enter" && checkPincode(pincode),
                className: "flex-1 bg-white border border-gray-200 border-r-0 px-4 py-3 text-sm focus:outline-none focus:bg-gray-50 transition-colors font-medium text-gray-900"
              }
            ),
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: () => checkPincode(pincode),
                disabled: isCheckingPincode || !pincode,
                className: "bg-black text-white px-6 font-bold text-xs tracking-widest hover:bg-gray-800 transition-colors active:scale-95 origin-right disabled:opacity-70",
                children: isCheckingPincode ? "CHECKING..." : "CHECK"
              }
            )
          ] }),
          pincodeResult ? /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
            /* @__PURE__ */ jsx("p", { className: `text-[12px] font-bold ${pincodeResult.available ? "text-green-600" : "text-red-600"}`, children: pincodeResult.message }),
            pincodeResult.available && product.delivery_timeline && /* @__PURE__ */ jsxs("p", { className: "text-[11.5px] text-gray-700 font-medium", children: [
              "Expected Delivery: ",
              /* @__PURE__ */ jsx("span", { className: "font-bold text-black", children: product.delivery_timeline.formatted_date })
            ] })
          ] }) : /* @__PURE__ */ jsx("p", { className: "text-[11px] text-gray-500 font-medium", children: "Please enter PIN code to check delivery time & availability." })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "order-10 md:order-10 border-t border-gray-200 mt-4 md:mt-10 divide-y divide-gray-100", children: [
          /* @__PURE__ */ jsxs("div", { className: "py-2", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => setOpenAccordion(openAccordion === "description" ? null : "description"), className: "flex w-full items-center justify-between py-4 font-bold text-gray-900 group outline-none", children: [
              /* @__PURE__ */ jsx("span", { className: "uppercase tracking-widest text-sm", children: "Product Description" }),
              openAccordion === "description" ? /* @__PURE__ */ jsx(ChevronUp, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" }) : /* @__PURE__ */ jsx(ChevronDown, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" })
            ] }),
            /* @__PURE__ */ jsx("div", { className: cn("overflow-hidden transition-all duration-300 ease-in-out", openAccordion === "description" ? "max-h-[2000px] opacity-100 pb-4" : "max-h-0 opacity-0"), children: /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-500 leading-relaxed bg-white", children: product.long_description ? /* @__PURE__ */ jsx("div", { className: "prose prose-sm max-w-none text-gray-600", dangerouslySetInnerHTML: { __html: product.long_description } }) : /* @__PURE__ */ jsx("p", { children: "Premium quality guarantees unmatched durability and comfort. Built identically to exact specifications required for luxury wear. This product maps perfectly to modern street aesthetics." }) }) })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "py-2", children: [
            /* @__PURE__ */ jsxs("button", { onClick: () => setOpenAccordion(openAccordion === "shipping" ? null : "shipping"), className: "flex w-full items-center justify-between py-4 font-bold text-gray-900 group outline-none", children: [
              /* @__PURE__ */ jsx("span", { className: "uppercase tracking-widest text-sm", children: "Shipping & Returns" }),
              openAccordion === "shipping" ? /* @__PURE__ */ jsx(ChevronUp, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" }) : /* @__PURE__ */ jsx(ChevronDown, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" })
            ] }),
            /* @__PURE__ */ jsx("div", { className: cn("overflow-hidden transition-all duration-300 ease-in-out", openAccordion === "shipping" ? "max-h-[1200px] opacity-100 pb-4" : "max-h-0 opacity-0"), children: /* @__PURE__ */ jsxs("div", { className: "text-sm text-gray-600 space-y-4 leading-relaxed", children: [
              /* @__PURE__ */ jsxs("div", { className: "bg-gray-50/60 p-4 rounded-xl border border-gray-100 space-y-3", children: [
                product.delivery_timeline && /* @__PURE__ */ jsxs("div", { className: "flex gap-3 pb-3 border-b border-gray-200/60", children: [
                  /* @__PURE__ */ jsx(Truck, { className: "w-5 h-5 mt-0.5 text-black shrink-0" }),
                  /* @__PURE__ */ jsxs("div", { children: [
                    /* @__PURE__ */ jsxs("p", { className: "text-black font-medium", children: [
                      "Delivered by: ",
                      /* @__PURE__ */ jsx("span", { className: "font-bold", children: product.delivery_timeline.formatted_date })
                    ] }),
                    product.delivery_timeline.show_to_user && /* @__PURE__ */ jsx("p", { className: "text-[11px] text-gray-500 mt-0.5 leading-tight", children: product.delivery_timeline.show_to_user })
                  ] })
                ] }),
                /* @__PURE__ */ jsx("div", { className: "space-y-2", children: policies.cod_charges || policies.prepaid_charges ? /* @__PURE__ */ jsxs(Fragment, { children: [
                  policies.cod_charges && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
                    /* @__PURE__ */ jsx(Truck, { className: "w-4 h-4 text-black shrink-0" }),
                    /* @__PURE__ */ jsxs("span", { children: [
                      /* @__PURE__ */ jsx("strong", { children: "COD:" }),
                      " ",
                      policies.cod_charges
                    ] })
                  ] }),
                  policies.prepaid_charges && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
                    /* @__PURE__ */ jsx(ShoppingBag, { className: "w-4 h-4 text-black shrink-0" }),
                    /* @__PURE__ */ jsxs("span", { children: [
                      /* @__PURE__ */ jsx("strong", { children: "Prepaid:" }),
                      " ",
                      policies.prepaid_charges
                    ] })
                  ] })
                ] }) : /* @__PURE__ */ jsxs(Fragment, { children: [
                  /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
                    /* @__PURE__ */ jsx(Truck, { className: "w-5 h-5 text-black shrink-0" }),
                    /* @__PURE__ */ jsx("p", { children: "Free shipping on all prepaid orders across the nation." })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
                    /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5 text-black shrink-0" }),
                    /* @__PURE__ */ jsx("p", { children: "Dispatch within 24–48 business hours." })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
                    /* @__PURE__ */ jsx(ShieldCheck, { className: "w-5 h-5 text-black shrink-0" }),
                    /* @__PURE__ */ jsx("p", { children: "Hassle-free returns & exchanges supported." })
                  ] })
                ] }) })
              ] }),
              policies.return_policy && /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-2", children: [
                  /* @__PURE__ */ jsx("h3", { className: "text-base font-bold uppercase tracking-widest text-black", children: "Returns" }),
                  product.is_returnable ? /* @__PURE__ */ jsx("span", { className: "text-[10px] font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-full uppercase tracking-wider shadow-sm", children: "Returnable" }) : /* @__PURE__ */ jsx("span", { className: "text-[10px] font-bold bg-red-100 text-red-800 px-2 py-0.5 rounded-full uppercase tracking-wider shadow-sm", children: "Non-Returnable (Exchange Only)" })
                ] }),
                /* @__PURE__ */ jsx("div", { dangerouslySetInnerHTML: { __html: policies.return_policy } })
              ] }),
              policies.exchange_policy && /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsx("h3", { className: "text-base font-bold uppercase tracking-widest text-black mb-2", children: "Exchanges" }),
                /* @__PURE__ */ jsx("div", { dangerouslySetInnerHTML: { __html: policies.exchange_policy } })
              ] }),
              policies.refund_method && /* @__PURE__ */ jsxs("div", { className: "pt-2 border-t border-gray-100 mt-2", children: [
                /* @__PURE__ */ jsx("h3", { className: "text-base font-bold uppercase tracking-widest text-black mb-1", children: "Refund Method" }),
                /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-600", dangerouslySetInnerHTML: { __html: policies.refund_method } })
              ] })
            ] }) })
          ] }),
          (() => {
            try {
              const sections = JSON.parse(policies.extra_sections || "[]");
              return sections.map(
                (sec, i) => sec.heading ? /* @__PURE__ */ jsxs("div", { className: "py-2", children: [
                  /* @__PURE__ */ jsxs(
                    "button",
                    {
                      onClick: () => setOpenAccordion(openAccordion === `extra-${i}` ? null : `extra-${i}`),
                      className: "flex w-full items-center justify-between py-4 font-bold text-gray-900 group outline-none",
                      children: [
                        /* @__PURE__ */ jsx("span", { className: "uppercase tracking-widest text-sm", children: sec.heading }),
                        openAccordion === `extra-${i}` ? /* @__PURE__ */ jsx(ChevronUp, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" }) : /* @__PURE__ */ jsx(ChevronDown, { className: "w-5 h-5 text-gray-400 group-hover:text-black transition-colors" })
                      ]
                    }
                  ),
                  /* @__PURE__ */ jsx("div", { className: cn("overflow-hidden transition-all duration-300 ease-in-out", openAccordion === `extra-${i}` ? "max-h-[1200px] opacity-100 pb-4" : "max-h-0 opacity-0"), children: /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-600 leading-relaxed", children: sec.heading.toUpperCase() === "DELIVERY TIMELINE" && product.delivery_timeline ? /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
                    /* @__PURE__ */ jsx(Truck, { className: "w-5 h-5 mt-0.5 text-black shrink-0" }),
                    /* @__PURE__ */ jsxs("div", { children: [
                      /* @__PURE__ */ jsxs("p", { className: "text-black font-medium", children: [
                        "Delivered by: ",
                        /* @__PURE__ */ jsx("span", { className: "font-bold", children: product.delivery_timeline.formatted_date })
                      ] }),
                      product.delivery_timeline.show_to_user && /* @__PURE__ */ jsx("p", { className: "text-[11px] text-gray-500 mt-0.5 leading-tight", children: product.delivery_timeline.show_to_user })
                    ] })
                  ] }) : /* @__PURE__ */ jsx("div", { dangerouslySetInnerHTML: { __html: sec.content } }) }) })
                ] }, i) : null
              );
            } catch {
              return null;
            }
          })()
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "order-11 md:order-11 flex flex-wrap items-center justify-between pt-8 pb-12 border-t border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-widest gap-4", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-1.5 p-2 bg-gray-50 rounded text-black", children: [
            /* @__PURE__ */ jsx(ShieldCheck, { className: "w-4 h-4" }),
            " 100% Original"
          ] }),
          /* @__PURE__ */ jsxs("span", { children: [
            "SKU: ",
            currentVariant ? currentVariant.code : "SELECT"
          ] }),
          product.category && /* @__PURE__ */ jsxs("span", { children: [
            "Cat: ",
            product.category
          ] })
        ] })
      ] }) })
    ] }),
    /* @__PURE__ */ jsxs("div", { id: "reviews-section", className: "max-w-4xl mx-auto px-4 py-16 border-t border-gray-100", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h2", { className: "text-2xl font-bold font-heading mb-2", children: "Customer Reviews" }),
          product.reviews_summary && product.reviews_summary.total_reviews > 0 ? /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
            /* @__PURE__ */ jsx("div", { className: "flex text-yellow-400", children: [1, 2, 3, 4, 5].map((i) => /* @__PURE__ */ jsx(Star, { className: `w-5 h-5 ${product.reviews_summary.average_rating >= i ? "fill-current" : "text-gray-200 fill-current"}` }, i)) }),
            /* @__PURE__ */ jsxs("span", { className: "font-bold text-gray-900 text-lg", children: [
              product.reviews_summary.average_rating,
              " out of 5"
            ] }),
            /* @__PURE__ */ jsxs("span", { className: "text-gray-500 text-sm", children: [
              "(",
              product.reviews_summary.total_reviews,
              " reviews)"
            ] })
          ] }) : /* @__PURE__ */ jsx("p", { className: "text-gray-500", children: "No reviews yet." })
        ] }),
        auth?.user && /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => setShowReviewModal(true),
            className: "bg-black text-white px-6 py-3 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-gray-800 transition-colors",
            children: "Write a Review"
          }
        )
      ] }),
      /* @__PURE__ */ jsx("div", { className: "space-y-8", children: (product.reviews || []).map((review) => /* @__PURE__ */ jsxs("div", { className: "border-b border-gray-100 pb-8", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-2", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
            /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500", children: review.user.name.charAt(0).toUpperCase() }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("p", { className: "font-bold text-gray-900 text-sm", children: review.user.name }),
              /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: review.created_at })
            ] })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "flex text-yellow-400", children: [1, 2, 3, 4, 5].map((i) => /* @__PURE__ */ jsx(Star, { className: `w-4 h-4 ${review.rating >= i ? "fill-current" : "text-gray-200 fill-current"}` }, i)) })
        ] }),
        review.comment && /* @__PURE__ */ jsx("p", { className: "text-gray-700 text-sm leading-relaxed mt-4", children: review.comment }),
        review.images && review.images.length > 0 && /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2 mt-4", children: review.images.map((img) => /* @__PURE__ */ jsx("a", { href: img.url, target: "_blank", children: /* @__PURE__ */ jsx("img", { src: img.url, className: "w-20 h-20 rounded-lg object-cover border border-gray-200 hover:opacity-80 transition-opacity" }) }, img.id)) }),
        review.admin_reply && /* @__PURE__ */ jsxs("div", { className: "mt-4 bg-gray-50 p-4 rounded-xl border-l-4 border-black", children: [
          /* @__PURE__ */ jsx("p", { className: "text-xs font-bold uppercase tracking-widest text-gray-900 mb-1", children: "Store Reply" }),
          /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-700", children: review.admin_reply })
        ] })
      ] }, review.id)) })
    ] }),
    /* @__PURE__ */ jsx(
      "div",
      {
        onClick: () => setShowSizeChart(false),
        className: cn(
          "fixed inset-0 bg-black/40 z-40 transition-opacity duration-300",
          showSizeChart ? "opacity-100 pointer-events-auto" : "opacity-0 pointer-events-none"
        )
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: cn(
      "fixed top-0 right-0 h-full w-full max-w-md bg-white z-50 shadow-2xl flex flex-col transition-transform duration-300 ease-in-out",
      showSizeChart ? "translate-x-0" : "translate-x-full"
    ), children: [
      /* @__PURE__ */ jsx(
        "button",
        {
          onClick: () => setShowSizeChart(false),
          className: "absolute top-4 left-4 p-2 rounded-full hover:bg-gray-100 transition-colors z-10",
          children: /* @__PURE__ */ jsx(X, { className: "w-5 h-5 text-gray-600" })
        }
      ),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4 px-6 pt-14 pb-5 border-b border-gray-100", children: [
        activeColorImage && /* @__PURE__ */ jsx("div", { className: "relative w-20 h-24 rounded-xl overflow-hidden shrink-0 bg-gray-50", children: activeColorImage.url.match(/\.(mp4|webm|mov|qt)$/i) ? /* @__PURE__ */ jsx("video", { src: activeColorImage.url, className: "w-full h-full object-cover absolute inset-0", autoPlay: true, loop: true, muted: true, playsInline: true }) : /* @__PURE__ */ jsx("img", { src: activeColorImage.url, alt: product.name, className: "w-full h-full absolute inset-0 object-cover" }) }),
        /* @__PURE__ */ jsxs("div", { className: "min-w-0", children: [
          product.brand && /* @__PURE__ */ jsx("p", { className: "text-xs font-bold text-gray-400 uppercase tracking-widest mb-1", children: product.brand }),
          /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold text-gray-900 leading-snug line-clamp-2", children: product.name }),
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mt-2", children: [
            /* @__PURE__ */ jsx("span", { className: "text-base font-extrabold text-gray-900", children: formatPrice(product.price) }),
            product.mrp > product.price && /* @__PURE__ */ jsxs(Fragment, { children: [
              /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-400 line-through", children: formatPrice(product.mrp) }),
              /* @__PURE__ */ jsxs("span", { className: "text-xs font-bold text-green-600", children: [
                product.discount_percentage,
                "% OFF"
              ] })
            ] })
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex border-b border-gray-200 shrink-0", children: [
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => setSizeChartTab("chart"),
            className: cn(
              "flex-1 py-3.5 text-sm font-bold tracking-wide transition-colors",
              sizeChartTab === "chart" ? "border-b-2 border-black text-black" : "text-gray-400 hover:text-gray-700"
            ),
            children: "Size Chart"
          }
        ),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => setSizeChartTab("measure"),
            className: cn(
              "flex-1 py-3.5 text-sm font-bold tracking-wide transition-colors",
              sizeChartTab === "measure" ? "border-b-2 border-black text-black" : "text-gray-400 hover:text-gray-700"
            ),
            children: "How to Measure"
          }
        )
      ] }),
      /* @__PURE__ */ jsx("div", { className: "flex-1 overflow-y-auto", children: sizeChartTab === "chart" ? /* @__PURE__ */ jsxs("div", { className: "p-4", children: [
        /* @__PURE__ */ jsx("p", { className: "text-right text-xs text-gray-400 font-semibold mb-3 uppercase tracking-wider", children: "Measurements table (Inches)" }),
        /* @__PURE__ */ jsx("div", { className: "overflow-x-auto rounded-xl border border-gray-100", children: /* @__PURE__ */ jsxs("table", { className: "w-full text-sm text-center", children: [
          /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { className: "bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider", children: [
            /* @__PURE__ */ jsx("th", { className: "py-3 px-3 text-left", children: "Size" }),
            sizeChartHeaders.map((header) => /* @__PURE__ */ jsx("th", { className: "py-3 px-3", children: header }, header))
          ] }) }),
          /* @__PURE__ */ jsx("tbody", { className: "divide-y divide-gray-50", children: sizeChartRows.length > 0 ? sizeChartRows.map((row) => {
            const isSelected = selectedSize?.toUpperCase() === row.size_code?.toUpperCase();
            const isAvailable = sizes.some((s) => s.toUpperCase() === row.size_code?.toUpperCase());
            return /* @__PURE__ */ jsxs(
              "tr",
              {
                onClick: () => isAvailable && handleSizeSelect(sizes.find((s) => s.toUpperCase() === row.size_code.toUpperCase()) || row.size_code),
                className: cn(
                  "transition-colors text-xs sm:text-sm",
                  isSelected ? "bg-black text-white cursor-pointer hover:bg-gray-900" : isAvailable ? "cursor-pointer hover:bg-gray-50" : "opacity-30 cursor-not-allowed"
                ),
                children: [
                  /* @__PURE__ */ jsx("td", { className: "py-3.5 px-3 font-bold text-left whitespace-nowrap", children: /* @__PURE__ */ jsxs("span", { className: cn("flex items-center gap-2"), children: [
                    /* @__PURE__ */ jsx("span", { className: cn(
                      "w-4 h-4 rounded-full border-2 inline-block shrink-0",
                      isSelected ? "border-white bg-white" : "border-gray-300"
                    ) }),
                    row.size_name || row.size_code
                  ] }) }),
                  sizeChartHeaders.map((header) => /* @__PURE__ */ jsx("td", { className: "py-3.5 px-3", children: row.measurements[header] || "-" }, header))
                ]
              },
              row.size_code
            );
          }) : /* @__PURE__ */ jsx("tr", { children: /* @__PURE__ */ jsx("td", { colSpan: sizeChartHeaders.length + 1, className: "py-8 text-center text-gray-400 italic", children: "No measurement data available." }) }) })
        ] }) }),
        /* @__PURE__ */ jsx("p", { className: "text-center text-xs text-gray-400 mt-3", children: "* Measurements table in Inches" }),
        /* @__PURE__ */ jsxs("div", { className: "mt-10 border-t border-gray-100 pt-6", children: [
          /* @__PURE__ */ jsx("p", { className: "text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-4", children: "Select Color" }),
          /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-4", children: colors.map((color) => /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
            /* @__PURE__ */ jsx(
              "button",
              {
                onClick: () => setSelectedColor(color.value),
                className: cn(
                  "w-10 h-10 rounded-full border-2 transition-all p-0.5",
                  selectedColor === color.value ? "border-black scale-110 shadow-md" : "border-transparent hover:border-gray-200"
                ),
                children: /* @__PURE__ */ jsx(
                  "div",
                  {
                    className: "w-full h-full rounded-full shadow-inner border border-gray-100",
                    style: { backgroundColor: color.meta || "#ccc" }
                  }
                )
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-black text-white text-[10px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-20", children: color.value })
          ] }, color.value)) })
        ] })
      ] }) : /* @__PURE__ */ jsxs("div", { className: "p-6 space-y-6 text-sm text-gray-600 leading-relaxed", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h4", { className: "font-bold text-gray-900 mb-2", children: "How to measure yourself" }),
          /* @__PURE__ */ jsx("p", { children: "Use a soft measuring tape and take measurements over light clothing for the most accurate fit." })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "space-y-4", children: [
          { label: "Bust", desc: "Measure around the fullest part of your chest, keeping the tape horizontal." },
          { label: "Waist", desc: "Measure around your natural waistline, the narrowest part of your torso." },
          { label: "Hips", desc: "Measure around the fullest part of your hips, about 8 inches below your waist." },
          { label: "Length", desc: "Measure from the highest point of your shoulder down to where you want the garment to end." },
          { label: "Shoulder", desc: "Measure from the edge of one shoulder to the edge of the other, across the back." }
        ].map((item) => /* @__PURE__ */ jsxs("div", { className: "flex gap-3", children: [
          /* @__PURE__ */ jsx("span", { className: "font-bold text-gray-900 w-16 shrink-0", children: item.label }),
          /* @__PURE__ */ jsx("span", { children: item.desc })
        ] }, item.label)) })
      ] }) }),
      /* @__PURE__ */ jsxs("div", { className: "shrink-0 p-4 border-t border-gray-100 grid grid-cols-2 gap-3", children: [
        /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: () => {
              addToCart();
              setShowSizeChart(false);
            },
            className: "flex items-center justify-center gap-2 bg-black text-white py-4 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-gray-900 transition-colors active:scale-[0.98]",
            children: [
              /* @__PURE__ */ jsx(ShoppingBag, { className: "w-4 h-4" }),
              "Add to Bag"
            ]
          }
        ),
        /* @__PURE__ */ jsxs("button", { className: "flex items-center justify-center gap-2 bg-white border-2 border-gray-200 text-gray-800 py-4 rounded-xl font-bold uppercase tracking-widest text-sm hover:border-gray-400 transition-colors active:scale-[0.98]", children: [
          /* @__PURE__ */ jsx(Heart, { className: "w-4 h-4" }),
          "Wishlist"
        ] })
      ] })
    ] })
  ] });
}
function ProductPage({ product }) {
  const { settings } = usePage().props;
  const policies = settings?.policies || {};
  const [coupons, setCoupons] = useState([]);
  useEffect(() => {
    api.get("/api/coupons/public").then((res) => setCoupons(res.data.product_coupons || [])).catch((err) => console.error("Failed to load coupons", err));
  }, []);
  if (!product) {
    return /* @__PURE__ */ jsx("div", { className: "flex min-h-screen flex-col items-center justify-center p-24 text-center", children: /* @__PURE__ */ jsx("h1", { className: "text-3xl font-bold mb-4", children: "Product Not Found" }) });
  }
  return /* @__PURE__ */ jsxs("div", { className: "w-full pb-12", children: [
    /* @__PURE__ */ jsx(Head, { children: /* @__PURE__ */ jsx("title", { children: product.name }) }),
    /* @__PURE__ */ jsx(ProductDetailClient, { product, policies, coupons })
  ] });
}
const __vite_glob_0_16 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ProductPage
}, Symbol.toStringTag, { value: "Module" }));
function ShopPage() {
  const { url, app_url } = usePage();
  const searchParams = new URLSearchParams(url.substring(url.indexOf("?")));
  const category = searchParams.get("category");
  const collection = searchParams.get("collection");
  const baseUrl = app_url || "https://dopestyle.in";
  const pageTitle = category || collection || "All Products";
  const getShopSchema = () => {
    return {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": pageTitle,
      "url": `${baseUrl}${url}`
    };
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsxs(Head, { children: [
      /* @__PURE__ */ jsx("title", { children: `Shop - ${pageTitle}` }),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", "head-key": "jsonld", children: JSON.stringify(getShopSchema()) })
    ] }),
    /* @__PURE__ */ jsx(
      ProductListing,
      {
        title: pageTitle,
        baseEndpoint: "/api/products"
      }
    )
  ] });
}
const __vite_glob_0_17 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ShopPage
}, Symbol.toStringTag, { value: "Module" }));
function SearchInput({ initialValue = "" }) {
  const [query, setQuery] = useState(initialValue);
  const [suggestions, setSuggestions] = useState([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const wrapperRef = useRef(null);
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);
  useEffect(() => {
    const fetchSuggestions = async () => {
      try {
        const res = await api.get(`/api/search-suggestions?q=${encodeURIComponent(query)}`);
        setSuggestions(res.data);
      } catch (e) {
        console.error(e);
      }
    };
    const timer = setTimeout(() => {
      fetchSuggestions();
    }, 300);
    return () => clearTimeout(timer);
  }, [query]);
  const handleSubmit = (e) => {
    e.preventDefault();
    if (query.trim()) {
      setShowSuggestions(false);
      router.get("/search", { q: query.trim() });
    }
  };
  const handleSuggestionClick = (suggestion) => {
    setQuery(suggestion);
    setShowSuggestions(false);
    router.get("/search", { q: suggestion });
  };
  return /* @__PURE__ */ jsxs("div", { ref: wrapperRef, className: "relative w-full", children: [
    /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "relative w-full z-10", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none", children: /* @__PURE__ */ jsx(Search, { className: "h-5 w-5 text-gray-400" }) }),
      /* @__PURE__ */ jsx(
        "input",
        {
          type: "text",
          value: query,
          onChange: (e) => {
            setQuery(e.target.value);
            setShowSuggestions(true);
          },
          onFocus: () => setShowSuggestions(true),
          className: "block w-full pl-11 pr-4 py-4 border-2 border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-gray-900 transition-colors bg-gray-50 text-lg font-medium relative z-10",
          placeholder: "Search for shirts, shoes, pants..."
        }
      ),
      /* @__PURE__ */ jsx(
        "button",
        {
          type: "submit",
          className: "absolute inset-y-2 right-2 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition-colors z-20",
          children: "Search"
        }
      )
    ] }),
    showSuggestions && suggestions.length > 0 && /* @__PURE__ */ jsx("div", { className: "absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50", children: /* @__PURE__ */ jsx("ul", { className: "py-2", children: suggestions.map((suggestion, idx) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsxs(
      "button",
      {
        type: "button",
        onClick: () => handleSuggestionClick(suggestion),
        className: "w-full text-left px-5 py-3 hover:bg-gray-50 flex items-center gap-3 transition-colors",
        children: [
          /* @__PURE__ */ jsx(TrendingUp, { className: "w-4 h-4 text-gray-400" }),
          /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-700", children: suggestion })
        ]
      }
    ) }, idx)) }) })
  ] });
}
const __vite_glob_0_19 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  SearchInput
}, Symbol.toStringTag, { value: "Module" }));
function SearchPage() {
  const { url } = usePage();
  const searchParams = new URLSearchParams(url.substring(url.indexOf("?")));
  const q = searchParams.get("q") || "";
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: `Search Results for "${q}"` }),
    /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12", children: [
      /* @__PURE__ */ jsxs("div", { className: "max-w-2xl mx-auto mb-12", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-3xl font-black tracking-tight text-gray-900 mb-6 text-center", children: q ? `Search Results for "${q}"` : "What are you looking for?" }),
        /* @__PURE__ */ jsx(SearchInput, { initialValue: q })
      ] }),
      q ? /* @__PURE__ */ jsx(
        ProductListing,
        {
          title: "",
          baseEndpoint: "/api/search",
          queryKey: "q",
          queryValue: q
        }
      ) : /* @__PURE__ */ jsx("div", { className: "text-center text-gray-500 py-12", children: "Enter a search term above to find products." })
    ] })
  ] });
}
const __vite_glob_0_18 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: SearchPage
}, Symbol.toStringTag, { value: "Module" }));
function WishlistPage() {
  const wishlist = useWishlistStore();
  const [mounted, setMounted] = useState(false);
  useEffect(() => {
    setMounted(true);
  }, []);
  if (!mounted) return /* @__PURE__ */ jsx("div", { className: "min-h-[60vh]" });
  if (wishlist.items.length === 0) return /* @__PURE__ */ jsxs("div", { className: "max-w-sm mx-auto px-4 py-28 text-center min-h-[60vh] flex flex-col items-center justify-center", children: [
    /* @__PURE__ */ jsx(Head, { title: "Wishlist" }),
    /* @__PURE__ */ jsx(Heart, { className: "w-12 h-12 text-gray-200 mx-auto mb-5" }),
    /* @__PURE__ */ jsx("h1", { className: "text-xl font-bold text-gray-900 mb-2", children: "Your wishlist is empty" }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-400 mb-8", children: "Save items you love to view them later." }),
    /* @__PURE__ */ jsxs(Link, { href: "/shop", className: "inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-800 transition-all", children: [
      "Browse Shop ",
      /* @__PURE__ */ jsx(ArrowRight, { size: 15 })
    ] })
  ] });
  return /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16 min-h-[60vh]", children: [
    /* @__PURE__ */ jsx(Head, { title: "Wishlist" }),
    /* @__PURE__ */ jsxs("div", { className: "mb-10", children: [
      /* @__PURE__ */ jsxs("h1", { className: "text-3xl font-bold text-gray-900 flex items-center gap-3", children: [
        /* @__PURE__ */ jsx(Heart, { size: 28, className: "text-red-500 fill-red-50" }),
        "Your Wishlist"
      ] }),
      /* @__PURE__ */ jsxs("p", { className: "text-sm text-gray-400 mt-2", children: [
        wishlist.items.length,
        " ",
        wishlist.items.length === 1 ? "item" : "items",
        " saved for later"
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6", children: wishlist.items.map((item) => /* @__PURE__ */ jsx(
      ProductCard,
      {
        onRemove: () => wishlist.removeItem(item.productId),
        product: {
          id: item.productId,
          name: item.name,
          slug: item.slug,
          price: item.price,
          mrp: item.mrp || item.price,
          discount_percentage: item.discount_percentage || 0,
          image: item.image,
          video: item.video,
          brand: item.brand,
          category: item.category
        }
      },
      item.productId
    )) })
  ] });
}
const __vite_glob_0_20 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: WishlistPage
}, Symbol.toStringTag, { value: "Module" }));
const SearchModalContent = ({ closeSearch }) => {
  const [query, setQuery] = useState("");
  const [suggestions, setSuggestions] = useState([]);
  useEffect(() => {
    const fetchSuggestions = async () => {
      try {
        const res = await api.get(`/api/search-suggestions?q=${encodeURIComponent(query)}`);
        setSuggestions(res.data);
      } catch (e) {
        console.error(e);
      }
    };
    const timer = setTimeout(() => {
      fetchSuggestions();
    }, 300);
    return () => clearTimeout(timer);
  }, [query]);
  const handleSubmit = (e) => {
    e.preventDefault();
    if (query.trim()) {
      closeSearch();
      router.get("/search", { q: query.trim() });
    }
  };
  const handleSuggestionClick = (suggestion) => {
    closeSearch();
    router.get("/search", { q: suggestion });
  };
  return /* @__PURE__ */ jsxs("div", { className: "bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative overflow-hidden transition-all duration-300 flex flex-col", children: [
    /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "p-4 px-6 flex items-center gap-4 border-b border-gray-100", children: [
      /* @__PURE__ */ jsx(Search, { className: "w-6 h-6 text-gray-400 shrink-0" }),
      /* @__PURE__ */ jsx(
        "input",
        {
          type: "text",
          value: query,
          onChange: (e) => setQuery(e.target.value),
          autoFocus: true,
          placeholder: "Search for products, collections...",
          className: "flex-1 text-xl font-medium focus:outline-none focus:ring-0 border-none p-0 bg-transparent"
        }
      ),
      /* @__PURE__ */ jsx("button", { type: "button", onClick: closeSearch, className: "text-gray-400 hover:text-gray-600 px-2 font-bold text-2xl", children: "×" })
    ] }),
    suggestions.length > 0 && /* @__PURE__ */ jsx("div", { className: "max-h-96 overflow-y-auto", children: /* @__PURE__ */ jsx("ul", { className: "py-2", children: suggestions.map((suggestion, idx) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsxs(
      "button",
      {
        type: "button",
        onClick: () => handleSuggestionClick(suggestion),
        className: "w-full text-left px-6 py-3 hover:bg-gray-50 flex items-center gap-4 transition-colors",
        children: [
          /* @__PURE__ */ jsx(TrendingUp, { className: "w-5 h-5 text-gray-400" }),
          /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-700 text-lg", children: suggestion })
        ]
      }
    ) }, idx)) }) })
  ] });
};
function Navbar({ settings }) {
  const cart = useCartStore();
  const { user, logout } = useAuthStore();
  const { openAuthModal: openAuthModal2, isSearchOpen, openSearch, closeSearch } = useUIStore();
  const { items: wishlistItems } = useWishlistStore();
  const [mounted, setMounted] = useState(false);
  const [categories, setCategories] = useState([]);
  const [hiddenMenuId, setHiddenMenuId] = useState(null);
  const [forceOpenMenuId, setForceOpenMenuId] = useState(null);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [activeMobileMenuId, setActiveMobileMenuId] = useState(null);
  const isMegaMenu = settings?.navbar_style === "mega_menu";
  const isCustom = settings?.navbar_style === "custom";
  const alignment = settings?.nav_alignment || "left";
  const position = settings?.nav_position || "inline";
  let menuItems = [];
  try {
    if (settings?.menu_structure) {
      menuItems = JSON.parse(settings.menu_structure);
    }
  } catch (e) {
  }
  useEffect(() => {
    setMounted(true);
    if (isMegaMenu) {
      const apiUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api";
      fetch(`${apiUrl}/categories`).then((res) => res.json()).then((data) => setCategories(data)).catch((err) => console.error("Failed to load categories", err));
    }
  }, [isMegaMenu]);
  if (!mounted) return /* @__PURE__ */ jsx("nav", { className: "h-16 border-b" });
  const storeName = settings?.store_name || "VYORA";
  const logoRelPath = settings?.main_logo;
  const authAppearance = typeof settings?.auth_appearance === "string" ? JSON.parse(settings.auth_appearance) : settings?.auth_appearance || {};
  const isModalMode = authAppearance.ux_mode === "modal";
  let alignmentClasses = "flex items-center space-x-6 ml-10 flex-1";
  if (alignment === "center") alignmentClasses = "flex items-center justify-center space-x-6 flex-1";
  if (alignment === "right") alignmentClasses = "flex items-center justify-end space-x-6 flex-1";
  const hoverStyle = settings?.nav_hover_style || "none";
  const getHoverClasses = (isChild = false) => {
    if (hoverStyle === "none") return "";
    const bottomPos = isChild ? "before:bottom-0" : "before:bottom-2";
    if (hoverStyle === "underline") {
      return `before:absolute ${bottomPos} before:left-0 before:w-full before:h-[2px] before:bg-black before:opacity-0 hover:before:opacity-100 before:transition-opacity before:duration-300`;
    }
    if (hoverStyle === "left_to_right") {
      return `before:absolute ${bottomPos} before:left-0 before:w-0 before:h-[2px] before:bg-black hover:before:w-full before:transition-all before:duration-300 before:ease-out`;
    }
    return "";
  };
  const renderDynamicLink = (item, isChild = false, parentId = null) => {
    let href = "/shop";
    if (item.type === "link") href = item.link || "/";
    if (item.type === "category") href = `/category/${item.ref_id}`;
    if (item.type === "collection") href = `/collection/${item.ref_id}`;
    if (item.type === "page") href = `/p/${item.ref_id}`;
    const handleClick = () => {
      if (parentId) {
        setHiddenMenuId(parentId);
        setForceOpenMenuId(null);
      }
    };
    if (item.type === "image") {
      return /* @__PURE__ */ jsx(Link, { href: item.link || "/shop", className: "block w-full group/promo", onClick: handleClick, children: /* @__PURE__ */ jsx("div", { className: "w-full flex flex-col items-center justify-center relative cursor-pointer", children: item.image_url ? /* @__PURE__ */ jsxs("div", { className: "relative inline-block w-full", children: [
        /* @__PURE__ */ jsx("img", { src: item.image_url, alt: item.label || "Promo", className: "max-w-full h-auto object-contain mx-auto rounded-xl shadow-sm" }),
        item.label && /* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent z-10 pointer-events-none rounded-b-xl", children: /* @__PURE__ */ jsx("span", { className: "text-sm font-bold text-white block text-center", children: item.label }) })
      ] }) : /* @__PURE__ */ jsxs("div", { className: "w-full aspect-[4/5] bg-gray-50 rounded-xl border border-gray-100 flex flex-col items-center justify-center p-4 text-center overflow-hidden", children: [
        /* @__PURE__ */ jsx("span", { className: "text-xs font-bold tracking-widest uppercase text-gray-400 block mb-1", children: "Promo" }),
        /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-gray-900", children: item.label }),
        item.label && /* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent z-10 pointer-events-none", children: /* @__PURE__ */ jsx("span", { className: "text-sm font-bold text-white block", children: item.label }) })
      ] }) }) });
    }
    const className = isChild ? `text-sm text-gray-500 hover:text-black transition-colors py-1 w-fit relative block ${getHoverClasses(true)}` : `text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`;
    return /* @__PURE__ */ jsx(Link, { href, className, onClick: handleClick, children: item.label });
  };
  const DynamicNavItems = () => {
    if (!isCustom) return null;
    return /* @__PURE__ */ jsx(Fragment, { children: menuItems.map((item) => /* @__PURE__ */ jsx("div", { className: "group", onMouseLeave: () => {
      setHiddenMenuId(null);
      setForceOpenMenuId(null);
    }, children: item.type === "mega_menu" ? /* @__PURE__ */ jsxs(Fragment, { children: [
      item.root_type && item.root_type !== "" ? /* @__PURE__ */ jsxs(Link, { href: item.root_type === "url" ? item.root_url || "#" : item.root_type === "category" ? `/shop?category=${item.root_ref_id}` : item.root_type === "collection" ? `/shop?collection=${item.root_ref_id}` : item.root_type === "page" ? `/${item.root_ref_id}` : "#", className: `cursor-pointer text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`, onClick: () => setForceOpenMenuId(null), children: [
        item.label,
        /* @__PURE__ */ jsx("span", { onClick: (e) => {
          e.preventDefault();
          e.stopPropagation();
          setForceOpenMenuId(forceOpenMenuId === item.id ? null : item.id);
        }, className: "p-1 -mr-1", children: /* @__PURE__ */ jsx(ChevronDown, { className: "w-3 h-3 text-gray-400 group-hover:text-black transition-colors" }) })
      ] }) : /* @__PURE__ */ jsxs("div", { className: `cursor-pointer text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`, children: [
        item.label,
        /* @__PURE__ */ jsx("span", { onClick: (e) => {
          e.preventDefault();
          e.stopPropagation();
          setForceOpenMenuId(forceOpenMenuId === item.id ? null : item.id);
        }, className: "p-1 -mr-1", children: /* @__PURE__ */ jsx(ChevronDown, { className: "w-3 h-3 text-gray-400 group-hover:text-black transition-colors" }) })
      ] }),
      /* @__PURE__ */ jsx("div", { className: `absolute left-0 top-full mt-0 w-full bg-white border-b border-t shadow-xl transition-all duration-300 transform origin-top z-[100] ${hiddenMenuId === item.id && forceOpenMenuId !== item.id ? "opacity-0 invisible pointer-events-none" : forceOpenMenuId === item.id ? "opacity-100 visible translate-y-0" : "opacity-0 invisible group-hover:opacity-100 group-hover:visible -translate-y-2 group-hover:translate-y-0"}`, children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto px-4 py-8", children: /* @__PURE__ */ jsx("div", { className: `grid gap-8 grid-cols-${item.columns || 4} items-start`, children: item.layout_columns?.map((col) => /* @__PURE__ */ jsx("div", { className: "flex flex-col gap-6", children: col.blocks?.map((block) => /* @__PURE__ */ jsx("div", { children: block.type === "image" ? renderDynamicLink(block, true, item.id) : /* @__PURE__ */ jsxs("div", { children: [
        block.label && /* @__PURE__ */ jsx("div", { className: "border-b border-gray-100 pb-2 mb-3", children: block.link ? /* @__PURE__ */ jsx(Link, { href: block.link, className: "text-sm font-black text-gray-900 uppercase tracking-wide hover:text-black", onClick: () => {
          setHiddenMenuId(item.id);
          setForceOpenMenuId(null);
        }, children: block.label }) : /* @__PURE__ */ jsx("span", { className: "text-sm font-black text-gray-900 uppercase tracking-wide", children: block.label }) }),
        block.links && block.links.length > 0 && /* @__PURE__ */ jsx("ul", { className: "space-y-1.5", children: block.links.map((link) => /* @__PURE__ */ jsx("li", { children: renderDynamicLink(link, true, item.id) }, link.id || Math.random())) })
      ] }) }, block.id || Math.random())) }, col.id || Math.random())) }) }) })
    ] }) : renderDynamicLink(item) }, item.id)) });
  };
  const ActionsComponent = () => /* @__PURE__ */ jsxs("div", { className: "hidden md:flex items-center space-x-6 ml-auto shrink-0", children: [
    user ? /* @__PURE__ */ jsxs("div", { className: "flex items-center space-x-4", children: [
      /* @__PURE__ */ jsx(Link, { href: "/orders", className: "text-sm font-medium hover:text-gray-600", children: "My Orders" }),
      /* @__PURE__ */ jsx(Link, { href: "/account", className: "text-gray-900 hover:text-gray-600 transition-colors", children: /* @__PURE__ */ jsx(User, { className: "w-5 h-5" }) })
    ] }) : isModalMode ? /* @__PURE__ */ jsxs(
      "button",
      {
        onClick: () => openAuthModal2("login"),
        className: "text-sm font-medium hover:text-gray-600 flex items-center gap-1",
        children: [
          /* @__PURE__ */ jsx(User, { className: "w-4 h-4" }),
          " Sign In"
        ]
      }
    ) : /* @__PURE__ */ jsxs(Link, { href: "/login", className: "text-sm font-medium hover:text-gray-600 flex items-center gap-1", children: [
      /* @__PURE__ */ jsx(User, { className: "w-4 h-4" }),
      " Sign In"
    ] }),
    /* @__PURE__ */ jsx("button", { onClick: openSearch, className: "text-gray-900 hover:text-gray-600 transition-colors", children: /* @__PURE__ */ jsx(Search, { className: "w-5 h-5" }) }),
    /* @__PURE__ */ jsxs(
      Link,
      {
        href: user ? "/wishlist" : "#",
        onClick: (e) => {
          if (!user) {
            e.preventDefault();
            openAuthModal2();
          }
        },
        className: "relative text-gray-900 hover:text-gray-600 transition-colors",
        children: [
          /* @__PURE__ */ jsx(Heart, { className: "w-5 h-5" }),
          wishlistItems.length > 0 && /* @__PURE__ */ jsx("span", { className: "absolute -top-1 -right-1 bg-black text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full font-bold", children: wishlistItems.length })
        ]
      }
    ),
    /* @__PURE__ */ jsxs(Link, { href: "/cart", className: "relative text-gray-900 hover:text-gray-600", children: [
      /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5" }),
      cart.items.length > 0 && /* @__PURE__ */ jsx("span", { className: "absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full", children: cart.items.length })
    ] })
  ] });
  return /* @__PURE__ */ jsxs("header", { className: "bg-white sticky top-0 z-50 shadow-sm relative", children: [
    /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 h-16 flex items-center justify-center md:justify-between relative", children: [
      /* @__PURE__ */ jsx("div", { className: "flex items-center gap-4 absolute left-1/2 -translate-x-1/2 md:relative md:left-0 md:translate-x-0", children: /* @__PURE__ */ jsx(Link, { href: "/", className: "flex items-center gap-2 shrink-0", children: logoRelPath ? /* @__PURE__ */ jsx("img", { src: `/${logoRelPath}`, alt: storeName, className: "h-8 w-auto object-contain" }) : /* @__PURE__ */ jsx("span", { className: "text-xl font-bold tracking-tighter", style: { fontFamily: "var(--font-heading)" }, children: storeName }) }) }),
      position === "inline" && /* @__PURE__ */ jsxs("div", { className: `${alignmentClasses} hidden md:flex`, children: [
        isMegaMenu && /* @__PURE__ */ jsx(Fragment, { children: categories.map((cat) => /* @__PURE__ */ jsxs("div", { className: "group", onMouseLeave: () => setHiddenMenuId(null), children: [
          /* @__PURE__ */ jsxs(Link, { href: `/shop?category=${cat.slug}`, className: "text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5", children: [
            cat.name,
            cat.children && cat.children.length > 0 && /* @__PURE__ */ jsx("span", { onClick: (e) => {
              e.preventDefault();
              e.stopPropagation();
              setForceOpenMenuId(forceOpenMenuId === cat.id ? null : cat.id);
            }, className: "p-1 -mr-1", children: /* @__PURE__ */ jsx(ChevronDown, { className: "w-3 h-3 text-gray-400 group-hover:text-black transition-colors" }) })
          ] }),
          cat.children && cat.children.length > 0 && /* @__PURE__ */ jsx("div", { className: `absolute left-0 top-[64px] w-full bg-white border-b border-t shadow-xl transition-all duration-300 transform origin-top z-[100] ${hiddenMenuId === cat.id && forceOpenMenuId !== cat.id ? "opacity-0 invisible pointer-events-none" : forceOpenMenuId === cat.id ? "opacity-100 visible translate-y-0" : "opacity-0 invisible group-hover:opacity-100 group-hover:visible -translate-y-2 group-hover:translate-y-0"}`, children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto px-4 py-8", children: /* @__PURE__ */ jsx("div", { className: "flex gap-16", children: /* @__PURE__ */ jsx("div", { className: "flex-1 grid grid-cols-3 gap-8", children: cat.children.map((sub) => /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx(Link, { href: `/shop?category=${sub.slug}`, className: "text-sm font-bold text-gray-900 border-b pb-2 mb-3 block hover:text-gray-600 uppercase tracking-wide", onClick: () => setHiddenMenuId(cat.id), children: sub.name }),
            sub.children && sub.children.length > 0 && /* @__PURE__ */ jsx("ul", { className: "space-y-2.5 mt-4", children: sub.children.map((deep) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(Link, { href: `/shop?category=${deep.slug}`, className: "text-sm text-gray-600 hover:text-black transition-colors block font-medium", onClick: () => setHiddenMenuId(cat.id), children: deep.name }) }, deep.id)) })
          ] }, sub.id)) }) }) }) })
        ] }, cat.id)) }),
        isCustom && /* @__PURE__ */ jsx(DynamicNavItems, {}),
        !isMegaMenu && !isCustom && /* @__PURE__ */ jsx(Link, { href: "/shop", className: "text-sm font-medium hover:text-gray-600 py-5", children: "Shop" })
      ] }),
      /* @__PURE__ */ jsx(ActionsComponent, {})
    ] }),
    position === "below" && isCustom && /* @__PURE__ */ jsx("div", { className: "border-t border-gray-100 bg-gray-50/50 hidden md:block relative", children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto px-4", children: /* @__PURE__ */ jsx("div", { className: `flex items-center ${alignment === "left" ? "justify-start" : alignment === "right" ? "justify-end" : "justify-center"} space-x-8`, children: /* @__PURE__ */ jsx(DynamicNavItems, {}) }) }) }),
    isMobileMenuOpen && /* @__PURE__ */ jsx("div", { className: "md:hidden absolute top-16 left-0 w-full bg-white border-b shadow-lg overflow-y-auto z-50 flex flex-col", style: { maxHeight: "calc(100vh - 7.5rem - env(safe-area-inset-bottom))" }, children: /* @__PURE__ */ jsxs("div", { className: "p-4 flex flex-col gap-4 pb-20", children: [
      isMegaMenu && categories.map((cat) => /* @__PURE__ */ jsxs("div", { className: "border-b pb-2", children: [
        /* @__PURE__ */ jsxs(
          "div",
          {
            className: "flex items-center justify-between font-bold text-gray-900 py-2 cursor-pointer",
            onClick: () => setActiveMobileMenuId(activeMobileMenuId === cat.id ? null : cat.id),
            children: [
              /* @__PURE__ */ jsx("span", { children: cat.name }),
              cat.children && cat.children.length > 0 && /* @__PURE__ */ jsx(ChevronDown, { className: `w-4 h-4 transition-transform ${activeMobileMenuId === cat.id ? "rotate-180" : ""}` })
            ]
          }
        ),
        activeMobileMenuId === cat.id && cat.children && /* @__PURE__ */ jsx("div", { className: "pl-4 py-2 flex flex-col gap-3", children: cat.children.map((sub) => /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx(Link, { href: `/shop?category=${sub.slug}`, className: "font-semibold text-gray-800 text-sm py-1 block", onClick: () => setIsMobileMenuOpen(false), children: sub.name }),
          sub.children && sub.children.length > 0 && /* @__PURE__ */ jsx("div", { className: "pl-3 mt-1 flex flex-col gap-2", children: sub.children.map((deep) => /* @__PURE__ */ jsx(Link, { href: `/shop?category=${deep.slug}`, className: "text-gray-500 text-sm hover:text-black py-1 block", onClick: () => setIsMobileMenuOpen(false), children: deep.name }, deep.id)) })
        ] }, sub.id)) })
      ] }, cat.id)),
      isCustom && menuItems.map((item) => /* @__PURE__ */ jsx("div", { className: "border-b pb-2", children: item.type === "mega_menu" ? /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsxs(
          "div",
          {
            className: "flex items-center justify-between font-bold text-gray-900 py-2 cursor-pointer",
            onClick: () => setActiveMobileMenuId(activeMobileMenuId === item.id ? null : item.id),
            children: [
              /* @__PURE__ */ jsx("span", { children: item.label }),
              /* @__PURE__ */ jsx(ChevronDown, { className: `w-4 h-4 transition-transform ${activeMobileMenuId === item.id ? "rotate-180" : ""}` })
            ]
          }
        ),
        activeMobileMenuId === item.id && /* @__PURE__ */ jsx("div", { className: "pl-4 py-2 flex flex-col gap-4", children: item.layout_columns?.map((col) => /* @__PURE__ */ jsx("div", { className: "flex flex-col gap-3", children: col.blocks?.map((block) => /* @__PURE__ */ jsx("div", { children: block.type === "image" ? /* @__PURE__ */ jsx("div", { className: "w-[150px] mt-2", onClick: () => setIsMobileMenuOpen(false), children: renderDynamicLink(block, true) }) : /* @__PURE__ */ jsxs("div", { children: [
          block.label && /* @__PURE__ */ jsx("div", { className: "font-semibold text-gray-800 text-sm mb-2", onClick: () => setIsMobileMenuOpen(false), children: block.link ? /* @__PURE__ */ jsx(Link, { href: block.link, children: block.label }) : block.label }),
          block.links && /* @__PURE__ */ jsx("div", { className: "pl-2 flex flex-col gap-2", children: block.links.map((link) => /* @__PURE__ */ jsx("div", { onClick: () => setIsMobileMenuOpen(false), children: renderDynamicLink(link, true) }, link.id || Math.random())) })
        ] }) }, block.id || Math.random())) }, col.id || Math.random())) })
      ] }) : /* @__PURE__ */ jsx("div", { className: "font-bold text-gray-900 py-2", onClick: () => setIsMobileMenuOpen(false), children: renderDynamicLink(item) }) }, item.id)),
      !isMegaMenu && !isCustom && /* @__PURE__ */ jsx(Link, { href: "/shop", className: "font-bold text-gray-900 py-2 border-b block", onClick: () => setIsMobileMenuOpen(false), children: "Shop" })
    ] }) }),
    isSearchOpen && /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm flex items-start justify-center pt-24 px-4", children: [
      /* @__PURE__ */ jsx(
        "div",
        {
          className: "absolute inset-0",
          onClick: closeSearch
        }
      ),
      /* @__PURE__ */ jsx(SearchModalContent, { closeSearch })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-[100] pb-[env(safe-area-inset-bottom)]", children: /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between px-1 h-14", children: [
      /* @__PURE__ */ jsxs(Link, { href: "/", className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black", children: [
        /* @__PURE__ */ jsx(Home$1, { className: "w-5 h-5" }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "Home" })
      ] }),
      user ? /* @__PURE__ */ jsxs(Link, { href: "/account", className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black", children: [
        /* @__PURE__ */ jsx(User, { className: "w-5 h-5" }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "User" })
      ] }) : isModalMode ? /* @__PURE__ */ jsxs("button", { onClick: () => openAuthModal2("login"), className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black", children: [
        /* @__PURE__ */ jsx(User, { className: "w-5 h-5" }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "User" })
      ] }) : /* @__PURE__ */ jsxs(Link, { href: "/login", className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black", children: [
        /* @__PURE__ */ jsx(User, { className: "w-5 h-5" }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "User" })
      ] }),
      /* @__PURE__ */ jsxs("button", { onClick: openSearch, className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black", children: [
        /* @__PURE__ */ jsx(Search, { className: "w-5 h-5" }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "Search" })
      ] }),
      /* @__PURE__ */ jsxs(
        Link,
        {
          href: user ? "/wishlist" : "#",
          onClick: (e) => {
            if (!user) {
              e.preventDefault();
              openAuthModal2();
            }
          },
          className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black relative",
          children: [
            /* @__PURE__ */ jsxs("div", { className: "relative", children: [
              /* @__PURE__ */ jsx(Heart, { className: "w-5 h-5" }),
              wishlistItems.length > 0 && /* @__PURE__ */ jsx("span", { className: "absolute -top-1.5 -right-2 bg-black text-white text-[9px] w-3.5 h-3.5 flex items-center justify-center rounded-full font-bold", children: wishlistItems.length })
            ] }),
            /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "Wishlist" })
          ]
        }
      ),
      /* @__PURE__ */ jsxs(Link, { href: "/cart", className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black relative", children: [
        /* @__PURE__ */ jsxs("div", { className: "relative", children: [
          /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5" }),
          cart.items.length > 0 && /* @__PURE__ */ jsx("span", { className: "absolute -top-1.5 -right-2 bg-primary text-white text-[9px] w-3.5 h-3.5 flex items-center justify-center rounded-full font-bold", children: cart.items.length })
        ] }),
        /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "Cart" })
      ] }),
      /* @__PURE__ */ jsxs(
        "button",
        {
          className: "flex flex-col items-center justify-center w-full gap-1 text-gray-500 hover:text-black",
          onClick: () => setIsMobileMenuOpen(!isMobileMenuOpen),
          children: [
            isMobileMenuOpen ? /* @__PURE__ */ jsx(X, { className: "w-5 h-5" }) : /* @__PURE__ */ jsx(Menu, { className: "w-5 h-5" }),
            /* @__PURE__ */ jsx("span", { className: "text-[9px] font-medium tracking-wide", children: "Menu" })
          ]
        }
      )
    ] }) })
  ] });
}
function AuthModal() {
  const { isAuthModalOpen, authView, closeAuthModal, setAuthView } = useUIStore();
  const { settings } = usePage().props;
  const [mounted, setMounted] = useState(false);
  useEffect(() => {
    setMounted(true);
  }, []);
  useEffect(() => {
    const handleEsc = (e) => {
      if (e.key === "Escape") closeAuthModal();
    };
    window.addEventListener("keydown", handleEsc);
    return () => window.removeEventListener("keydown", handleEsc);
  }, [closeAuthModal]);
  if (!mounted || !isAuthModalOpen) return null;
  const parse = (val) => {
    if (typeof val === "string") {
      try {
        return JSON.parse(val);
      } catch {
        return {};
      }
    }
    return val || {};
  };
  const authAppearance = parse(settings.auth_appearance);
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-[100] flex items-center justify-center p-4", children: [
    /* @__PURE__ */ jsx(
      "div",
      {
        className: "absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity",
        onClick: closeAuthModal
      }
    ),
    /* @__PURE__ */ jsxs(
      "div",
      {
        className: "relative w-full max-w-[440px] bg-white shadow-2xl animate-in fade-in zoom-in duration-300",
        style: {
          borderRadius: authAppearance.border_radius ? `${authAppearance.border_radius}px` : "24px",
          borderColor: authAppearance.border_color,
          borderWidth: authAppearance.border_color ? "1px" : "0px"
        },
        children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: closeAuthModal,
              className: "absolute top-5 right-5 z-10 p-2 rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-black",
              children: /* @__PURE__ */ jsx(X, { className: "w-5 h-5" })
            }
          ),
          /* @__PURE__ */ jsx("div", { className: "p-8", children: authView === "login" ? /* @__PURE__ */ jsx(
            LoginForm,
            {
              settings,
              isModal: true,
              onSuccess: closeAuthModal,
              onSwitchToRegister: () => setAuthView("register")
            }
          ) : /* @__PURE__ */ jsx(
            RegisterForm,
            {
              settings,
              isModal: true,
              onSuccess: closeAuthModal,
              onSwitchToLogin: () => setAuthView("login")
            }
          ) })
        ]
      }
    )
  ] });
}
function QuickViewModal() {
  const { quickViewProduct: product, quickViewAction: action, closeQuickView } = useUIStore();
  const cart = useCartStore();
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  useEffect(() => {
    if (product) {
      setSelectedColor(null);
      setSelectedSize(null);
    }
  }, [product]);
  const colors = useMemo(() => {
    if (!product) return [];
    const all = /* @__PURE__ */ new Map();
    product.variants?.forEach((v) => {
      const c = v.attributes.find((a) => a.name === "Color");
      if (c && !all.has(c.value)) all.set(c.value, c);
    });
    return Array.from(all.values());
  }, [product]);
  const sizes = useMemo(() => {
    if (!product) return [];
    const all = /* @__PURE__ */ new Set();
    product.variants?.forEach((v) => {
      const s = v.attributes.find((a) => a.name === "Size");
      if (s) all.add(s.value);
    });
    return Array.from(all);
  }, [product]);
  const currentVariant = useMemo(() => {
    if (!product) return null;
    typeof window !== "undefined" && window.location.pathname.startsWith("/product/");
    if (colors.length > 0 && !selectedColor) return null;
    if (sizes.length > 0 && !selectedSize) return null;
    return product.variants.find((v) => {
      const matchColor = colors.length === 0 || v.attributes.find((a) => a.name === "Color")?.value === selectedColor;
      const matchSize = sizes.length === 0 || v.attributes.find((a) => a.name === "Size")?.value === selectedSize;
      return matchColor && matchSize;
    });
  }, [product, selectedColor, selectedSize, colors, sizes]);
  const displayImage = useMemo(() => {
    if (!product) return "";
    if (selectedColor) {
      const colorObj = colors.find((c) => c.value === selectedColor);
      if (colorObj) {
        const img = product.images?.find((i) => i.color_id?.toString() === colorObj.id?.toString());
        if (img) return img.url;
      }
    }
    const primary = product.images?.find((i) => i.is_primary);
    return primary?.url || product.images?.[0]?.url || product.image || "";
  }, [product, selectedColor, colors]);
  if (!product) return null;
  const isPDP = typeof window !== "undefined" && window.location.pathname.startsWith("/product/");
  const handleAction = () => {
    if (!currentVariant) return alert("Please select all options.");
    const colorObj = colors.find((c) => c.value === selectedColor);
    let variantLabel = "";
    if (selectedColor && selectedSize) variantLabel = `${selectedColor} - ${selectedSize}`;
    else if (selectedColor) variantLabel = selectedColor;
    else if (selectedSize) variantLabel = selectedSize;
    const colorImg = colorObj ? product.images?.find((img) => img.color_id?.toString() === colorObj.id?.toString()) : null;
    cart.addItem({
      skuId: currentVariant.id,
      productId: product.id,
      name: product.name,
      slug: product.slug,
      variant: variantLabel,
      price: currentVariant.price,
      mrp: currentVariant.mrp,
      image: colorImg?.url || product.image || product.images?.[0]?.url || "",
      quantity: 1,
      colorName: selectedColor || void 0,
      colorHex: colorObj?.meta || void 0,
      sizeName: selectedSize || void 0,
      size: selectedSize || void 0,
      deliveryDate: product.delivery_timeline?.formatted_date || void 0
    });
    closeQuickView();
    if (action === "buy") {
      router.visit("/checkout");
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-black/60 backdrop-blur-sm", onClick: closeQuickView }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]", children: [
      /* @__PURE__ */ jsxs("div", { className: "p-4 border-b flex justify-between items-center bg-gray-50 shrink-0", children: [
        /* @__PURE__ */ jsx("h2", { className: "font-bold text-lg truncate pr-4", children: product.name }),
        /* @__PURE__ */ jsx("button", { onClick: closeQuickView, className: "p-1.5 hover:bg-gray-200 rounded-full transition-colors", children: /* @__PURE__ */ jsx(X, { size: 18 }) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "p-6 overflow-y-auto flex-1 space-y-6 flex flex-col md:flex-row gap-6", children: [
        /* @__PURE__ */ jsx("div", { className: `${isPDP ? "w-[40%] md:w-[30%]" : "w-[50%] md:w-2/5"} mx-auto shrink-0`, children: /* @__PURE__ */ jsx("div", { className: "aspect-[3/4] bg-gray-100 rounded-xl overflow-hidden relative border border-gray-200", children: displayImage ? /* @__PURE__ */ jsx("img", { src: displayImage, alt: product.name, className: "absolute inset-0 w-full h-full object-cover" }) : /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center justify-center text-gray-400", children: "No Image" }) }) }),
        /* @__PURE__ */ jsxs("div", { className: "flex-1 space-y-6", children: [
          colors.length > 0 && /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("p", { className: "text-xs font-bold text-gray-400 uppercase tracking-widest mb-3", children: "Color" }),
            /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: colors.map((c) => /* @__PURE__ */ jsx(
              "button",
              {
                onClick: () => setSelectedColor(c.value),
                className: `w-10 h-10 rounded-full border-2 transition-all ${selectedColor === c.value ? "border-black ring-2 ring-black/20 scale-110" : "border-gray-200 hover:border-gray-400"}`,
                style: { backgroundColor: c.meta || "#ccc" },
                title: c.value
              },
              c.value
            )) })
          ] }),
          sizes.length > 0 && /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-3", children: [
              /* @__PURE__ */ jsx("p", { className: "text-xs font-bold text-gray-400 uppercase tracking-widest", children: "Size" }),
              product.size_chart && /* @__PURE__ */ jsxs(Link, { href: `/product/${product.slug}`, onClick: closeQuickView, className: "text-[10px] font-bold text-gray-500 underline uppercase tracking-wider hover:text-black transition-colors underline-offset-4 flex items-center gap-1", children: [
                /* @__PURE__ */ jsx(Ruler, { size: 12 }),
                " View Size Chart"
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: sizes.map((s) => {
              const inStock = product.variants.some(
                (v) => (!selectedColor || v.attributes.find((a) => a.name === "Color")?.value === selectedColor) && v.attributes.find((a) => a.name === "Size")?.value === s && v.stock > 0
              );
              return /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: () => {
                    if (inStock) setSelectedSize(s);
                  },
                  disabled: !inStock,
                  className: `min-w-[3rem] h-10 px-3 rounded-xl border font-bold text-sm transition-all
                                                ${selectedSize === s ? "border-black bg-black text-white" : !inStock ? "opacity-30 border-gray-200 cursor-not-allowed bg-gray-50" : "border-gray-200 hover:border-black text-gray-700 bg-white"}`,
                  children: s
                },
                s
              );
            }) })
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "p-4 border-t bg-gray-50 flex items-center justify-between gap-4 shrink-0", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-baseline gap-2", children: [
            /* @__PURE__ */ jsx("span", { className: "text-xl font-black text-gray-900 leading-none", children: formatPrice(currentVariant ? currentVariant.price : product.price) }),
            (currentVariant ? currentVariant.mrp : product.mrp) > (currentVariant ? currentVariant.price : product.price) && /* @__PURE__ */ jsx("span", { className: "text-xs font-semibold text-gray-400 line-through", children: formatPrice(currentVariant ? currentVariant.mrp : product.mrp) })
          ] }),
          product.coupon_price && /* @__PURE__ */ jsxs("div", { className: "text-[10px] text-gray-500 mt-1 font-medium bg-green-50/50 px-1.5 py-0.5 rounded border border-green-100/50 inline-block w-max", children: [
            "Best Price ",
            /* @__PURE__ */ jsx("span", { className: "text-green-700 font-bold", children: formatPrice(product.coupon_price) }),
            " with coupon"
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-2 flex-1 max-w-[360px]", children: [
          !isPDP && /* @__PURE__ */ jsx(
            Link,
            {
              href: `/product/${product.slug}`,
              onClick: closeQuickView,
              className: "flex-1 bg-white border border-gray-300 text-gray-800 px-4 py-3 rounded-xl font-bold tracking-widest text-[10px] hover:bg-gray-50 text-center uppercase flex items-center justify-center whitespace-nowrap",
              children: "View Details"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: handleAction,
              disabled: !currentVariant || currentVariant.stock <= 0,
              className: "flex-1 bg-black text-white px-4 py-3 rounded-xl font-bold uppercase tracking-widest text-[10px] hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap",
              children: action === "buy" ? "Buy Now" : "Add to Cart"
            }
          )
        ] })
      ] })
    ] })
  ] });
}
function Footer() {
  const { settings } = usePage().props;
  const bgColor = settings.footer_bg_color || "#ffffff";
  const textColor = settings.footer_text_color || "#000000";
  const structure = settings.footer_structure || [];
  const showNewsletter = settings.footer_show_newsletter == "1";
  const showSocial = settings.footer_social_links == "1";
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState("");
  const handleSubscribe = async (e) => {
    e.preventDefault();
    if (!email) return;
    setLoading(true);
    setError("");
    try {
      await api.post("/api/subscribe", { email });
      setSubmitted(true);
    } catch (err) {
      if (err.response?.status === 422) {
        if (err.response.data.errors?.email?.[0].includes("already")) {
          setError("This email is already subscribed.");
        } else {
          setError(err.response.data.errors?.email?.[0] || "Invalid email address.");
        }
      } else {
        setError("Something went wrong. Please try again.");
      }
    } finally {
      setLoading(false);
    }
  };
  const socialLinks = [
    { key: "social_facebook", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z", clipRule: "evenodd" }) }) },
    { key: "social_instagram", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z", clipRule: "evenodd" }) }) },
    { key: "social_linkedin", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { d: "M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z" }) }) },
    { key: "social_twitter", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { d: "M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" }) }) },
    { key: "social_tiktok", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { d: "M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.78-1.15 5.54-3.33 7.39-1.92 1.63-4.58 2.37-7.05 1.83-2.61-.58-4.88-2.31-5.91-4.78-1.1-2.63-.82-5.78.83-8.15 1.48-2.12 3.86-3.44 6.43-3.71v4.19c-1.33.15-2.58.76-3.41 1.76-1.19 1.44-1.25 3.65-.12 5.16 1.05 1.41 3.05 1.95 4.67 1.3 1.57-.63 2.56-2.19 2.66-3.88.13-2.5.06-5.02.09-7.53.03-3.72-.01-7.44.02-11.16H12.525z" }) }) },
    { key: "social_pinterest", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { d: "M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.951-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.366 18.607 0 12.017 0z" }) }) },
    { key: "social_youtube", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 01-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 01-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 011.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418zM15.194 12L10 15V9l5.194 3z", clipRule: "evenodd" }) }) },
    { key: "social_whatsapp", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { d: "M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a5.8 5.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" }) }) },
    { key: "social_arattai", icon: /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.44 1.223zM8.25 10.875a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25zM10.875 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875-1.125a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z", clipRule: "evenodd" }) }) }
  ];
  const availableSocials = socialLinks.filter((s) => settings.general && settings.general[s.key]);
  return /* @__PURE__ */ jsxs("footer", { style: { backgroundColor: bgColor, color: textColor }, className: "mt-auto border-t border-gray-100", children: [
    showNewsletter && /* @__PURE__ */ jsx("div", { className: "border-b", style: { borderColor: `${textColor}22` }, children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 md:px-8 py-12 md:py-16", children: /* @__PURE__ */ jsxs("div", { className: "max-w-xl mx-auto text-center", children: [
      /* @__PURE__ */ jsx("h3", { className: "text-2xl md:text-3xl font-black mb-4 tracking-tight", children: "Subscribe to our newsletter" }),
      /* @__PURE__ */ jsx("p", { className: "mb-6 opacity-80", children: "Get the latest updates, drops, and promotions straight to your inbox." }),
      submitted ? /* @__PURE__ */ jsx("p", { className: "text-xl font-bold text-green-600", children: "🎉 You're in! Thanks for subscribing." }) : /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsxs("form", { className: "flex w-full gap-2", onSubmit: handleSubscribe, children: [
          /* @__PURE__ */ jsx(
            "input",
            {
              type: "email",
              value: email,
              onChange: (e) => setEmail(e.target.value),
              placeholder: "Enter your email",
              className: "flex-1 rounded-xl border-gray-300 focus:ring-black focus:border-black py-3 px-4 text-gray-900",
              required: true
            }
          ),
          /* @__PURE__ */ jsx("button", { type: "submit", disabled: loading, className: "bg-black text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition-colors disabled:opacity-60", children: loading ? "..." : "Subscribe" })
        ] }),
        error && /* @__PURE__ */ jsx("p", { className: "text-red-500 text-sm mt-3", children: error })
      ] })
    ] }) }) }),
    /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 md:px-8 py-12 md:py-16", children: /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12", children: structure.map((col) => /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h4", { className: "font-bold text-lg mb-6 uppercase tracking-wider", children: col.title }),
      /* @__PURE__ */ jsx("ul", { className: "space-y-4", children: col.items.map((item) => {
        if (item.type === "link") {
          return /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(Link, { href: item.url, className: "opacity-80 hover:opacity-100 hover:underline transition-all", children: item.label }) }, item.id);
        } else if (item.type === "phone") {
          return /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: `tel:${item.url}`, className: "opacity-80 hover:opacity-100 hover:underline transition-all", children: item.label }) }, item.id);
        } else if (item.type === "email") {
          return /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: `mailto:${item.url}`, className: "opacity-80 hover:opacity-100 hover:underline transition-all", children: item.label }) }, item.id);
        } else if (item.type === "legal") {
          return /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(Link, { href: `/policy/${item.legal_slug}`, className: "opacity-80 hover:opacity-100 hover:underline transition-all", children: item.label || item.legal_slug.replace(/-/g, " ") }) }, item.id);
        } else if (item.type === "text") {
          return /* @__PURE__ */ jsx("li", { className: "opacity-80 whitespace-pre-line leading-relaxed", children: item.content }, item.id);
        } else if (item.type === "image_text") {
          const imgElement = item.image_url ? /* @__PURE__ */ jsx("img", { src: item.image_url, alt: "Footer Image", className: "max-w-full h-auto rounded mb-3 max-h-16 object-contain" }) : null;
          const txtElement = item.content ? /* @__PURE__ */ jsx("div", { className: "whitespace-pre-line leading-relaxed", children: item.content }) : null;
          return /* @__PURE__ */ jsxs("li", { className: "opacity-80", children: [
            imgElement && (item.image_link ? /* @__PURE__ */ jsx("a", { href: item.image_link, target: "_blank", rel: "noreferrer", className: "block hover:opacity-90 transition-opacity", children: imgElement }) : imgElement),
            txtElement && (item.text_link ? /* @__PURE__ */ jsx("a", { href: item.text_link, target: "_blank", rel: "noreferrer", className: "block hover:underline hover:opacity-100 transition-all", children: txtElement }) : txtElement)
          ] }, item.id);
        } else if (item.type === "payment_badges") {
          return /* @__PURE__ */ jsxs("li", { className: "mt-2", children: [
            item.label && /* @__PURE__ */ jsx("div", { className: "opacity-80 mb-2", children: item.label }),
            /* @__PURE__ */ jsxs("div", { className: "flex gap-3 items-center flex-wrap text-gray-700 mt-2", children: [
              item.show_card && /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-1", children: [
                /* @__PURE__ */ jsx("div", { className: "w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm", style: { borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }, title: "Card Payment", children: /* @__PURE__ */ jsx(CreditCard, { size: 20, strokeWidth: 1.5 }) }),
                /* @__PURE__ */ jsx("span", { className: "text-[9px] font-bold tracking-widest uppercase opacity-70", children: "Card" })
              ] }),
              item.show_wallet && /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-1", children: [
                /* @__PURE__ */ jsx("div", { className: "w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm", style: { borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }, title: "Wallet", children: /* @__PURE__ */ jsx(Wallet, { size: 20, strokeWidth: 1.5 }) }),
                /* @__PURE__ */ jsx("span", { className: "text-[9px] font-bold tracking-widest uppercase opacity-70", children: "Wallet" })
              ] }),
              item.show_upi && /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-1", children: [
                /* @__PURE__ */ jsx("div", { className: "w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm", style: { borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }, title: "UPI", children: /* @__PURE__ */ jsx(Smartphone, { size: 20, strokeWidth: 1.5 }) }),
                /* @__PURE__ */ jsx("span", { className: "text-[9px] font-bold tracking-widest uppercase opacity-70", children: "UPI" })
              ] }),
              item.show_netbanking && /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-1", children: [
                /* @__PURE__ */ jsx("div", { className: "w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm", style: { borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }, title: "Netbanking", children: /* @__PURE__ */ jsx(Landmark, { size: 20, strokeWidth: 1.5 }) }),
                /* @__PURE__ */ jsx("span", { className: "text-[9px] font-bold tracking-widest uppercase opacity-70", children: "NB" })
              ] })
            ] })
          ] }, item.id);
        } else if (item.type === "app_links") {
          return /* @__PURE__ */ jsxs("li", { className: "mt-2 flex flex-col gap-2", children: [
            item.ios_url && /* @__PURE__ */ jsx("a", { href: item.ios_url, target: "_blank", rel: "noreferrer", className: "inline-block transition-opacity hover:opacity-80", children: /* @__PURE__ */ jsx("img", { src: "https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg", alt: "Download on the App Store", className: "h-10 w-auto" }) }),
            item.android_url && /* @__PURE__ */ jsx("a", { href: item.android_url, target: "_blank", rel: "noreferrer", className: "inline-block transition-opacity hover:opacity-80", children: /* @__PURE__ */ jsx("img", { src: "https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg", alt: "Get it on Google Play", className: "h-10 w-auto" }) })
          ] }, item.id);
        } else if (item.type === "store_map") {
          return /* @__PURE__ */ jsxs("li", { className: "mt-2", children: [
            item.label && /* @__PURE__ */ jsx("div", { className: "opacity-80 mb-1", children: item.label }),
            item.content && /* @__PURE__ */ jsx("p", { className: "opacity-80 whitespace-pre-line leading-relaxed mb-2", children: item.content }),
            item.url && /* @__PURE__ */ jsxs("a", { href: item.url, target: "_blank", rel: "noreferrer", className: "inline-flex items-center gap-1.5 hover:underline opacity-80 hover:opacity-100 transition-all", children: [
              /* @__PURE__ */ jsxs("svg", { className: "w-4 h-4", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: [
                /* @__PURE__ */ jsx("path", { "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" }),
                /* @__PURE__ */ jsx("path", { "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M15 11a3 3 0 11-6 0 3 3 0 016 0z" })
              ] }),
              "Get Directions"
            ] })
          ] }, item.id);
        } else if (item.type === "business_hours") {
          return /* @__PURE__ */ jsxs("li", { className: "mt-2", children: [
            item.label && /* @__PURE__ */ jsx("div", { className: "opacity-80 mb-2", children: item.label }),
            item.content && /* @__PURE__ */ jsx("div", { className: "opacity-80 whitespace-pre-line leading-relaxed", children: item.content })
          ] }, item.id);
        }
        return null;
      }) })
    ] }, col.id)) }) }),
    /* @__PURE__ */ jsx("div", { className: "border-t", style: { borderColor: `${textColor}22` }, children: /* @__PURE__ */ jsxs("div", { className: "container mx-auto px-4 md:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4", children: [
      /* @__PURE__ */ jsx("p", { className: "text-sm opacity-80 text-center md:text-left", children: settings.footer_bottom_text }),
      showSocial && /* @__PURE__ */ jsxs("div", { className: "flex space-x-4 mb-4 md:mb-0", children: [
        availableSocials.map((social) => /* @__PURE__ */ jsx("a", { href: settings.general[social.key], target: "_blank", rel: "noreferrer", className: "opacity-80 hover:opacity-100 transition-opacity", children: social.icon }, social.key)),
        settings.general?.social_custom_links?.map((customLink, idx) => customLink.url && /* @__PURE__ */ jsxs("a", { href: customLink.url, target: "_blank", rel: "noreferrer", className: "opacity-80 hover:opacity-100 transition-opacity flex items-center group relative", title: customLink.label, children: [
          /* @__PURE__ */ jsx("svg", { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" }) }),
          /* @__PURE__ */ jsx("span", { className: "sr-only", children: customLink.label })
        ] }, `custom-${idx}`))
      ] })
    ] }) })
  ] });
}
const SettingsContext = React.createContext({});
function Layout({ children }) {
  const { settings } = usePage().props;
  useEffect(() => {
    useAuthStore.getState().checkAuth();
    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        useAuthStore.getState().checkAuth();
      }
    };
    document.addEventListener("visibilitychange", handleVisibilityChange);
    return () => document.removeEventListener("visibilitychange", handleVisibilityChange);
  }, []);
  const primary = settings?.primary_color || "#000000";
  const secondary = settings?.secondary_color || "#ffffff";
  const accent = settings?.accent_color || "#3b82f6";
  const headingFont = settings?.heading_font || "Inter";
  const bodyFont = settings?.body_font || "Inter";
  useEffect(() => {
    const fontFamilies = [.../* @__PURE__ */ new Set([headingFont, bodyFont])].map((f) => `family=${f.replace(/ /g, "+")}:wght@400;500;600;700;800`).join("&");
    const googleFontsUrl = `https://fonts.googleapis.com/css2?${fontFamilies}&display=swap`;
    const link = document.createElement("link");
    link.href = googleFontsUrl;
    link.rel = "stylesheet";
    document.head.appendChild(link);
    return () => {
      document.head.removeChild(link);
    };
  }, [headingFont, bodyFont]);
  return /* @__PURE__ */ jsxs(SettingsContext.Provider, { value: settings, children: [
    /* @__PURE__ */ jsx(Head, { children: /* @__PURE__ */ jsx("script", { src: "https://checkout.razorpay.com/v1/checkout.js" }) }),
    /* @__PURE__ */ jsxs(
      "div",
      {
        className: "min-h-screen antialiased flex flex-col",
        style: {
          "--primary": primary,
          "--secondary": secondary,
          "--accent": accent,
          "--font-heading": `"${headingFont}", sans-serif`,
          "--font-body": `"${bodyFont}", sans-serif`,
          background: secondary,
          color: primary
        },
        children: [
          /* @__PURE__ */ jsx(Navbar, { settings }),
          /* @__PURE__ */ jsx("main", { className: "flex-grow pb-[calc(3.5rem+env(safe-area-inset-bottom))] md:pb-0", children }),
          /* @__PURE__ */ jsx(Footer, {}),
          /* @__PURE__ */ jsx(AuthModal, {}),
          /* @__PURE__ */ jsx(QuickViewModal, {})
        ]
      }
    )
  ] });
}
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    title: (title) => {
      const appName = page.props?.settings?.store_name || "Vyora";
      return title ? title.includes(appName) ? title : `${title} - ${appName}` : appName;
    },
    resolve: (name) => {
      const pages = /* @__PURE__ */ Object.assign({ "./Pages/Account/Index.tsx": __vite_glob_0_0, "./Pages/Account/OrderDetails.tsx": __vite_glob_0_1, "./Pages/Account/Orders.tsx": __vite_glob_0_2, "./Pages/Auth/Login.tsx": __vite_glob_0_3, "./Pages/Auth/Register.tsx": __vite_glob_0_4, "./Pages/Cart.tsx": __vite_glob_0_5, "./Pages/Category/Show.tsx": __vite_glob_0_6, "./Pages/Checkout.tsx": __vite_glob_0_7, "./Pages/Checkout/ThankYou.tsx": __vite_glob_0_8, "./Pages/CmsPage.tsx": __vite_glob_0_9, "./Pages/Collection/Show.tsx": __vite_glob_0_10, "./Pages/GiftCards/Index.tsx": __vite_glob_0_11, "./Pages/GiftCards/MyCards.tsx": __vite_glob_0_12, "./Pages/GiftCards/Share.tsx": __vite_glob_0_13, "./Pages/Home.tsx": __vite_glob_0_14, "./Pages/LegalPage.tsx": __vite_glob_0_15, "./Pages/Product/Show.tsx": __vite_glob_0_16, "./Pages/Shop/Index.tsx": __vite_glob_0_17, "./Pages/Shop/Search.tsx": __vite_glob_0_18, "./Pages/Shop/SearchInput.tsx": __vite_glob_0_19, "./Pages/Wishlist.tsx": __vite_glob_0_20 });
      let pageModule = pages[`./Pages/${name}.tsx`];
      if (!pageModule) {
        const pagesJsx = /* @__PURE__ */ Object.assign({});
        pageModule = pagesJsx[`./Pages/${name}.jsx`];
      }
      pageModule.default.layout = pageModule.default.layout || ((page2) => /* @__PURE__ */ jsx(Layout, { children: page2 }));
      return pageModule;
    },
    setup: ({ App, props }) => /* @__PURE__ */ jsx(App, { ...props })
  })
);
