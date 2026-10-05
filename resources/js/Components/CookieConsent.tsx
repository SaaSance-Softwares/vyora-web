import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Cookie, X, Check, Settings } from 'lucide-react';
import api from '@/lib/api';
import { useAuthStore } from '@/store/auth';

export default function CookieConsent() {
    const { props } = usePage<any>();
    const settings = props.settings || {};
    const { user, checkAuth } = useAuthStore();
    
    const position = settings.cookie_widget_position === 'right' ? 'right' : 'left';
    const themeColor = settings.cookie_widget_color || '#000000';
    
    const [showBanner, setShowBanner] = useState(false);
    const [isFloating, setIsFloating] = useState(false);
    const [showCustomize, setShowCustomize] = useState(false);
    
    // Toggles for customize modal
    const [toggles, setToggles] = useState({
        google_analytics: false,
        meta_pixel: false,
        snapchat_pixel: false,
    });
    
    useEffect(() => {
        const consent = localStorage.getItem('cookie_consent');
        if (!consent) {
            setShowBanner(true);
        } else {
            setIsFloating(true);
            try {
                const parsed = JSON.parse(consent);
                setToggles({
                    google_analytics: !!parsed.google_analytics,
                    meta_pixel: !!parsed.meta_pixel,
                    snapchat_pixel: !!parsed.snapchat_pixel,
                });
            } catch (e) {
                if (consent === 'all') {
                    setToggles({ google_analytics: true, meta_pixel: true, snapchat_pixel: true });
                }
            }
        }
    }, []);
    
    // Sync with user profile on load if they are logged in
    useEffect(() => {
        if (user?.tracking_consent) {
            setToggles({
                google_analytics: !!user.tracking_consent.google_analytics,
                meta_pixel: !!user.tracking_consent.meta_pixel,
                snapchat_pixel: !!user.tracking_consent.snapchat_pixel,
            });
            localStorage.setItem('cookie_consent', JSON.stringify(user.tracking_consent));
            window.dispatchEvent(new Event('cookie_consent_updated'));
        }
    }, [user?.tracking_consent]);

    const savePreferences = async (prefs: any) => {
        localStorage.setItem('cookie_consent', JSON.stringify(prefs));
        setShowBanner(false);
        setShowCustomize(false);
        setIsFloating(true);
        window.dispatchEvent(new Event('cookie_consent_updated'));
        
        if (user) {
            try {
                await api.put('/api/account/profile', {
                    name: user.name,
                    email: user.email,
                    tracking_consent: prefs
                });
                checkAuth();
            } catch(e) {}
        }
    };

    const addTimestamps = (prefs: any) => {
        const now = new Date().toISOString();
        return {
            ...prefs,
            google_analytics_timestamp: now,
            meta_pixel_timestamp: now,
            snapchat_pixel_timestamp: now
        };
    };

    const acceptAll = () => {
        const prefs = { google_analytics: true, meta_pixel: true, snapchat_pixel: true };
        setToggles(prefs);
        savePreferences(addTimestamps(prefs));
    };

    const rejectNonEssential = () => {
        const prefs = { google_analytics: false, meta_pixel: false, snapchat_pixel: false };
        setToggles(prefs);
        savePreferences(addTimestamps(prefs));
    };
    
    const saveCustom = () => {
        savePreferences(addTimestamps(toggles));
    };

    if (!showBanner && !isFloating && !showCustomize) return null;

    if (showCustomize) {
        return (
            <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-in fade-in duration-200">
                <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-300">
                    <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div className="flex items-center gap-2">
                            <Settings className="w-5 h-5 text-gray-700" />
                            <h3 className="font-bold text-gray-900">Privacy Preferences</h3>
                        </div>
                        <button onClick={() => {setShowCustomize(false); setShowBanner(true);}} className="p-1 hover:bg-gray-200 rounded-full transition-colors text-gray-500">
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                    <div className="p-6 space-y-6">
                        <div className="space-y-4">
                            <div className="flex justify-between items-center p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span className="text-sm font-semibold text-gray-900 block">Strictly Necessary</span>
                                    <span className="text-xs text-gray-500">Required for site functionality</span>
                                </div>
                                <span className="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded uppercase tracking-wider">Always On</span>
                            </div>
                            
                            <div className="flex justify-between items-center p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span className="text-sm font-semibold text-gray-900 block">Google Analytics</span>
                                    <span className="text-xs text-gray-500">Anonymous traffic analysis</span>
                                </div>
                                <label className="flex items-center cursor-pointer relative">
                                    <input type="checkbox" className="sr-only peer" checked={toggles.google_analytics} onChange={(e) => setToggles({...toggles, google_analytics: e.target.checked})} />
                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all" style={{ backgroundColor: toggles.google_analytics ? themeColor : undefined }}></div>
                                </label>
                            </div>
                            
                            <div className="flex justify-between items-center p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span className="text-sm font-semibold text-gray-900 block">Meta Pixel</span>
                                    <span className="text-xs text-gray-500">Personalized Facebook/IG ads</span>
                                </div>
                                <label className="flex items-center cursor-pointer relative">
                                    <input type="checkbox" className="sr-only peer" checked={toggles.meta_pixel} onChange={(e) => setToggles({...toggles, meta_pixel: e.target.checked})} />
                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all" style={{ backgroundColor: toggles.meta_pixel ? themeColor : undefined }}></div>
                                </label>
                            </div>

                            <div className="flex justify-between items-center p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span className="text-sm font-semibold text-gray-900 block">Snapchat Pixel</span>
                                    <span className="text-xs text-gray-500">Personalized Snapchat ads</span>
                                </div>
                                <label className="flex items-center cursor-pointer relative">
                                    <input type="checkbox" className="sr-only peer" checked={toggles.snapchat_pixel} onChange={(e) => setToggles({...toggles, snapchat_pixel: e.target.checked})} />
                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all" style={{ backgroundColor: toggles.snapchat_pixel ? themeColor : undefined }}></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div className="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                        <button onClick={() => {setShowCustomize(false); setShowBanner(true);}} className="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors">Cancel</button>
                        <button onClick={saveCustom} className="px-6 py-2 rounded-lg text-white text-sm font-bold transition-opacity hover:opacity-90 shadow-sm" style={{ backgroundColor: themeColor }}>Save Preferences</button>
                    </div>
                </div>
            </div>
        );
    }

    if (showBanner) {
        return (
            <div className={`fixed bottom-6 ${position === 'right' ? 'right-6' : 'left-6'} z-[9999] w-[340px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 animate-in slide-in-from-bottom-8 duration-500`}>
                <div className="flex items-start gap-3 mb-4">
                    <div className="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style={{ backgroundColor: `${themeColor}15` }}>
                        <Cookie className="w-5 h-5" style={{ color: themeColor }} />
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-gray-900 mb-1">Your Privacy Matters</h3>
                        <p className="text-[11px] text-gray-500 leading-relaxed">
                            We use cookies to improve your experience. 
                            <a href="/cookie-policy" className="underline ml-1 hover:text-gray-900">Read policy</a>
                        </p>
                    </div>
                </div>
                <div className="flex flex-col gap-2">
                    <button onClick={acceptAll} className="w-full py-2.5 rounded-xl text-white text-xs font-bold uppercase tracking-wider transition-opacity hover:opacity-90 shadow-sm" style={{ backgroundColor: themeColor }}>
                        Accept All
                    </button>
                    <div className="flex gap-2">
                        <button onClick={rejectNonEssential} className="flex-1 py-2.5 rounded-xl text-gray-600 bg-gray-50 border border-gray-200 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors">
                            Essential Only
                        </button>
                        <button onClick={() => {setShowBanner(false); setShowCustomize(true);}} className="flex-1 py-2.5 rounded-xl text-gray-600 bg-gray-50 border border-gray-200 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors">
                            Customize
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    if (isFloating) {
        return (
            <button 
                onClick={() => { setShowBanner(true); setIsFloating(false); }}
                className={`fixed bottom-6 ${position === 'right' ? 'right-6' : 'left-6'} z-[9999] w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform`}
                style={{ backgroundColor: themeColor }}
                aria-label="Cookie Preferences"
            >
                <Cookie className="w-5 h-5 text-white" />
            </button>
        );
    }

    return null;
}
