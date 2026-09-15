import React, { useState, useEffect, useMemo } from 'react';
import PosLayout from './Layout';
import CameraScanner from '@/Components/Pos/CameraScanner';

export default function Returns() {
    const posBaseUrl = '/' + window.location.pathname.split('/')[1];
    
    // Core States
    const [searchQuery, setSearchQuery] = useState('');
    const [orders, setOrders] = useState<any[]>([]);
    const [selectedOrder, setSelectedOrder] = useState<any | null>(null);
    const [returnQtys, setReturnQtys] = useState<Record<number, number>>({});
    
    // UI States
    const [isSearching, setIsSearching] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);
    const [showCamera, setShowCamera] = useState(false);
    const [imageModal, setImageModal] = useState<{isOpen: boolean, title: string, images: string[]}>({ isOpen: false, title: '', images: [] });
    const [showMobileDetails, setShowMobileDetails] = useState(false);
    
    // Exchange Mode States
    const [exchangeMode, setExchangeMode] = useState(true);
    const [activeMobileTab, setActiveMobileTab] = useState<'returns' | 'new'>('returns');
    const [catalog, setCatalog] = useState<any[]>([]);
    const [exchangeCart, setExchangeCart] = useState<any[]>([]);
    const [scanInput, setScanInput] = useState('');
    const [locationId, setLocationId] = useState<string>('');
    const [locations, setLocations] = useState<any[]>([]);
    const [shiftStarted, setShiftStarted] = useState(false);

    // Load Local Data for Exchanges
    useEffect(() => {
        const savedCatalog = localStorage.getItem('pos_catalog');
        const savedLocation = localStorage.getItem('pos_location_id') || localStorage.getItem('pos_location');
        if (savedCatalog) setCatalog(JSON.parse(savedCatalog));
        fetch(`${posBaseUrl}/api/locations`).then(res=>res.json()).then(data=>setLocations(data)).catch(console.error);
        if (savedLocation) {
            setLocationId(savedLocation);
            setShiftStarted(true);
        }
    }, []);

    // -------------------------------------------------------------
    // Order Search & Selection
    // -------------------------------------------------------------
    const handleSearch = async (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        if (!searchQuery.trim()) return;

        setIsSearching(true);
        try {
            const res = await fetch(`${posBaseUrl}/api/orders/search?query=${encodeURIComponent(searchQuery.trim())}`);
            const data = await res.json();
            setOrders(data);
            setSelectedOrder(null);
            setShowMobileDetails(false);
            setReturnQtys({});
            setExchangeMode(true);
            setExchangeCart([]);
        } catch (error) {
            console.error(error);
            alert("Error searching for orders.");
        } finally {
            setIsSearching(false);
        }
    };

    const selectOrder = (order: any) => {
        setSelectedOrder(order);
        setShowMobileDetails(true);
        setExchangeMode(true);
        setActiveMobileTab('returns');
        setExchangeCart([]);
        const initialQtys: Record<number, number> = {};
        order.items.forEach((item: any) => {
            initialQtys[item.id] = 0;
        });
        setReturnQtys(initialQtys);
    };

    const updateReturnQty = (itemId: number, delta: number, maxReturnable: number) => {
        setReturnQtys(prev => {
            const current = prev[itemId] || 0;
            const next = current + delta;
            if (next < 0 || next > maxReturnable) return prev;
            return { ...prev, [itemId]: next };
        });
    };

    // -------------------------------------------------------------
    // Exchange Cart Logic
    // -------------------------------------------------------------
    const processScanCode = (code: string) => {
        let found = false;
        for (const product of catalog) {
            const sku = product.skus?.find((s: any) => s.barcode === code.trim() || s.short_code === code.trim());
            if (sku) {
                addToExchangeCart(product, sku);
                found = true;
                break;
            }
        }
        if (!found) alert('Barcode not found in this location\'s catalog!');
    };

    const handleScan = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' && scanInput.trim() !== '') {
            processScanCode(scanInput);
            setScanInput('');
        }
    };

    const addToExchangeCart = (product: any, sku: any) => {
        setExchangeCart(prev => {
            const existing = prev.find(item => item.sku_id === sku.id);
            if (existing) {
                return prev.map(item => item.sku_id === sku.id ? { ...item, qty: item.qty + 1 } : item);
            }
            return [...prev, {
                sku_id: sku.id,
                product_id: product.id,
                name: product.name,
                price: sku.selling_price,
                qty: 1,
                image: sku.image,
                color_name: sku.color_name,
                size_name: sku.size_name,
                gallery: sku.gallery || (sku.image ? [sku.image] : [])
            }];
        });
    };

    const updateExchangeQty = (sku_id: number, newQty: number) => {
        if (newQty <= 0) {
            setExchangeCart(prev => prev.filter(i => i.sku_id !== sku_id));
        } else {
            setExchangeCart(prev => prev.map(i => i.sku_id === sku_id ? { ...i, qty: newQty } : i));
        }
    };

    // -------------------------------------------------------------
    // Financial Calculations
    // -------------------------------------------------------------
    const totalRefund = useMemo(() => {
        if (!selectedOrder) return 0;
        return selectedOrder.items.reduce((sum: number, item: any) => {
            const qty = returnQtys[item.id] || 0;
            return sum + (item.price * qty);
        }, 0);
    }, [selectedOrder, returnQtys]);

    const exchangeTotal = useMemo(() => {
        return exchangeCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }, [exchangeCart]);

    const balanceDue = exchangeTotal - totalRefund;
    
    const isOrderOld = useMemo(() => {
        if (!selectedOrder) return false;
        const orderDate = new Date(selectedOrder.created_at);
        const daysOld = (new Date().getTime() - orderDate.getTime()) / (1000 * 3600 * 24);
        return daysOld > 30; // 30-day strict return policy
    }, [selectedOrder]);

    // -------------------------------------------------------------
    // Process Transaction
    // -------------------------------------------------------------
    const processTransaction = async () => {
        if (totalRefund <= 0 && exchangeTotal <= 0) return;
        if (!locationId) {
            alert("No active POS location found. Please start a shift on the Dashboard first.");
            return;
        }

        const itemsToReturn = Object.entries(returnQtys)
            .filter(([_, qty]) => qty > 0)
            .map(([id, qty]) => ({ id: parseInt(id), return_qty: qty }));

        if (itemsToReturn.length === 0) {
            alert("Please select at least one item to return/exchange.");
            return;
        }

        const payload = {
            location_id: parseInt(locationId),
            items: itemsToReturn,
            exchange_items: exchangeMode ? exchangeCart : []
        };

        setIsProcessing(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch(`${posBaseUrl}/api/orders/${selectedOrder.id}/refund`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (response.ok) {
                alert(`Success! Transaction Complete.\n\nRefunded/Credited: ₹${result.refunded_amount}${result.new_order_id ? `\nNew Order #: EXC-${result.new_order_id}` : ''}`);
                // Refresh order data
                handleSearch();
            } else {
                alert(`Error: ${result.error || 'Failed to process transaction'}`);
            }
        } catch (error) {
            console.error(error);
            alert("Network error processing transaction.");
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <PosLayout>
            {showCamera && (
                <CameraScanner
                    onClose={() => setShowCamera(false)}
                    onScan={(code) => {
                        setShowCamera(false);
                        processScanCode(code);
                    }}
                />
            )}
            <div className="flex flex-col lg:flex-row h-full w-full bg-gray-50 overflow-hidden">
                {/* Left Side: Order Search & List */}
                <div className={`w-full lg:w-1/3 bg-white border-r border-gray-200 flex-col z-10 shadow-xl shadow-gray-200/50 ${selectedOrder && showMobileDetails ? 'hidden lg:flex' : 'flex'} h-full`}>
                    <div className="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 className="text-2xl font-bold uppercase tracking-widest text-black mb-6">Order Lookup</h2>
                        <form onSubmit={handleSearch} className="relative">
                            <input 
                                type="text" 
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Order # or Phone Number"
                                className="w-full pl-12 pr-4 py-4 bg-white border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-black transition-colors font-medium text-lg placeholder:font-normal shadow-sm"
                                autoFocus
                            />
                            <svg className="w-6 h-6 absolute left-4 top-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button type="submit" className="hidden">Search</button>
                        </form>
                    </div>
                    
                    <div className="flex-1 lg:overflow-y-auto p-4 space-y-3 pb-32 lg:pb-4 h-full">
                        {isSearching ? (
                            <div className="text-center py-10 text-gray-500 font-bold uppercase tracking-widest text-sm">Searching...</div>
                        ) : orders.length === 0 ? (
                            <div className="text-center py-10 text-gray-400 font-medium">No orders found</div>
                        ) : (
                            orders.map(order => (
                                <div 
                                    key={order.id} 
                                    onClick={() => selectOrder(order)}
                                    className={`p-4 rounded-xl cursor-pointer border transition-all ${selectedOrder?.id === order.id ? 'border-black shadow-lg bg-black text-white' : 'border-gray-200 bg-white hover:border-black'}`}
                                >
                                    <div className="flex justify-between items-start mb-2">
                                        <span className="font-bold tracking-wide">{order.order_number}</span>
                                        <span className={`font-bold ${selectedOrder?.id === order.id ? 'text-gray-300' : 'text-gray-500'}`}>₹{order.total_amount}</span>
                                    </div>
                                    <div className={`text-sm ${selectedOrder?.id === order.id ? 'text-gray-400' : 'text-gray-500'}`}>
                                        {order.customer_name || 'Walk-in'} • {order.customer_phone || 'No Phone'}
                                    </div>
                                    <div className={`text-xs mt-2 font-medium flex justify-between ${selectedOrder?.id === order.id ? 'text-gray-400' : 'text-gray-400'}`}>
                                        <span>{new Date(order.created_at).toLocaleDateString()}</span>
                                        <span>{order.location_name || 'Online Store'}</span>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Right Side: Exchange/Return Dashboard */}
                <div className={`w-full lg:w-2/3 flex-col bg-gray-50 relative overflow-hidden h-full ${selectedOrder && showMobileDetails ? 'flex' : 'hidden lg:flex'}`}>
                    {!selectedOrder ? (
                        <div className="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <span className="text-6xl mb-4 opacity-50">🔄</span>
                            <p className="text-lg font-bold uppercase tracking-widest text-gray-400">Select an order to begin</p>
                        </div>
                    ) : (
                        <>
                            {/* Header */}
                            <div className="shrink-0 p-4 lg:p-6 bg-white border-b border-gray-200 shadow-sm flex flex-col z-10">
                                <div className="flex justify-between items-start lg:items-center w-full">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2 lg:gap-3 mb-1">
                                            <button onClick={() => setShowMobileDetails(false)} className="lg:hidden p-1 -ml-1 text-gray-500 hover:text-black">
                                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" /></svg>
                                            </button>
                                            <h3 className="text-lg lg:text-2xl font-bold uppercase text-black truncate">{selectedOrder.order_number}</h3>
                                            {isOrderOld && (
                                                <span className="hidden sm:inline-block bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">Past 30 Days</span>
                                            )}
                                        </div>
                                        <p className="text-gray-500 font-medium text-xs lg:text-sm pl-8 lg:pl-0">
                                            Paid: ₹{selectedOrder.total_amount} 
                                            {selectedOrder.discount_amount > 0 && ` (-₹${selectedOrder.discount_amount})`}
                                        </p>
                                    </div>
                                    
                                    <div className="flex gap-1.5 lg:gap-2 ml-2 shrink-0">
                                        <button 
                                            onClick={() => setExchangeMode(true)}
                                            className={`px-3 lg:px-4 py-2 rounded-lg font-bold transition flex items-center gap-1 lg:gap-2 ${exchangeMode ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                        >
                                            <span className="text-sm">EXC</span><span className="hidden lg:inline text-sm">🛍️</span>
                                        </button>
                                        <button 
                                            onClick={() => {
                                                setExchangeMode(false);
                                                setActiveMobileTab('returns');
                                            }}
                                            className={`px-3 lg:px-4 py-2 rounded-lg font-bold transition ${!exchangeMode ? 'bg-black text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                        >
                                            <span className="text-sm">RET</span>
                                        </button>
                                    </div>
                                </div>
                                
                                {/* Mobile Tabs (Only shown on mobile when Exchange Mode is active) */}
                                {exchangeMode && (
                                    <div className="flex lg:hidden mt-4 bg-gray-100 p-1 rounded-xl shadow-inner gap-1">
                                        <button 
                                            onClick={() => setActiveMobileTab('returns')}
                                            className={`flex-1 py-2.5 rounded-lg font-bold text-xs uppercase tracking-wider transition ${activeMobileTab === 'returns' ? 'bg-white shadow text-black' : 'text-gray-500 hover:text-gray-900'}`}
                                        >
                                            Returning Items (Credit)</button>
                                        <button 
                                            onClick={() => {
                                                let currentTotalRefund = 0;
                                                selectedOrder.items.forEach((item: any) => {
                                                    const qty = returnQtys[item.id] || 0;
                                                    currentTotalRefund += (qty * item.price);
                                                });
                                                if (currentTotalRefund <= 0) {
                                                    alert("Please select at least one item to return before adding new items.");
                                                    return;
                                                }
                                                setActiveMobileTab('new');
                                            }}
                                            className={`flex-1 py-2.5 rounded-lg font-bold text-xs uppercase tracking-wider transition ${activeMobileTab === 'new' ? 'bg-white shadow text-black' : 'text-gray-500 hover:text-gray-900'}`}
                                        >
                                            New Items (Debit)</button>
                                    </div>
                                )}
                            </div>

                            {/* Main Content Area */}
                            <div className="flex-1 flex flex-col lg:flex-row overflow-y-auto lg:overflow-hidden">
                                
                                {/* Left Pane: Returns */}
                                <div className={`${activeMobileTab === 'returns' || !exchangeMode ? 'flex' : 'hidden'} lg:flex flex-none lg:flex-1 flex-col lg:overflow-y-auto ${exchangeMode ? 'border-b lg:border-b-0 lg:border-r border-gray-200' : ''}`}>
                                    <div className="p-4 bg-gray-100 border-b border-gray-200 sticky top-0 z-10">
                                        <h4 className="font-bold text-gray-700 uppercase tracking-widest text-xs">Returning Items (Credit)</h4>
                                    </div>
                                    <div className="p-4 space-y-3">
                                        {selectedOrder.items.map((item: any) => {
                                            const maxReturnable = item.quantity - (item.returned_quantity || 0);
                                            const currentReturnQty = returnQtys[item.id] || 0;
                                            const isFullyReturned = maxReturnable === 0;

                                            return (
                                                <div key={item.id} className={`bg-white p-4 rounded-xl shadow-sm border ${isFullyReturned ? 'border-gray-100 opacity-50' : 'border-gray-200'}`}>
                                                    <div className="flex gap-3 mb-2">
                                                        {item.image_url ? (
                                                            <img 
                                                                src={item.image_url} 
                                                                onClick={() => setImageModal({ isOpen: true, title: item.product_name, images: item.gallery || [item.image_url] })}
                                                                className="w-16 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition"
                                                            />
                                                        ) : (
                                                            <div className="w-16 h-16 bg-gray-100 rounded-lg flex-shrink-0 border border-gray-200" />
                                                        )}
                                                        <div className="flex-1">
                                                            <div className="flex justify-between items-start">
                                                                <div className="flex flex-col">
                                                                    <h5 className="font-bold text-gray-900">{item.product_name}</h5>
                                                                    {item.variant_name && <p className="text-xs text-gray-500 font-medium mt-0.5">{item.variant_name}</p>}
                                                                </div>
                                                                <span className="font-bold text-gray-900">₹{item.price}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="text-xs text-gray-500 mb-3 flex justify-between">
                                                        <span>Bought: {item.quantity} | Already Returned: {item.returned_quantity || 0}</span>
                                                    </div>
                                                    
                                                    {isFullyReturned ? (
                                                                                                            <div className="text-center text-xs font-bold uppercase tracking-widest text-gray-400 bg-gray-50 py-2 rounded-lg">
                                                            Fully {selectedOrder.status === 'exchanged' || selectedOrder.status === 'partially returned' ? 'Exchanged' : 'Returned'}
                                                        </div>
                                                    ) : (
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-sm font-medium text-gray-500">Return Qty:</span>
                                                            <div className="flex items-center space-x-3 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                                                <button onClick={() => updateReturnQty(item.id, -1, maxReturnable)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 active:scale-95 transition">-</button>
                                                                <span className="font-bold text-gray-900 w-4 text-center">{currentReturnQty}</span>
                                                                <button onClick={() => updateReturnQty(item.id, 1, maxReturnable)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 active:scale-95 transition">+</button>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Right Pane: Exchange Cart (Only visible in Exchange Mode) */}
                                {exchangeMode && (
                                    <div className={`${activeMobileTab === 'new' ? 'flex' : 'hidden'} lg:flex flex-none lg:flex-1 flex-col bg-white lg:overflow-hidden shadow-[-4px_0_15px_rgba(0,0,0,0.03)]`}>
                                        <div className="p-4 bg-blue-50 border-b border-blue-100 sticky top-0 z-10">
                                            <h4 className="font-bold text-blue-800 uppercase tracking-widest text-xs flex justify-between">
                                                <span>New Items (Debit)</span>
                                                <span className="bg-blue-200 text-blue-900 px-2 py-0.5 rounded">Store: {locations?.find((l:any) => l.id == locationId)?.name || locationId}</span>
                                            </h4>
                                        </div>
                                        
                                        <div className="p-4 border-b border-gray-100 flex gap-2">
                                            <input 
                                                type="text" 
                                                placeholder="Scan barcode or press Enter..." 
                                                value={scanInput}
                                                onChange={(e) => setScanInput(e.target.value)}
                                                onKeyDown={handleScan}
                                                className="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-black focus:border-black transition-colors font-medium"
                                                autoFocus
                                            />
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
                                            </button>
                                        </div>

                                        <div className="flex-1 overflow-y-auto p-4 space-y-3">
                                            {exchangeCart.length === 0 ? (
                                                <div className="h-full flex items-center justify-center text-gray-400 text-sm font-medium">
                                                    Scan items to add to exchange cart
                                                </div>
                                            ) : (
                                                exchangeCart.map(item => (
                                                    <div key={item.sku_id} className="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                                                        <div className="flex gap-3 mb-3">
                                                            {item.image ? (
                                                                <img 
                                                                    src={item.image} 
                                                                    onClick={() => setImageModal({ isOpen: true, title: item.name, images: item.gallery || [item.image] })}
                                                                    className="w-16 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition"
                                                                />
                                                            ) : (
                                                                <div className="w-16 h-16 bg-gray-100 rounded-lg flex-shrink-0 border border-gray-200" />
                                                            )}
                                                            <div className="flex-1">
                                                                <div className="flex justify-between items-start">
                                                                    <div className="flex flex-col">
                                                                        <h5 className="font-bold text-gray-900 line-clamp-2 pr-2">{item.name}</h5>
                                                                        {(item.color_name || item.size_name) && (item.color_name !== 'Default' || item.size_name !== 'Default') && (
                                                                            <p className="text-xs text-gray-500 font-medium mt-0.5">
                                                                                {[item.color_name !== 'Default' ? item.color_name : null, item.size_name !== 'Default' ? item.size_name : null].filter(Boolean).join(' / ')}
                                                                            </p>
                                                                        )}
                                                                    </div>
                                                                    <span className="font-bold text-gray-900 whitespace-nowrap">₹{item.price}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center justify-between">
                                                            <button onClick={() => updateExchangeQty(item.sku_id, 0)} className="text-red-500 text-xs font-bold uppercase tracking-wider hover:underline">Remove</button>
                                                            <div className="flex items-center space-x-3 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                                                <button onClick={() => updateExchangeQty(item.sku_id, item.qty - 1)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 active:scale-95 transition">-</button>
                                                                <span className="font-bold text-gray-900 w-4 text-center">{item.qty}</span>
                                                                <button onClick={() => updateExchangeQty(item.sku_id, item.qty + 1)} className="w-8 h-8 bg-white rounded shadow-sm flex items-center justify-center font-bold text-gray-700 active:scale-95 transition">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Bottom Financial Action Bar */}
                            <div className="shrink-0 p-6 bg-white border-t border-gray-200 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] z-20">
                                <div className="flex justify-between items-center mb-4">
                                    <div className="flex gap-8">
                                        <div>
                                            <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Return Credit</p>
                                            <p className="text-xl font-bold text-green-600">+₹{totalRefund.toFixed(2)}</p>
                                        </div>
                                        {exchangeMode && (
                                            <div>
                                                <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">New Items</p>
                                                <p className="text-xl font-bold text-red-500">-₹{exchangeTotal.toFixed(2)}</p>
                                            </div>
                                        )}
                                    </div>
                                    <div className="text-right">
                                        <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                                            {balanceDue > 0 ? 'Balance Due (Collect)' : 'Refund Due (Pay)'}
                                        </p>
                                        <p className={`text-4xl font-bold ${balanceDue > 0 ? 'text-black' : 'text-black'}`}>
                                            ₹{Math.abs(balanceDue).toFixed(2)}
                                        </p>
                                    </div>
                                </div>
                                <button 
                                    disabled={totalRefund <= 0 && exchangeTotal <= 0 || isProcessing}
                                    onClick={processTransaction}
                                    className={`w-full py-4 rounded-xl font-bold text-xl transition shadow-xl uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed ${balanceDue > 0 ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-200' : 'bg-black text-white hover:bg-gray-900 shadow-gray-300'}`}
                                >
                                    {isProcessing ? "Processing..." : (balanceDue > 0 ? `Collect ₹${balanceDue.toFixed(2)} & Process` : `Process Refund of ₹${Math.abs(balanceDue).toFixed(2)}`)}
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        
            {/* Image Gallery Modal */}
            {imageModal.isOpen && (
                <div className="fixed inset-0 z-[150] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white rounded-2xl max-w-4xl w-full flex flex-col max-h-[90vh] overflow-hidden shadow-2xl animate-fade-in-up">
                        <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 className="text-xl font-bold text-gray-900 uppercase tracking-wide truncate pr-4">{imageModal.title}</h3>
                            <button onClick={() => setImageModal({ isOpen: false, title: '', images: [] })} className="text-gray-400 hover:text-black bg-white shadow-sm border border-gray-200 rounded-full p-2 transition">
                                ✕
                            </button>
                        </div>
                        <div className="p-6 overflow-y-auto flex-1 bg-gray-100">
                            {imageModal.images.length === 0 ? (
                                <div className="text-center text-gray-400 font-bold py-10 uppercase tracking-widest">No images available</div>
                            ) : (
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {imageModal.images.map((img, idx) => (
                                        <div key={idx} className="bg-white rounded-xl overflow-hidden shadow-md border border-gray-200">
                                            <img src={img} className="w-full h-auto object-contain bg-gray-50 aspect-[3/4]" />
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

        </PosLayout>
    );
}

Returns.layout = (page: React.ReactNode) => page;
