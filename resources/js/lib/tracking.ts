// Declare global window properties for TS
declare global {
    interface Window {
        dataLayer: any[];
        gtag: (...args: any[]) => void;
        fbq: (...args: any[]) => void;
        snaptr: (...args: any[]) => void;
    }
}

// Cookie consent checkers (DPDP compliance)
export const hasMetaConsent = (): boolean => {
    if (typeof window === 'undefined') return false;
    const consentRaw = localStorage.getItem('cookie_consent');
    if (!consentRaw || consentRaw === 'essential') return false;
    try {
        const consent = JSON.parse(consentRaw);
        return !!consent.meta_pixel;
    } catch (e) {
        return consentRaw === 'all';
    }
};

export const hasSnapchatConsent = (): boolean => {
    if (typeof window === 'undefined') return false;
    const consentRaw = localStorage.getItem('cookie_consent');
    if (!consentRaw || consentRaw === 'essential') return false;
    try {
        const consent = JSON.parse(consentRaw);
        return !!consent.snapchat_pixel;
    } catch (e) {
        return consentRaw === 'all';
    }
};

export const hasGoogleConsent = (): boolean => {
    if (typeof window === 'undefined') return false;
    const consentRaw = localStorage.getItem('cookie_consent');
    if (!consentRaw || consentRaw === 'essential') return false;
    try {
        const consent = JSON.parse(consentRaw);
        return !!consent.google_analytics;
    } catch (e) {
        return consentRaw === 'all';
    }
};

// Helper to extract first-party cookies (_fbp, _fbc, _scid)
export const getCookie = (name: string): string | null => {
    if (typeof document === 'undefined') return null;
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop()?.split(';').shift() || null;
    }
    return null;
};

// Safely call gtag if defined and consented
export const gtag = (...args: any[]) => {
    if (!hasGoogleConsent()) return;

    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
        window.gtag(...args);
    } else if (typeof window !== 'undefined' && window.dataLayer) {
        window.dataLayer.push(args);
    }
};

// Helper to generate a unique event ID for deduplication
const generateEventId = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'evt_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
};

// Safely call fbq if defined and trigger CAPI (Consent-checked)
export const fbq = (action: string, eventName: string, eventData: any = {}, userData?: any, customEventId?: string) => {
    if (!hasMetaConsent()) {
        return; // DPDP: Block both browser pixel and server CAPI if consent not granted
    }

    const eventId = customEventId || generateEventId();

    if (typeof window !== 'undefined' && typeof window.fbq === 'function') {
        window.fbq(action, eventName, eventData, { eventID: eventId });
    }

    // Trigger Server-side CAPI for Standard Events
    if (typeof window !== 'undefined' && action === 'track') {
        const fbp = getCookie('_fbp');
        const fbc = getCookie('_fbc');

        const fullUserData = {
            ...(userData || {}),
            ...(fbp ? { fbp } : {}),
            ...(fbc ? { fbc } : {}),
        };

        fetch('/api/tracking/meta-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                event_name: eventName,
                event_id: eventId,
                event_source_url: window.location.href,
                custom_data: eventData,
                user_data: fullUserData
            })
        }).catch(err => console.error('CAPI Event Error:', err));
    }
};

export const snaptr = (action: string, eventName: string, eventData: any = {}) => {
    if (!hasSnapchatConsent()) {
        return;
    }

    const eventId = generateEventId();

    if (typeof window !== 'undefined' && typeof window.snaptr === 'function') {
        window.snaptr(action, eventName, { ...eventData, client_dedup_id: eventId });
    }

    // Trigger Server-side CAPI for Standard Events
    if (typeof window !== 'undefined' && action === 'track') {
        const scid = getCookie('_scid');
        
        fetch('/api/tracking/snapchat-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                event_name: eventName,
                event_id: eventId,
                event_source_url: window.location.href,
                custom_data: eventData,
                sc_cookie1: scid
            })
        }).catch(err => console.error('Snapchat CAPI Event Error:', err));
    }
};

export const trackPageView = (url: string) => {
    gtag('event', 'page_view', { page_path: url });
    fbq('track', 'PageView');
    snaptr('track', 'PAGE_VIEW');
};

export const trackViewContent = (product: any) => {
    const value = parseFloat(product.price) || 0;
    const productId = String(product.id);
    const title = product.name || product.title || '';
    
    // GA4
    gtag('event', 'view_item', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: productId,
                item_name: title,
                price: value,
                quantity: 1
            }
        ]
    });

    // Meta (Catalog matched by product ID, content_type: product_group, matches item_group_id)
    fbq('track', 'ViewContent', {
        content_ids: [productId],
        content_name: title,
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'VIEW_CONTENT', {
        item_ids: [productId],
        item_category: 'product',
        price: value,
        currency: 'INR'
    });
};

export const trackAddToCart = (product: any, quantity: number = 1) => {
    const price = parseFloat(product.price) || 0;
    const value = price * quantity;
    const productId = String(product.id || product.productId);
    const title = product.name || product.title || '';

    // GA4
    gtag('event', 'add_to_cart', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: productId,
                item_name: title,
                price: price,
                quantity: quantity
            }
        ]
    });

    // Meta (Catalog matched by product ID, content_type: product_group, matches item_group_id)
    fbq('track', 'AddToCart', {
        content_ids: [productId],
        content_name: title,
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'ADD_CART', {
        item_ids: [productId],
        price: value,
        currency: 'INR'
    });
};

export const trackAddToWishlist = (product: any) => {
    const value = parseFloat(product.price) || 0;
    const productId = String(product.id || product.productId);
    const title = product.name || product.title || '';

    // GA4
    gtag('event', 'add_to_wishlist', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: productId,
                item_name: title,
                price: value,
                quantity: 1
            }
        ]
    });

    // Meta
    fbq('track', 'AddToWishlist', {
        content_ids: [productId],
        content_name: title,
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'SAVE', {
        item_ids: [productId],
        price: value,
        currency: 'INR'
    });
};

export const trackInitiateCheckout = (cartTotal: number, items: any[], customerData?: any) => {
    const productIds = items.map(item => String(item.productId || item.product_id || item.id));

    // Format items for GA4
    const gaItems = items.map(item => ({
        item_id: String(item.productId || item.product_id || item.id),
        item_name: item.name || item.title,
        price: parseFloat(item.price) || 0,
        quantity: item.quantity || 1
    }));

    // GA4
    gtag('event', 'begin_checkout', {
        currency: 'INR',
        value: cartTotal,
        items: gaItems
    });

    // Meta (Catalog matched by product IDs, content_type: product_group, matches item_group_id)
    fbq('track', 'InitiateCheckout', {
        content_ids: productIds,
        content_type: 'product_group',
        value: cartTotal,
        num_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0),
        currency: 'INR'
    }, customerData);

    // Snapchat
    snaptr('track', 'START_CHECKOUT', {
        item_ids: productIds,
        price: cartTotal,
        currency: 'INR',
        number_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
    });
};

export const trackPurchase = (transactionId: string, total: number, items: any[], customerData?: any) => {
    const productIds = items.map(item => String(item.productId || item.product_id || item.id));

    const gaItems = items.map(item => ({
        item_id: String(item.productId || item.product_id || item.id),
        item_name: item.name || item.title || item.product_name,
        price: parseFloat(item.price) || 0,
        quantity: item.quantity || 1
    }));

    // GA4
    gtag('event', 'purchase', {
        transaction_id: transactionId,
        value: total,
        currency: 'INR',
        items: gaItems
    });

    // Meta (Catalog matched by product IDs, content_type: product_group, matches item_group_id)
    fbq('track', 'Purchase', {
        content_ids: productIds,
        content_type: 'product_group',
        value: total,
        currency: 'INR',
        num_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
    }, customerData, `purchase_${transactionId}`);

    // Snapchat
    snaptr('track', 'PURCHASE', {
        transaction_id: transactionId,
        item_ids: productIds,
        price: total,
        currency: 'INR',
        number_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
    });
};

export const trackLogin = (method: string = 'Email') => {
    gtag('event', 'login', { method });
    
    snaptr('track', 'LOGIN', {
        sign_up_method: method
    });
};

export const trackSignUp = (method: string = 'Email') => {
    gtag('event', 'sign_up', { method });
    fbq('track', 'CompleteRegistration', { content_name: method });
    
    snaptr('track', 'SIGN_UP', {
        sign_up_method: method
    });
};

export const trackSearch = (searchQuery: string) => {
    gtag('event', 'search', { search_term: searchQuery });
    fbq('track', 'Search', { search_string: searchQuery });
    
    snaptr('track', 'SEARCH', {
        search_string: searchQuery
    });
};
