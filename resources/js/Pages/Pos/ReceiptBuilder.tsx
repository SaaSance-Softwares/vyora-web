import React, { useState } from 'react';
import PosLayout from './Layout';

export default function ReceiptBuilder() {
    const [brandName, setBrandName] = useState('VYORA PREMIUM');
    const [address, setAddress] = useState('123 Fashion Street, New Delhi\nGSTIN: 22AAAAA0000A1Z5');
    const [footerText, setFooterText] = useState('Thank you for shopping!\nNo returns on discounted items.');
    const [printerSize, setPrinterSize] = useState('80mm'); // 58mm or 80mm
    const [barcodeType, setBarcodeType] = useState('QR'); // 1D, QR, None

    // Simulated Cart for Preview
    const previewCart = [
        { name: "Premium T-Shirt", qty: 2, price: 499, total: 998 },
        { name: "Denim Jacket", qty: 1, price: 1299, total: 1299 }
    ];

    return (
        <PosLayout title="Receipt Builder">
            <div className="flex w-full h-[calc(100vh-4rem)] bg-gray-50 flex-col lg:flex-row overflow-hidden">
                
                {/* Left Side: Settings Panel */}
                <div className="w-full lg:w-1/2 p-6 lg:p-10 overflow-y-auto bg-white border-r border-gray-200 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-10">
                    <div className="flex justify-between items-center mb-8">
                        <h2 className="text-2xl font-bold text-black uppercase tracking-wider">Receipt Designer</h2>
                    </div>
                    
                    <div className="space-y-6">
                        {/* Printer Size */}
                        <div>
                            <label className="block text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">Paper Width</label>
                            <div className="flex space-x-4">
                                <label className={`flex-1 p-4 border rounded-xl cursor-pointer text-center font-bold transition-all shadow-sm ${printerSize === '58mm' ? 'border-black bg-black text-white' : 'border-gray-300 hover:border-black text-gray-700 bg-white'}`} onClick={() => setPrinterSize('58mm')}>
                                    58mm (Small)
                                </label>
                                <label className={`flex-1 p-4 border rounded-xl cursor-pointer text-center font-bold transition-all shadow-sm ${printerSize === '80mm' ? 'border-black bg-black text-white' : 'border-gray-300 hover:border-black text-gray-700 bg-white'}`} onClick={() => setPrinterSize('80mm')}>
                                    80mm (Standard)
                                </label>
                            </div>
                        </div>

                        {/* Brand Name */}
                        <div>
                            <label className="block text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Brand Name</label>
                            <input 
                                type="text" 
                                value={brandName}
                                onChange={(e) => setBrandName(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-1 focus:ring-black focus:border-black bg-gray-50 focus:bg-white transition-colors"
                            />
                        </div>

                        {/* Address & GST */}
                        <div>
                            <label className="block text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Address & GSTIN</label>
                            <textarea 
                                value={address}
                                onChange={(e) => setAddress(e.target.value)}
                                rows={3}
                                className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-1 focus:ring-black focus:border-black bg-gray-50 focus:bg-white transition-colors"
                            />
                        </div>

                        {/* Footer Text */}
                        <div>
                            <label className="block text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Footer / Return Policy</label>
                            <textarea 
                                value={footerText}
                                onChange={(e) => setFooterText(e.target.value)}
                                rows={3}
                                className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-1 focus:ring-black focus:border-black bg-gray-50 focus:bg-white transition-colors"
                            />
                        </div>

                        {/* Barcode Setting */}
                        <div>
                            <label className="block text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Bottom Barcode (For Returns)</label>
                            <select 
                                value={barcodeType}
                                onChange={(e) => setBarcodeType(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-1 focus:ring-black focus:border-black bg-gray-50 focus:bg-white transition-colors"
                            >
                                <option value="QR">2D QR Code</option>
                                <option value="1D">1D Barcode</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                        
                        <button className="w-full bg-black text-white p-4 rounded-xl font-bold text-lg hover:bg-gray-900 transition active:scale-95 shadow-xl shadow-gray-300 mt-8 uppercase tracking-wide">
                            Save Template
                        </button>
                    </div>
                </div>

                {/* Right Side: Live Preview */}
                <div className="w-full lg:w-1/2 p-6 lg:p-10 flex flex-col items-center overflow-y-auto bg-gray-200/80 inset-0">
                    <p className="text-gray-500 font-bold mb-6 uppercase tracking-widest text-sm bg-white/50 px-4 py-1.5 rounded-full border border-gray-300">Live Thermal Preview</p>
                    
                    {/* The Receipt Paper */}
                    <div 
                        className="bg-white shadow-[0_20px_50px_rgba(0,0,0,0.1)] p-6 text-black font-mono transition-all duration-300 relative mx-auto"
                        style={{ 
                            width: printerSize === '58mm' ? '280px' : '380px',
                            minHeight: '400px'
                        }}
                    >
                        {/* Zigzag Top edge using CSS */}
                        <div className="absolute top-0 left-0 right-0 h-2 -mt-2 opacity-50 flex overflow-hidden">
                            {[...Array(30)].map((_, i) => (
                                <div key={i} className="w-3 h-3 bg-white rotate-45 transform -translate-y-2 translate-x-1 shrink-0"></div>
                            ))}
                        </div>
                        
                        {/* Header */}
                        <div className="text-center mb-6 border-b border-dashed border-gray-400 pb-4">
                            <h1 className="text-2xl font-bold uppercase mb-2 leading-none">{brandName}</h1>
                            <p className="text-xs whitespace-pre-wrap leading-tight text-gray-700">{address}</p>
                            <p className="text-[11px] mt-3 text-gray-500">Date: {new Date().toLocaleDateString()} {new Date().toLocaleTimeString()}</p>
                            <p className="text-[11px] text-gray-500">Receipt #: POS-123456</p>
                        </div>

                        {/* Items */}
                        <div className="mb-4 min-h-[120px]">
                            <table className="w-full text-xs">
                                <thead>
                                    <tr className="border-b border-dashed border-gray-400">
                                        <th className="text-left pb-2 text-gray-600 font-medium">Item</th>
                                        <th className="text-center pb-2 text-gray-600 font-medium">Qty</th>
                                        <th className="text-right pb-2 text-gray-600 font-medium">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {previewCart.map((item, idx) => (
                                        <tr key={idx}>
                                            <td className="py-2 pr-2 font-medium">{item.name}</td>
                                            <td className="text-center py-2">{item.qty}</td>
                                            <td className="text-right py-2 font-medium">₹{item.total}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals */}
                        <div className="border-t border-dashed border-gray-400 pt-3 mb-6 space-y-1.5">
                            <div className="flex justify-between text-xs text-gray-600">
                                <span>Subtotal:</span>
                                <span>₹2297.00</span>
                            </div>
                            <div className="flex justify-between text-xs text-gray-600">
                                <span>GST (Included):</span>
                                <span>₹109.38</span>
                            </div>
                            <div className="flex justify-between font-bold text-[15px] mt-3 pt-3 border-t border-dashed border-gray-400">
                                <span>TOTAL:</span>
                                <span>₹2297.00</span>
                            </div>
                        </div>

                        {/* Footer & Barcode */}
                        <div className="text-center pt-5 border-t border-dashed border-gray-400">
                            <p className="text-[11px] whitespace-pre-wrap mb-5 text-gray-600">{footerText}</p>
                            
                            {barcodeType === 'QR' && (
                                <div className="flex justify-center mb-2">
                                    <div className="w-28 h-28 border-[3px] border-black p-1 flex items-center justify-center relative">
                                        <div className="w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTMgM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNCAwdi0yaC04djhoOHYtNGgtMnYtMmgyeXpNMyAxM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNiAySDE1djRINDUydi0yaDR2LTJoMnY0aDJ2LTR6bS0yLTJoLTR2MmgydjIuNWgzdi00LjVoLTF6IiBmaWxsPSJjdXJyZW50Q29sb3IiLz48L3N2Zz4=')] opacity-80"></div>
                                    </div>
                                </div>
                            )}
                            
                            {barcodeType === '1D' && (
                                <div className="flex justify-center mb-2">
                                    <div className="w-56 h-14 bg-[repeating-linear-gradient(90deg,#000,#000_3px,transparent_3px,transparent_6px,#000_6px,#000_8px,transparent_8px,transparent_11px)] opacity-90"></div>
                                </div>
                            )}
                            
                            {barcodeType !== 'None' && <p className="text-[10px] tracking-widest mt-1 text-gray-500">POS-123456</p>}
                        </div>

                        {/* Zigzag Bottom edge */}
                        <div className="absolute bottom-0 left-0 right-0 h-2 -mb-2 opacity-50 flex overflow-hidden">
                            {[...Array(30)].map((_, i) => (
                                <div key={i} className="w-3 h-3 bg-white rotate-45 transform translate-y-2 translate-x-1 shrink-0"></div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </PosLayout>
    );
}

ReceiptBuilder.layout = (page: React.ReactNode) => page;
