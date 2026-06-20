// Declare global window properties for TS
declare global {
    interface Window {
        dataLayer: any[];
        gtag: (...args: any[]) => void;
        fbq: (...args: any[]) => void;
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

export const trackPageView = (url: string) => {
    gtag('event', 'page_view', { page_path: url });
    fbq('track', 'PageView');
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
        content_type: 'product',
        value: value,
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
        content_type: 'product',
        value: value,
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
        content_type: 'product',
        value: cartTotal,
        num_items: items.length,
        currency: 'INR'
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
        content_type: 'product',
        value: total,
        currency: 'INR'
    });
};
