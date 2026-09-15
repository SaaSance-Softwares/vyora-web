import React, { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

export default function PosLayout({ 
    children, 
    title, 
    syncCount = 0, 
    onSync = undefined 
}: { 
    children: React.ReactNode, 
    title?: string,
    syncCount?: number,
    onSync?: () => void
}) {
    const [showOptionsModal, setShowOptionsModal] = useState(false);
    const [showIosPrompt, setShowIosPrompt] = useState(false);
    const [posBaseUrl, setPosBaseUrl] = useState('');
    const { url } = usePage();
    const isReturnsPage = url.includes('/returns');

    useEffect(() => {
        if (typeof window !== 'undefined') setPosBaseUrl('/' + window.location.pathname.split('/')[1]);
        // Detect iOS device
        const isIos = () => {
            const userAgent = window.navigator.userAgent.toLowerCase();
            return /iphone|ipad|ipod/.test(userAgent);
        };
        // Detect if already running in standalone PWA mode
        const isInStandaloneMode = () => {
            return ('standalone' in window.navigator) && !!(window.navigator as any).standalone;
        };

        if (isIos() && !isInStandaloneMode()) {
            const hasSeenPrompt = localStorage.getItem('has_seen_ios_pwa_prompt');
            if (!hasSeenPrompt) {
                // Show prompt after a short delay so it doesn't jarringly block the UI immediately
                setTimeout(() => setShowIosPrompt(true), 2000);
            }
        }
    }, []);

    const dismissIosPrompt = () => {
        localStorage.setItem('has_seen_ios_pwa_prompt', 'true');
        setShowIosPrompt(false);
    };

    const handleEndShift = () => {
        if (syncCount > 0) {
            alert("You have pending offline orders! Please connect to the internet and sync before ending your shift.");
            return;
        }
        
        if (confirm("Are you sure you want to end this shift? This will clear your current POS session.")) {
            localStorage.removeItem('pos_catalog');
            localStorage.removeItem('pos_location_id');
            localStorage.removeItem('pos_location');
            localStorage.removeItem('pos_sync_queue');
            window.location.reload();
        }
    };

    const handleHardRefresh = async () => {
        if (confirm("This will refresh the POS app and update to the latest version. Proceed?")) {
            if ('serviceWorker' in navigator) {
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (let registration of registrations) {
                    await registration.unregister();
                }
            }
            if ('caches' in window) {
                const keys = await caches.keys();
                for (let key of keys) {
                    await caches.delete(key);
                }
            }
            window.location.reload();
        }
    };

    return (
        <div className="min-h-[100dvh] h-[100dvh] bg-gray-50 flex flex-col font-sans overflow-hidden">
            {title && <Head title={title} />}

            {/* Top Navbar */}
            <header className="bg-black text-white shadow-md z-10 relative border-b border-gray-800">
                <div className="w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div className="flex items-center">
                        <Link href={posBaseUrl || '#'} className="font-bold text-xl tracking-tight uppercase hover:text-gray-300">Vyora POS</Link>
                        <span className="ml-4 px-3 py-1 bg-gray-800 text-xs rounded-full uppercase tracking-widest hidden sm:inline-block font-semibold">
                            Offline Ready
                        </span>
                    </div>
                    <div className="flex items-center space-x-6">
                        {posBaseUrl && (
                            <Link href={isReturnsPage ? posBaseUrl : `${posBaseUrl}/returns`} className="text-gray-300 hover:text-white font-bold text-sm uppercase tracking-wider hidden sm:block bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-700 transition active:scale-95">
                                {isReturnsPage ? 'Sales' : 'Returns & Exchanges'}
                            </Link>
                        )}
                        
                        {/* Dynamic Sync Indicator */}
                        <div 
                            className={`flex items-center space-x-2 text-sm font-medium ${syncCount > 0 ? 'cursor-pointer hover:opacity-80' : ''}`}
                            onClick={syncCount > 0 && onSync ? onSync : undefined}
                        >
                            {syncCount === 0 ? (
                                <>
                                    <span className="h-3 w-3 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                    <span className="hidden sm:inline text-gray-300">Online & Synced</span>
                                </>
                            ) : (
                                <>
                                    <span className="h-3 w-3 bg-red-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                                    <span className="hidden sm:inline text-white font-bold">{syncCount} Pending Sync</span>
                                </>
                            )}
                        </div>
                        
                        <button 
                            onClick={() => setShowOptionsModal(true)}
                            className="bg-gray-900 hover:bg-gray-800 px-4 py-1.5 rounded-md text-sm transition font-medium shadow-sm border border-gray-700 flex items-center gap-2"
                        >
                            Options ⚙️
                        </button>
                    </div>
                </div>
            </header>

            {/* Main POS Workspace */}
            <main className="flex-1 flex overflow-hidden relative">
                {children}

                {/* Options Modal */}
                {showOptionsModal && (
                    <div className="absolute inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                        <div className="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 animate-fade-in-up">
                            <h2 className="text-xl font-bold text-gray-900 mb-6 text-center uppercase tracking-wide">POS Options</h2>
                            
                            <div className="space-y-4">
                                {posBaseUrl && (
                                    <Link 
                                        href={isReturnsPage ? posBaseUrl : `${posBaseUrl}/returns`} 
                                        className="w-full bg-gray-900 hover:bg-black text-white px-4 py-4 rounded-lg font-bold text-lg shadow-sm transition flex items-center justify-center gap-2 mb-4"
                                    >
                                        {isReturnsPage ? 'Sales 🛒' : 'Returns & Exchanges 🔄'}
                                    </Link>
                                )}

                                <button 
                                    onClick={handleHardRefresh}
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-4 rounded-lg font-bold text-lg shadow-sm transition flex items-center justify-center gap-2"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Hard Refresh App
                                </button>

                                <button 
                                    onClick={handleEndShift}
                                    className="w-full bg-gray-100 hover:bg-gray-200 text-black px-4 py-4 rounded-lg font-bold text-lg border border-gray-300 shadow-sm transition"
                                >
                                    End Shift
                                </button>

                                <Link 
                                    href="/logout" 
                                    method="post" 
                                    as="button" 
                                    className="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-4 rounded-lg font-bold text-lg shadow-sm transition block text-center"
                                >
                                    Exit POS (Log Out)
                                </Link>

                                <button 
                                    onClick={() => setShowOptionsModal(false)}
                                    className="w-full mt-4 text-gray-500 hover:text-black font-semibold py-2"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Custom iOS PWA Prompt */}
                {showIosPrompt && (
                    <div className="absolute bottom-4 left-4 right-4 z-50 bg-white shadow-2xl border border-gray-200 rounded-xl p-4 flex flex-col gap-3 animate-fade-in-up md:max-w-md md:left-auto">
                        <div className="flex justify-between items-start">
                            <h3 className="font-bold text-black text-lg">Install Vyora POS</h3>
                            <button onClick={dismissIosPrompt} className="text-gray-400 hover:text-black p-1">
                                ✕
                            </button>
                        </div>
                        <p className="text-sm text-gray-600">
                            Install this web app on your iPhone for a full-screen, native experience.
                        </p>
                        <div className="bg-gray-50 rounded-lg p-3 text-sm text-gray-800 flex flex-col gap-2 font-medium">
                            <div className="flex items-center gap-2">
                                <span className="bg-white w-6 h-6 rounded flex items-center justify-center shadow-sm border border-gray-200 text-blue-500 text-lg">↑</span>
                                <span>1. Tap the <b>Share</b> button in Safari</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="bg-white w-6 h-6 rounded flex items-center justify-center shadow-sm border border-gray-200 text-lg">⊞</span>
                                <span>2. Tap <b>Add to Home Screen</b></span>
                            </div>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
