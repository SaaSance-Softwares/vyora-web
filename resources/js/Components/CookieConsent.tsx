import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Cookie, X, Check } from 'lucide-react';

export default function CookieConsent() {
    const { props } = usePage<any>();
    const settings = props.settings || {};
    
    const position = settings.cookie_widget_position === 'right' ? 'right' : 'left';
    const themeColor = settings.cookie_widget_color || '#000000';
    
    const [showBanner, setShowBanner] = useState(false);
    const [isFloating, setIsFloating] = useState(false);
    
    useEffect(() => {
        const consent = localStorage.getItem('cookie_consent');
        if (!consent) {
            setShowBanner(true);
        } else {
            setIsFloating(true);
        }
    }, []);

    const acceptAll = () => {
        localStorage.setItem('cookie_consent', 'all');
        setShowBanner(false);
        setIsFloating(true);
        window.dispatchEvent(new Event('cookie_consent_updated'));
    };

    const rejectNonEssential = () => {
        localStorage.setItem('cookie_consent', 'essential');
        setShowBanner(false);
        setIsFloating(true);
        window.dispatchEvent(new Event('cookie_consent_updated'));
    };
    
    if (!showBanner && !isFloating) return null;

    if (showBanner) {
        return (
            <div className={`fixed bottom-6 ${position === 'right' ? 'right-6' : 'left-6'} z-[9999] w-[340px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 animate-in slide-in-from-bottom-8 duration-500`}>
                <div className="flex items-start gap-3 mb-4">
                    <div className="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style={{ backgroundColor: `${themeColor}15` }}>
                        <Cookie className="w-5 h-5" style={{ color: themeColor }} />
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-gray-900 mb-1">Your Privacy Matters</h3>
                        <p className="text-xs text-gray-500 leading-relaxed">
                            We use cookies to improve your experience and deliver personalized advertising. 
                            <a href="/cookie-policy" className="underline ml-1 hover:text-gray-900">Read policy</a>
                        </p>
                    </div>
                </div>
                <div className="flex flex-col gap-2">
                    <button 
                        onClick={acceptAll}
                        className="w-full py-2.5 rounded-xl text-white text-xs font-bold uppercase tracking-wider transition-opacity hover:opacity-90"
                        style={{ backgroundColor: themeColor }}
                    >
                        Accept All
                    </button>
                    <button 
                        onClick={rejectNonEssential}
                        className="w-full py-2.5 rounded-xl text-gray-600 bg-gray-50 border border-gray-200 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors"
                    >
                        Essential Only
                    </button>
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
