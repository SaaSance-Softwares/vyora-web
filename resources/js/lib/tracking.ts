// Declare global window properties for TS
declare global {
    interface Window {
        dataLayer: any[];
        gtag: (...args: any[]) => void;
        fbq: (...args: any[]) => void;
        snaptr: (...args: any[]) => void;
    }
}

// Safely call gtag if defined
export const gtag = (...args: any[]) => {
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

// Safely call fbq if defined and trigger CAPI
export const fbq = (action: string, eventName: string, eventData: any = {}) => {
    const eventId = generateEventId();

    if (typeof window !== 'undefined' && typeof window.fbq === 'function') {
        window.fbq(action, eventName, eventData, { eventID: eventId });
    }

    // Trigger Server-side CAPI for Standard Events
    if (typeof window !== 'undefined' && action === 'track') {
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
                custom_data: eventData
            })
        }).catch(err => console.error('CAPI Event Error:', err));
    }
};

export const snaptr = (action: string, eventName: string, eventData: any = {}) => {
    const eventId = generateEventId();

    if (typeof window !== 'undefined' && typeof window.snaptr === 'function') {
        window.snaptr(action, eventName, { ...eventData, client_dedup_id: eventId });
    }

    // Trigger Server-side CAPI for Standard Events
    if (typeof window !== 'undefined' && action === 'track') {
        // Find Snapchat Cookie ID (_scid) manually to pass to backend if possible
        const scid = document.cookie.split('; ').find(row => row.startsWith('_scid='))?.split('=')[1];
        
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
    const value = parseFloat(product.price);
    
    // GA4
    gtag('event', 'view_item', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: product.id,
                item_name: product.title,
                price: value,
                quantity: 1
            }
        ]
    });

    // Meta
    fbq('track', 'ViewContent', {
        content_ids: [product.id],
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'VIEW_CONTENT', {
        item_ids: [product.id],
        item_category: 'product',
        price: value,
        currency: 'INR'
    });
};

export const trackAddToCart = (product: any, quantity: number = 1) => {
    const value = parseFloat(product.price) * quantity;

    // GA4
    gtag('event', 'add_to_cart', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: product.id,
                item_name: product.title,
                price: parseFloat(product.price),
                quantity: quantity
            }
        ]
    });

    // Meta
    fbq('track', 'AddToCart', {
        content_ids: [product.id],
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'ADD_CART', {
        item_ids: [product.id],
        price: value,
        currency: 'INR'
    });
};

export const trackAddToWishlist = (product: any) => {
    const value = parseFloat(product.price);

    // GA4
    gtag('event', 'add_to_wishlist', {
        currency: 'INR',
        value: value,
        items: [
            {
                item_id: product.id,
                item_name: product.title,
                price: parseFloat(product.price),
                quantity: 1
            }
        ]
    });

    // Meta
    fbq('track', 'AddToWishlist', {
        content_ids: [product.id],
        content_type: 'product_group',
        value: value,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'SAVE', {
        item_ids: [product.id],
        price: value,
        currency: 'INR'
    });
};

export const trackInitiateCheckout = (cartTotal: number, items: any[]) => {
    // Format items for GA4
    const gaItems = items.map(item => ({
        item_id: item.product_id || item.id,
        item_name: item.title || item.name,
        price: parseFloat(item.price),
        quantity: item.quantity
    }));

    // GA4
    gtag('event', 'begin_checkout', {
        currency: 'INR',
        value: cartTotal,
        items: gaItems
    });

    // Meta
    fbq('track', 'InitiateCheckout', {
        content_ids: gaItems.map(i => i.item_id),
        content_type: 'product_group',
        value: cartTotal,
        num_items: items.length,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'START_CHECKOUT', {
        item_ids: gaItems.map(i => i.item_id),
        price: cartTotal,
        currency: 'INR',
        number_items: items.reduce((sum, item) => sum + (item.quantity || 1), 0)
    });
};

export const trackPurchase = (transactionId: string, total: number, items: any[]) => {
    const gaItems = items.map(item => ({
        item_id: item.product_id || item.id,
        item_name: item.title || item.name,
        price: parseFloat(item.price),
        quantity: item.quantity
    }));

    // GA4
    gtag('event', 'purchase', {
        transaction_id: transactionId,
        value: total,
        currency: 'INR',
        items: gaItems
    });

    // Meta
    fbq('track', 'Purchase', {
        content_ids: gaItems.map(i => i.item_id),
        content_type: 'product_group',
        value: total,
        currency: 'INR'
    });

    // Snapchat
    snaptr('track', 'PURCHASE', {
        transaction_id: transactionId,
        item_ids: gaItems.map(i => i.item_id),
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
