import React, { useState, useEffect, useMemo, useRef } from 'react';
import PosLayout from './Layout';
import { usePage } from '@inertiajs/react';
import { useUIStore } from '@/store/ui';

import CameraScanner from '@/Components/Pos/CameraScanner';
import CountryCodePicker, { COUNTRIES } from '@/Components/auth/CountryCodePicker';


export default function Dashboard() {
    const { appName, user } = usePage().props as any;
    const posBaseUrl = '/' + window.location.pathname.split('/')[1];
    
    // Core States
    const [cartVisible, setCartVisible] = useState(false);
    const [shiftStarted, setShiftStarted] = useState(false);
    
    // Offline Data States
    const [locations, setLocations] = useState<any[]>([]);
    const [selectedLocation, setSelectedLocation] = useState<string>('');
    const [catalog, setCatalog] = useState<any[]>([]);
    const [isSyncing, setIsSyncing] = useState(false);
    
    // Offline Queue State
    const [syncQueue, setSyncQueue] = useState<any[]>([]);

    // Cart & Scanner States
    const [cart, setCart] = useState<any[]>([]);
    const [scanInput, setScanInput] = useState('');

    // Checkout Modal States
    const [showCheckoutModal, setShowCheckoutModal] = useState(false);
    const [customerName, setCustomerName] = useState('');
    const [customerPhone, setCustomerPhone] = useState('');
    const [customerCountryCode, setCustomerCountryCode] = useState('+91');
    const [cashReceived, setCashReceived] = useState<number | ''>('');

    // Discount & Coupon States
    const [coupons, setCoupons] = useState<any[]>([]);
    const [selectedCouponCode, setSelectedCouponCode] = useState<string>('');

    // Variant Picker Modal
    const [variantProduct, setVariantProduct] = useState<any>(null);
    const [selectedColor, setSelectedColor] = useState<string>('');

    const { openAuthModal } = useUIStore();
    
    // Modal states
    const [showCustomerModal, setShowCustomerModal] = useState(false);
    const [customerNameDraft, setCustomerNameDraft] = useState('');
    const [customerPhoneDraft, setCustomerPhoneDraft] = useState('');
    const [customerCountryCodeDraft, setCustomerCountryCodeDraft] = useState('+91');

    // Camera states
    const [showCamera, setShowCamera] = useState(false);
    const scannerRef = useRef<Html5Qrcode | null>(null);
    // Print Iframe Ref
    const printIframeRef = useRef<HTMLIFrameElement>(null);

    // Boot: Check if a shift is already active in local memory
    useEffect(() => {
        const savedCatalog = localStorage.getItem('pos_catalog');
        const savedLocation = localStorage.getItem('pos_location_id') || localStorage.getItem('pos_location');
        const savedQueue = localStorage.getItem('pos_sync_queue');
        
        if (savedQueue) {
            setSyncQueue(JSON.parse(savedQueue));
        }

        // Always fetch live locations and coupons from server
        fetch(`${posBaseUrl}/api/locations`)
            .then(res => res.json())
            .then(data => setLocations(data))
            .catch(err => console.error("Offline or Error fetching locations:", err));
            
        fetch(`${posBaseUrl}/api/coupons`)
            .then(res => res.json())
            .then(data => {
                setCoupons(data);
                localStorage.setItem('pos_coupons', JSON.stringify(data));
            })
            .catch(err => {
                console.error("Offline or Error fetching coupons:", err);
                const savedCoupons = localStorage.getItem('pos_coupons');
                if (savedCoupons) setCoupons(JSON.parse(savedCoupons));
            });

        if (savedCatalog && savedLocation) {
            setCatalog(JSON.parse(savedCatalog));
            setSelectedLocation(savedLocation);
            setShiftStarted(true);
            
            if (savedQueue && JSON.parse(savedQueue).length > 0) {
                syncOrders(JSON.parse(savedQueue));
            }
        }
    }, []);

    // -------------------------------------------------------------
    // Shift Actions
    // -------------------------------------------------------------
    const handleStartShift = async () => {
        if (!selectedLocation) return alert("Please select a location.");
        setIsSyncing(true);
        try {
            const response = await fetch(`${posBaseUrl}/api/catalog?location_id=${selectedLocation}`);
            const data = await response.json();
            
            if (data.catalog && data.catalog.length > 0) {
                setCatalog(data.catalog);
                localStorage.setItem('pos_catalog', JSON.stringify(data.catalog));
                localStorage.setItem('pos_location_id', selectedLocation);
                localStorage.setItem('pos_location', selectedLocation);
                setShiftStarted(true);
            } else {
                alert("No products assigned to this location.");
            }
        } catch (error) {
            console.error(error);
            alert("Could not reach server to download catalog. Try again when online.");
        } finally {
            setIsSyncing(false);
        }
    };

    // -------------------------------------------------------------
    // Cart & Scanner Logic
    // -------------------------------------------------------------
    const addToCart = (product: any, sku: any) => {
        setCart(prev => {
            const existing = prev.find(item => item.sku_id === sku.id);
            if (existing) {
                return prev.map(item => item.sku_id === sku.id ? { ...item, qty: item.qty + 1 } : item);
            }
            return [...prev, {
                sku_id: sku.id,
                product_id: product.id,
                name: product.name,
                barcode: sku.barcode,
                price: sku.selling_price,
                qty: 1,
                image: sku.image,
                color_name: sku.color_name,
                size_name: sku.size_name
            }];
        });
    };

    const updateQty = (sku_id: number, newQty: number) => {
        if (newQty <= 0) {
            setCart(prev => prev.filter(i => i.sku_id !== sku_id));
        } else {
            setCart(prev => prev.map(i => i.sku_id === sku_id ? { ...i, qty: newQty } : i));
        }
    };

    const handleScan = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' && scanInput.trim() !== '') {
            let found = false;
            for (const product of catalog) {
                const sku = product.skus?.find((s: any) => s.barcode === scanInput.trim() || s.short_code === scanInput.trim());
                if (sku) {
                    addToCart(product, sku);
                    found = true;
                    break;
                }
            }
            if (!found) alert('Barcode not found in this location\'s catalog!');
            setScanInput('');
        }
    };

    const subtotal = useMemo(() => cart.reduce((sum, item) => sum + (item.price * item.qty), 0), [cart]);

    const processedCart = useMemo(() => {
        let flatDiscountToDistribute = 0;
        
        const activeCoupon = selectedCouponCode ? coupons.find(c => c.code === selectedCouponCode) : null;
        
        if (activeCoupon) {
            // Check minimum cart value
            if (!activeCoupon.min_cart_value || subtotal >= activeCoupon.min_cart_value) {
                if (activeCoupon.type === 'percentage') {
                    flatDiscountToDistribute = subtotal * (activeCoupon.discount_amount / 100);
                    // Enforce max discount cap
                    if (activeCoupon.max_discount_amount && flatDiscountToDistribute > activeCoupon.max_discount_amount) {
                        flatDiscountToDistribute = activeCoupon.max_discount_amount;
                    }
                } else if (activeCoupon.type === 'fixed') {
                    flatDiscountToDistribute = activeCoupon.discount_amount;
                }
            }
        }

        if (flatDiscountToDistribute > subtotal) {
            flatDiscountToDistribute = subtotal;
        }

        return cart.map(item => {
            const itemTotal = item.price * item.qty;
            const itemWeight = subtotal > 0 ? (itemTotal / subtotal) : 0;
            const itemDiscount = flatDiscountToDistribute * itemWeight;
            
            // To ensure exactness and avoid refund issues, calculate unit price reduction
            let discountedPricePerUnit = item.price - (itemDiscount / item.qty);
            if (discountedPricePerUnit < 0) discountedPricePerUnit = 0;

            discountedPricePerUnit = Math.max(0, discountedPricePerUnit);

            return {
                ...item,
                original_price: item.price,
                price: discountedPricePerUnit
            };
        });
    }, [cart, selectedCouponCode, coupons, subtotal]);

    const finalTotal = useMemo(() => processedCart.reduce((sum, item) => sum + (item.price * item.qty), 0), [processedCart]);
    const total = Math.round(finalTotal);
    const globalDiscountAmount = subtotal - finalTotal;
    
    const changeDue = (typeof cashReceived === 'number' && cashReceived >= total) ? (cashReceived - total) : 0;

    // -------------------------------------------------------------
    // Offline Order Sync Engine (Phase 3 & 4)
    // -------------------------------------------------------------
    const initiateCheckout = () => {
        if (cart.length === 0) return;
        setCustomerName('');
        setCustomerPhone('');
        setCashReceived('');
        setShowCheckoutModal(true);
    };

    const processSale = (shouldPrint: boolean = true) => {
        // 1. Create a unique order payload
        const uuid = crypto.randomUUID ? crypto.randomUUID() : Math.random().toString();
        const orderNumber = 'POS-' + uuid.substring(0, 6).toUpperCase();
        
        const newOrder = {
            uuid: uuid,
            order_number: orderNumber,
            location_id: selectedLocation,
            total_amount: total,
            discount_amount: globalDiscountAmount,
            coupon_code: selectedCouponCode,
            customer_name: customerName,
            customer_phone: customerPhone ? `${customerCountryCode}${customerPhone}` : '',
            items: processedCart,
            created_at: new Date().toISOString(),
            send_digital_receipt: !shouldPrint // if shouldPrint is false, we definitely want digital receipt. If true, we still send it as per our earlier logic (default true in backend). Let's just pass true or rely on backend.
        };

        // 2. Add to local sync queue
        const updatedQueue = [...syncQueue, newOrder];
        setSyncQueue(updatedQueue);
        localStorage.setItem('pos_sync_queue', JSON.stringify(updatedQueue));

        // 3. Trigger Print Receipt if requested
        if (shouldPrint) {
            printReceipt(newOrder);
        }

        // 4. Reset UI
        setCart([]);
        setSelectedCouponCode('');
        setShowCheckoutModal(false);

        // 5. Try to sync in the background automatically
        syncOrders(updatedQueue);
    };

    const syncOrders = async (queueToSync = syncQueue) => {
        if (queueToSync.length === 0) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const response = await fetch(`${posBaseUrl}/api/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ orders: queueToSync })
            });

            if (response.ok) {
                // Successfully synced to the master database!
                setSyncQueue([]);
                localStorage.setItem('pos_sync_queue', JSON.stringify([]));
            } else {
                try {
                    const errorData = await response.json();
                    alert("Sync Error: " + (errorData.message || errorData.error || "Unknown server error"));
                } catch(e) {
                    alert("Sync Error: Server returned " + response.status);
                }
            }
        } catch (error) {
            console.log("Offline or Server Unreachable. Order remains in queue.", error);
        }
    };

    // -------------------------------------------------------------
    // Receipt Auto-Printing (Phase 4)
    // -------------------------------------------------------------
    const printReceipt = (order: any) => {
        if (!printIframeRef.current) return;
        const iframe = printIframeRef.current;
        const doc = iframe.contentDocument || iframe.contentWindow?.document;
        if (!doc) return;

        // Construct HTML for the 80mm thermal receipt
        const html = `
            <html>
            <head>
                <style>
                    body { font-family: monospace; width: 300px; margin: 0 auto; color: #000; font-size: 12px; }
                    .center { text-align: center; }
                    .bold { font-weight: bold; }
                    .border-b { border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 10px; }
                    .border-t { border-top: 1px dashed #000; margin-top: 10px; padding-top: 10px; }
                    table { width: 100%; border-collapse: collapse; }
                    th { text-align: left; font-weight: bold; padding-bottom: 5px; }
                    td { padding: 2px 0; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                </style>
            </head>
            <body>
                <div class="center border-b">
                    <h2 style="margin:0; font-size:18px;">VYORA POS</h2>
                    <p style="margin:2px 0;">Receipt #: ${order.order_number}</p>
                    <p style="margin:2px 0;">Date: ${new Date(order.created_at).toLocaleString()}</p>
                </div>
                
                ${order.customer_name || order.customer_phone ? `
                <div class="border-b">
                    ${order.customer_name ? `<p style="margin:2px 0;">Customer: ${order.customer_name}</p>` : ''}
                    ${order.customer_phone ? `<p style="margin:2px 0;">Phone: ${order.customer_phone}</p>` : ''}
                </div>
                ` : ''}

                <table>
                    <thead>
                        <tr style="border-bottom: 1px dashed #000;">
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map((item: any) => `
                        <tr>
                            <td>
                                ${item.name}
                                ${item.price < item.original_price ? `<br><small style="color: #666;">(₹${item.price.toFixed(2)} ea)</small>` : ''}
                            </td>
                            <td class="text-center">${item.qty}</td>
                            <td class="text-right">${(item.price * item.qty).toFixed(2)}</td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>

                <div class="border-t border-b">
                    ${order.discount_amount && order.discount_amount > 0 ? `
                    <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                        <span>Subtotal</span>
                        <span>${(order.total_amount + order.discount_amount).toFixed(2)}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                        <span>Discount Applied</span>
                        <span>-${order.discount_amount.toFixed(2)}</span>
                    </div>
                    ` : ''}
                    <div style="display:flex; justify-content:space-between; font-size:14px;" class="bold">
                        <span>TOTAL</span>
                        <span>${order.total_amount.toFixed(2)}</span>
                    </div>
                </div>

                <div class="center" style="margin-top:20px;">
                    <p>Thank you for shopping!</p>
                    <!-- Simulated 1D Barcode using CSS for Returns -->
                    <div style="margin-top:10px; height: 40px; background: repeating-linear-gradient(90deg, #000, #000 2px, transparent 2px, transparent 4px, #000 4px, #000 5px, transparent 5px, transparent 8px);"></div>
                    <p style="margin-top:2px;">${order.order_number}</p>
                </div>
            </body>
            </html>
        `;

        doc.open();
        doc.write(html);
        doc.close();

        // Print after it renders
        setTimeout(() => {
            iframe.contentWindow?.focus();
            iframe.contentWindow?.print();
        }, 200);
    };

    return (
        <PosLayout 
            title="POS Terminal" 
            syncCount={syncQueue.length} 
            onSync={() => syncOrders(syncQueue)}
        >
            {/* Camera Barcode Scanner */}
            {showCamera && (
                <CameraScanner
                    onClose={() => setShowCamera(false)}
                    onScan={(code) => {
                        setShowCamera(false);
                        // Find the matching SKU across the catalog
                        let found = false;
                        for (const product of catalog) {
                            const sku = product.skus?.find(
                                (s: any) => s.barcode === code || s.short_code === code
                            );
                            if (sku) {
                                addToCart(product, sku);
                                found = true;
                                break;
                            }
                        }
                        if (!found) {
                            alert(`No product found for barcode: ${code}`);
                        }
                    }}
                />
            )}

            {/* Hidden Iframe for Thermal Printing */}
            <iframe ref={printIframeRef} style={{ display: 'none' }} title="Receipt Printer" />

            {/* CHECKOUT MODAL */}
            {showCheckoutModal && (
                <div className="absolute inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white p-8 rounded-2xl shadow-2xl max-w-lg w-full border border-gray-200">
                        <div className="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h2 className="text-2xl font-bold text-black uppercase tracking-wider">Complete Sale</h2>
                            <button onClick={() => setShowCheckoutModal(false)} className="text-gray-400 hover:text-black">✕</button>
                        </div>
                        
                        <div className="space-y-5">
                            <div className="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center mb-6">
                                <p className="text-sm text-gray-500 uppercase tracking-widest font-bold mb-1">Total Amount Due</p>
                                <p className="text-4xl font-bold text-black">₹{total}</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-900 mb-1 uppercase tracking-wide">Customer Phone (Optional)</label>
                                    <input 
                                        type="tel" 
                                        placeholder="Send WhatsApp Receipt"
                                        value={customerPhone}
                                        onChange={(e) => setCustomerPhone(e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-black bg-white"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-900 mb-1 uppercase tracking-wide">Customer Name (Optional)</label>
                                    <input 
                                        type="text" 
                                        placeholder="For receipt"
                                        value={customerName}
                                        onChange={(e) => setCustomerName(e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-black bg-white"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-900 mb-1 uppercase tracking-wide">Cash Received</label>
                                <div className="flex flex-col space-y-3">
                                    <input 
                                        type="number" 
                                        placeholder="Amount given by customer"
                                        value={cashReceived}
                                        onChange={(e) => setCashReceived(e.target.value ? Number(e.target.value) : '')}
                                        className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-black bg-white text-lg font-bold"
                                    />
                                    {typeof cashReceived === 'number' && cashReceived >= total && (
                                        <div className="bg-green-100 text-green-800 px-4 py-3 rounded-lg font-bold text-center">
                                            Change to give: ₹{changeDue}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="flex gap-3 mt-4">
                                <button 
                                    onClick={() => processSale(false)}
                                    className="flex-1 bg-white text-black border-2 border-black p-4 rounded-xl font-bold text-[13px] hover:bg-gray-50 transition uppercase tracking-wide"
                                >
                                    Proceed & Digital Receipt
                                </button>
                                <button 
                                    onClick={() => processSale(true)}
                                    className="flex-1 bg-black text-white p-4 rounded-xl font-bold text-[13px] hover:bg-gray-900 transition shadow-xl shadow-gray-300 uppercase tracking-wide"
                                >
                                    Process & Print Bill
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* START SHIFT OVERLAY */}
            {!shiftStarted && (
                <div className="absolute inset-0 z-50 bg-gray-100/90 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200">
                        <div className="text-center mb-8">
                            <h2 className="text-3xl font-bold text-black uppercase tracking-wider mb-2">Vyora POS</h2>
                            <p className="text-gray-500 font-medium">Select your location to begin.</p>
                        </div>
                        
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Location / Market</label>
                                <select 
                                    className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-black bg-gray-50 font-medium"
                                    value={selectedLocation}
                                    onChange={(e) => setSelectedLocation(e.target.value)}
                                >
                                    <option value="">-- Select a Location --</option>
                                    {locations.map(loc => (
                                        <option key={loc.id} value={loc.id}>{loc.name}</option>
                                    ))}
                                </select>
                                {locations.length === 0 && !isSyncing && (
                                    <div className="mt-2 flex items-center justify-between text-xs text-gray-500 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                                        <span>⚠️ No stores loaded.</span>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                fetch(`${posBaseUrl}/api/locations`)
                                                    .then(res => res.json())
                                                    .then(data => setLocations(data))
                                                    .catch(() => alert('Could not reach server.'));
                                            }}
                                            className="font-bold text-black underline ml-2"
                                        >
                                            Retry
                                        </button>
                                    </div>
                                )}
                            </div>

                            <button 
                                onClick={handleStartShift}
                                disabled={isSyncing || !selectedLocation}
                                className="w-full bg-black text-white p-4 rounded-xl font-bold text-lg hover:bg-gray-900 transition shadow-lg shadow-gray-300 uppercase tracking-wide disabled:opacity-50"
                            >
                                {isSyncing ? "Downloading Catalog..." : "Start Shift"}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* MAIN DASHBOARD */}
            <div className={`flex w-full h-full relative ${!shiftStarted ? 'blur-md pointer-events-none' : ''}`}>
                
                {/* Left Side: Product Grid */}
                <div className={`w-full lg:w-3/5 bg-white flex flex-col h-full transition-all ${cartVisible ? 'hidden lg:flex' : 'flex'}`}>
                    
                    <div className="p-4 border-b border-gray-200 sticky top-0 bg-white z-10 flex space-x-3">
                        <div className="relative flex-1">
                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                🔍
                            </div>
                            <input
                                type="text"
                                placeholder="Scan Barcode or Search Products..."
                                value={scanInput}
                                onChange={(e) => setScanInput(e.target.value)}
                                onKeyDown={handleScan}
                                className="w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black focus:border-black outline-none text-lg transition-shadow bg-gray-50 focus:bg-white"
                                autoFocus
                            />
                        </div>
                        {/* Camera Scan Button — for phones/tablets */}
                        <button
                            type="button"
                            onClick={() => setShowCamera(true)}
                            className="px-4 py-3 bg-black hover:bg-gray-800 text-white font-bold rounded-lg transition border border-gray-800 whitespace-nowrap text-sm shadow-sm flex items-center gap-2"
                            title="Scan with camera"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span className="hidden sm:inline">Scan</span>
                        </button>
                    </div>
                    
                    <div className="flex-1 p-4 overflow-y-auto bg-gray-50/50">
                        {catalog.length === 0 ? (
                            <div className="h-full flex flex-col items-center justify-center text-gray-400 space-y-4">
                                <span className="text-4xl">📦</span>
                                <p className="text-lg font-bold uppercase tracking-widest text-gray-400">Catalog is Empty</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 pb-28 lg:pb-4">
                                {catalog.map((product) => {
                                    const primarySku = product.skus && product.skus.length > 0 ? product.skus[0] : null;
                                    
                                    return (
                                        <div 
                                            key={product.id} 
                                            onClick={() => setVariantProduct(product)}
                                            className="bg-white border border-gray-200 rounded-xl p-3 hover:shadow-lg hover:border-black cursor-pointer transition-all active:scale-95 group"
                                        >
                                            <div className="aspect-square bg-gray-50 rounded-lg mb-3 flex items-center justify-center text-gray-400 group-hover:bg-gray-100 transition-colors overflow-hidden border border-gray-100 relative">
                                                {product.image ? (
                                                    <img src={product.image} alt={product.name} className="object-cover w-full h-full mix-blend-multiply" />
                                                ) : (
                                                    <span>📷</span>
                                                )}
                                                {product.skus && product.skus.length > 1 && (
                                                    <span className="absolute bottom-1 right-1 bg-black text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                                        {product.skus.length} variants
                                                    </span>
                                                )}
                                            </div>
                                            <h3 className="text-sm font-medium text-gray-800 line-clamp-2 leading-tight">{product.name}</h3>
                                            <p className="text-black font-bold mt-2 text-lg">
                                                ₹{primarySku ? primarySku.selling_price : '0'}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </div>

                {/* Right Side: Cart */}
                <div className={`w-full lg:w-2/5 bg-white flex-col h-full border-l border-gray-200 shadow-2xl lg:shadow-none absolute lg:relative z-40 lg:z-auto ${cartVisible ? 'flex' : 'hidden lg:flex'}`}>
                    
                    <div className="lg:hidden p-4 bg-black text-white flex justify-between items-center">
                        <h2 className="text-xl font-bold tracking-wide uppercase">Current Cart</h2>
                        <button onClick={() => setCartVisible(false)} className="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                            ✕
                        </button>
                    </div>

                    <div className="p-5 bg-white border-b border-gray-200 flex justify-between items-center z-10">
                        <div>
                            <h2 className="text-xl font-bold text-gray-900 hidden lg:block uppercase tracking-wide">Current Cart</h2>
                            {customerName && (
                                <p className="text-sm text-gray-500 mt-0.5">👤 {customerName} {customerPhone && `· ${customerCountryCode} ${customerPhone}`}</p>
                            )}
                        </div>
                    </div>

                    <div className="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50/50">
                        {cart.length === 0 ? (
                            <div className="h-full flex items-center justify-center text-gray-400 font-medium text-lg">
                                Scan items to add to cart
                            </div>
                        ) : (
                            processedCart.map(item => (
                                <div key={item.sku_id} className="flex flex-col bg-white p-3 sm:p-4 rounded-xl shadow-sm border border-gray-200 hover:border-black transition-colors gap-3">
                                    <div className="flex gap-4">
                                        <div className="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                            <img 
                                                src={item.image || `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=374151`} 
                                                alt={item.name} 
                                                className="w-full h-full object-cover"
                                                onError={(e) => { (e.target as HTMLImageElement).src = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=374151`; }}
                                            />
                                        </div>
                                        
                                        <div className="flex-1 min-w-0">
                                            <div className="flex justify-between items-start mb-1">
                                                <h3 className="font-bold text-gray-900 truncate pr-2" title={item.name}>{item.name}</h3>
                                                <button onClick={() => updateQty(item.sku_id, 0)} className="text-gray-400 hover:text-red-500 transition-colors p-1 -mr-1 rounded hover:bg-red-50">
                                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                            
                                            <div className="flex flex-wrap gap-2 mt-1.5">
                                                {item.color_name && item.color_name !== 'Default' && (
                                                    <span className="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-medium border border-gray-200 shadow-sm">
                                                        {item.color_name}
                                                    </span>
                                                )}
                                                {item.size_name && item.size_name !== 'Default' && (
                                                    <span className="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-medium border border-gray-200 shadow-sm">
                                                        {item.size_name}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center justify-between pt-2 border-t border-gray-50">
                                        <div className="flex items-baseline gap-2">
                                            <span className="font-bold text-lg text-gray-900">
                                                ₹{item.price.toFixed(2)}
                                            </span>
                                            {item.price < item.original_price && (
                                                <span className="text-xs text-gray-400 line-through">
                                                    ₹{item.original_price}
                                                </span>
                                            )}
                                        </div>
                                        
                                        <div className="flex items-center space-x-3 bg-gray-100 rounded-lg p-1">
                                            <button onClick={() => updateQty(item.sku_id, item.qty - 1)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 hover:text-black hover:bg-gray-50 active:scale-95 transition">-</button>
                                            <span className="font-bold text-gray-900 w-5 text-center text-sm">{item.qty}</span>
                                            <button onClick={() => updateQty(item.sku_id, item.qty + 1)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 hover:text-black hover:bg-gray-50 active:scale-95 transition">+</button>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    <div className="p-5 bg-white border-t border-gray-200 space-y-3 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                        {cart.length > 0 && coupons.length > 0 && (
                            <div className="flex flex-col gap-1 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <label className="text-xs font-bold text-gray-500 uppercase">Apply Offline Offer</label>
                                <select 
                                    value={selectedCouponCode} 
                                    onChange={(e) => setSelectedCouponCode(e.target.value)}
                                    className="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-black focus:border-black block w-full p-2"
                                >
                                    <option value="">No offer applied</option>
                                    {coupons.map(c => {
                                        const disabled = c.min_cart_value && subtotal < c.min_cart_value;
                                        let label = c.code;
                                        if (c.type === 'percentage') label += ` (${c.discount_amount}%)`;
                                        if (c.type === 'fixed') label += ` (₹${c.discount_amount})`;
                                        if (c.min_cart_value) label += ` - Min ₹${c.min_cart_value}`;
                                        
                                        return (
                                            <option key={c.code} value={c.code} disabled={disabled}>
                                                {label} {disabled ? '(Min value not met)' : ''}
                                            </option>
                                        );
                                    })}
                                </select>
                            </div>
                        )}

                        <div className="flex justify-between text-gray-500 font-medium text-sm px-1">
                            <span>Subtotal</span>
                            <span className={globalDiscountAmount > 0 ? "line-through text-gray-400" : ""}>₹{subtotal.toFixed(2)}</span>
                        </div>
                        {globalDiscountAmount > 0 && (
                            <div className="flex justify-between text-green-600 font-bold text-sm px-1">
                                <span>Discount Applied</span>
                                <span>-₹{globalDiscountAmount.toFixed(2)}</span>
                            </div>
                        )}
                        <div className="flex justify-between font-bold text-2xl pt-3 border-t border-gray-200 text-black px-1 mt-2">
                            <span>Total</span>
                            <span>₹{total.toFixed(2)}</span>
                        </div>
                        <button 
                            disabled={cart.length === 0}
                            onClick={initiateCheckout}
                            className="w-full bg-black text-white p-4 rounded-xl font-bold text-lg hover:bg-gray-900 transition active:scale-95 shadow-xl shadow-gray-300 mt-4 uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Checkout (₹{total})
                        </button>
                    </div>
                </div>

                {/* Mobile Cart Toggle Button */}
                <div className={`lg:hidden fixed bottom-6 right-6 z-30 transition-transform ${cartVisible ? 'translate-y-24' : 'translate-y-0'}`}>
                    <button 
                        onClick={() => setCartVisible(true)}
                        className="bg-black text-white rounded-full px-6 py-4 shadow-2xl flex items-center justify-center font-bold text-lg hover:bg-gray-900 active:scale-95 transition uppercase tracking-wide border border-gray-800"
                    >
                        🛒 View Cart (₹{total})
                    </button>
                </div>
                
            </div>

            {/* ── VARIANT PICKER MODAL ── */}
            {variantProduct && (() => {
                // Compute unique colors and sizes from the product's SKUs
                const uniqueColors = Array.from(
                    new Map(variantProduct.skus.map((s: any) => [s.color_name, s])).values()
                ) as any[];
                const activeColor = selectedColor || uniqueColors[0]?.color_name;
                const sizesForColor = variantProduct.skus.filter((s: any) => s.color_name === activeColor);
                const selectedSku = sizesForColor.length === 1 ? sizesForColor[0] : null;

                return (
                    <div
                        className="fixed inset-0 z-[200] bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
                        onClick={() => { setVariantProduct(null); setSelectedColor(''); }}
                    >
                        <div
                            className="bg-white w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl overflow-hidden"
                            onClick={e => e.stopPropagation()}
                        >
                            {/* Header */}
                            <div className="flex items-center justify-between px-5 pt-5 pb-4">
                                <div className="flex items-center gap-3">
                                    {variantProduct.image && (
                                        <img src={variantProduct.image} className="w-12 h-12 object-cover rounded-xl border border-gray-100" />
                                    )}
                                    <div>
                                        <h3 className="font-bold text-gray-900 text-base leading-tight">{variantProduct.name}</h3>
                                        <p className="text-xs text-gray-400 mt-0.5">Select a variant to add</p>
                                    </div>
                                </div>
                                <button
                                    onClick={() => { setVariantProduct(null); setSelectedColor(''); }}
                                    className="text-gray-400 hover:text-black w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-lg transition"
                                >✕</button>
                            </div>

                            <div className="px-5 pb-6 space-y-6">
                                {/* Color Swatches */}
                                {uniqueColors.length > 0 && (
                                    <div>
                                        <p className="text-sm font-bold text-gray-700 mb-3">Color Variant:</p>
                                        <div className="flex gap-3 flex-wrap">
                                            {uniqueColors.map((sku: any) => (
                                                <button
                                                    key={sku.color_name}
                                                    onClick={() => setSelectedColor(sku.color_name)}
                                                    className={`flex flex-col items-center gap-1.5 group`}
                                                >
                                                    <div className={`w-16 h-16 rounded-xl overflow-hidden border-2 transition-all ${activeColor === sku.color_name ? 'border-black scale-105 shadow-md' : 'border-transparent bg-gray-100'}`}>
                                                        {sku.image ? (
                                                            <img src={sku.image} className="w-full h-full object-cover" />
                                                        ) : (
                                                            <div className="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400 text-xl">👕</div>
                                                        )}
                                                    </div>
                                                    <span className={`text-[11px] font-semibold transition-colors ${activeColor === sku.color_name ? 'text-black' : 'text-gray-400'}`}>
                                                        {sku.color_name}
                                                    </span>
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Size Buttons */}
                                <div>
                                    <p className="text-sm font-bold text-gray-700 mb-3">Select Size</p>
                                    <div className="flex gap-2 flex-wrap">
                                        {sizesForColor.map((sku: any) => (
                                            <button
                                                key={sku.id}
                                                onClick={() => {
                                                    addToCart(variantProduct, sku);
                                                    setVariantProduct(null);
                                                    setSelectedColor('');
                                                }}
                                                disabled={sku.stock === 0}
                                                className={`relative px-5 py-3 rounded-xl border-2 font-bold text-sm transition-all
                                                    ${sku.stock === 0
                                                        ? 'border-gray-100 text-gray-300 cursor-not-allowed bg-gray-50'
                                                        : 'border-gray-200 text-gray-900 hover:border-black hover:bg-black hover:text-white active:scale-95 bg-white shadow-sm'
                                                    }`}
                                            >
                                                {sku.size_name}
                                                {sku.stock === 0 && (
                                                    <span className="absolute inset-0 flex items-center justify-center">
                                                        <span className="w-full border-t border-gray-300 absolute rotate-[-10deg]"></span>
                                                    </span>
                                                )}
                                            </button>
                                        ))}
                                    </div>
                                    {/* Price line */}
                                    <div className="mt-4 flex items-center justify-between text-sm text-gray-500 border-t border-gray-100 pt-3">
                                        <span>Price</span>
                                        <span className="font-bold text-black text-lg">
                                            ₹{sizesForColor[0]?.selling_price ?? '—'}
                                            {sizesForColor.some((s: any) => s.selling_price !== sizesForColor[0]?.selling_price) ? '+' : ''}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            })()}

            {/* ── CUSTOMER MODAL ── */}
            {showCustomerModal && (
                <div className="fixed inset-0 z-[200] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
                        <div className="flex items-center justify-between p-5 border-b border-gray-100">
                            <h3 className="font-bold text-gray-900 text-lg">Customer Details</h3>
                            <button onClick={() => setShowCustomerModal(false)} className="text-gray-400 hover:text-black p-1 rounded-full hover:bg-gray-100">✕</button>
                        </div>
                        <div className="p-5 space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Name</label>
                                <input
                                    type="text"
                                    placeholder="Customer name"
                                    value={customerNameDraft}
                                    onChange={e => setCustomerNameDraft(e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none"
                                    autoFocus
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Phone (for WhatsApp receipt)</label>
                                <div className="flex gap-2">
                                    <div className="w-[110px] shrink-0">
                                        <CountryCodePicker
                                            value={customerCountryCodeDraft}
                                            onChange={setCustomerCountryCodeDraft}
                                        />
                                    </div>
                                    <input
                                        type="tel"
                                        placeholder="Mobile number"
                                        value={customerPhoneDraft}
                                        onChange={e => setCustomerPhoneDraft(e.target.value.replace(/[^0-9]/g, ''))}
                                        className="flex-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="p-5 pt-0 flex gap-3">
                            {customerName && (
                                <button
                                    onClick={() => { setCustomerName(''); setCustomerPhone(''); setCustomerCountryCode('+91'); setShowCustomerModal(false); }}
                                    className="flex-1 border border-red-200 text-red-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-red-50 transition"
                                >
                                    Remove
                                </button>
                            )}
                            <button
                                onClick={() => {
                                    setCustomerName(customerNameDraft);
                                    setCustomerPhone(customerPhoneDraft);
                                    setCustomerCountryCode(customerCountryCodeDraft);
                                    setShowCustomerModal(false);
                                }}
                                className="flex-1 bg-black text-white py-2.5 rounded-xl font-bold text-sm hover:bg-gray-900 transition"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            )}

        </PosLayout>
    );
}

Dashboard.layout = (page: React.ReactNode) => page;
