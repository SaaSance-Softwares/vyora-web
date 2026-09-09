import { Link, usePage } from '@inertiajs/react';
import React, { useState } from 'react';
import { CreditCard, Wallet, Smartphone, Landmark } from 'lucide-react';
import api from '@/lib/api';

export default function Footer() {
    const { settings } = usePage<any>().props;

    const bgColor = settings.footer_bg_color || '#ffffff';
    const textColor = settings.footer_text_color || '#000000';
    const structure = settings.footer_structure || [];
    const showNewsletter = settings.footer_show_newsletter == '1';
    const showSocial = settings.footer_social_links == '1';

    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);
    const [submitted, setSubmitted] = useState(false);
    const [error, setError] = useState('');

    const handleSubscribe = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!email) return;
        setLoading(true);
        setError('');
        try {
            await api.post('/api/subscribe', { email });
            setSubmitted(true);
        } catch (err: any) {
            if (err.response?.status === 422) {
                if (err.response.data.errors?.email?.[0].includes('already')) {
                    setError('This email is already subscribed.');
                } else {
                    setError(err.response.data.errors?.email?.[0] || 'Invalid email address.');
                }
            } else {
                setError('Something went wrong. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    };

    const socialLinks = [
        { key: 'social_facebook', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fillRule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clipRule="evenodd" /></svg> },
        { key: 'social_instagram', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fillRule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clipRule="evenodd" /></svg> },
        { key: 'social_linkedin', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z" /></svg> },
        { key: 'social_twitter', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" /></svg> },
        { key: 'social_tiktok', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.78-1.15 5.54-3.33 7.39-1.92 1.63-4.58 2.37-7.05 1.83-2.61-.58-4.88-2.31-5.91-4.78-1.1-2.63-.82-5.78.83-8.15 1.48-2.12 3.86-3.44 6.43-3.71v4.19c-1.33.15-2.58.76-3.41 1.76-1.19 1.44-1.25 3.65-.12 5.16 1.05 1.41 3.05 1.95 4.67 1.3 1.57-.63 2.56-2.19 2.66-3.88.13-2.5.06-5.02.09-7.53.03-3.72-.01-7.44.02-11.16H12.525z"/></svg> },
        { key: 'social_pinterest', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.951-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.366 18.607 0 12.017 0z"/></svg> },
        { key: 'social_youtube', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fillRule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 01-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 01-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 011.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418zM15.194 12L10 15V9l5.194 3z" clipRule="evenodd" /></svg> },
        { key: 'social_whatsapp', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a5.8 5.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg> },
        { key: 'social_arattai', icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fillRule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.44 1.223zM8.25 10.875a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25zM10.875 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875-1.125a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clipRule="evenodd" /></svg> },
    ];

    const availableSocials = socialLinks.filter(s => settings.general && settings.general[s.key]);

    return (
        <footer style={{ backgroundColor: bgColor, color: textColor }} className="mt-auto border-t border-gray-100">
            {showNewsletter && (
                <div className="border-b" style={{ borderColor: `${textColor}22` }}>
                    <div className="container mx-auto px-4 md:px-8 py-12 md:py-16">
                        <div className="max-w-xl mx-auto text-center">
                            <h3 className="text-2xl md:text-3xl font-black mb-4 tracking-tight">Subscribe to our newsletter</h3>
                            <p className="mb-6 opacity-80">Get the latest updates, drops, and promotions straight to your inbox.</p>
                            {submitted ? (
                                <p className="text-xl font-bold text-green-600">🎉 You're in! Thanks for subscribing.</p>
                            ) : (
                                <div>
                                    <form className="flex w-full gap-2" onSubmit={handleSubscribe}>
                                        <input 
                                            type="email" 
                                            value={email}
                                            onChange={e => setEmail(e.target.value)}
                                            placeholder="Enter your email" 
                                            className="flex-1 rounded-xl border-gray-300 focus:ring-black focus:border-black py-3 px-4 text-gray-900"
                                            required
                                        />
                                        <button type="submit" disabled={loading} className="bg-black text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition-colors disabled:opacity-60">
                                            {loading ? '...' : 'Subscribe'}
                                        </button>
                                    </form>
                                    {error && <p className="text-red-500 text-sm mt-3">{error}</p>}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            <div className="container mx-auto px-4 md:px-8 py-12 md:py-16">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">
                    {structure.map((col: any) => (
                        <div key={col.id}>
                            <h4 className="font-bold text-lg mb-6 uppercase tracking-wider">{col.title}</h4>
                            <ul className="space-y-4">
                                {col.items.map((item: any) => {
                                    if (item.type === 'link') {
                                        return (
                                            <li key={item.id}>
                                                <Link href={item.url} className="opacity-80 hover:opacity-100 hover:underline transition-all">
                                                    {item.label}
                                                </Link>
                                            </li>
                                        );
                                    } else if (item.type === 'phone') {
                                        return (
                                            <li key={item.id}>
                                                <a href={`tel:${item.url}`} className="opacity-80 hover:opacity-100 hover:underline transition-all">
                                                    {item.label}
                                                </a>
                                            </li>
                                        );
                                    } else if (item.type === 'email') {
                                        return (
                                            <li key={item.id}>
                                                <a href={`mailto:${item.url}`} className="opacity-80 hover:opacity-100 hover:underline transition-all">
                                                    {item.label}
                                                </a>
                                            </li>
                                        );
                                    } else if (item.type === 'legal') {
                                        return (
                                            <li key={item.id}>
                                                <Link href={`/policy/${item.legal_slug}`} className="opacity-80 hover:opacity-100 hover:underline transition-all">
                                                    {item.label || item.legal_slug.replace(/-/g, ' ')}
                                                </Link>
                                            </li>
                                        );
                                    } else if (item.type === 'text') {
                                        return (
                                            <li key={item.id} className="opacity-80 whitespace-pre-line leading-relaxed">
                                                {item.content}
                                            </li>
                                        );
                                    } else if (item.type === 'image_text') {
                                        const imgElement = item.image_url ? (
                                            <img src={item.image_url} alt="Footer Image" className="max-w-full h-auto rounded mb-3 max-h-16 object-contain" />
                                        ) : null;
                                        
                                        const txtElement = item.content ? (
                                            <div className="whitespace-pre-line leading-relaxed">
                                                {item.content}
                                            </div>
                                        ) : null;

                                        return (
                                            <li key={item.id} className="opacity-80">
                                                {imgElement && (
                                                    item.image_link ? (
                                                        <a href={item.image_link} target="_blank" rel="noreferrer" className="block hover:opacity-90 transition-opacity">
                                                            {imgElement}
                                                        </a>
                                                    ) : imgElement
                                                )}
                                                {txtElement && (
                                                    item.text_link ? (
                                                        <a href={item.text_link} target="_blank" rel="noreferrer" className="block hover:underline hover:opacity-100 transition-all">
                                                            {txtElement}
                                                        </a>
                                                    ) : txtElement
                                                )}
                                            </li>
                                        );
                                    } else if (item.type === 'payment_badges') {
                                        return (
                                            <li key={item.id} className="mt-2">
                                                {item.label && <div className="opacity-80 mb-2">{item.label}</div>}
                                                <div className="flex gap-3 items-center flex-wrap text-gray-700 mt-2">
                                                    {item.show_card && (
                                                        <div className="flex flex-col items-center gap-1">
                                                            <div className="w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm" style={{ borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }} title="Card Payment">
                                                                <CreditCard size={20} strokeWidth={1.5} />
                                                            </div>
                                                            <span className="text-[9px] font-bold tracking-widest uppercase opacity-70">Card</span>
                                                        </div>
                                                    )}
                                                    {item.show_wallet && (
                                                        <div className="flex flex-col items-center gap-1">
                                                            <div className="w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm" style={{ borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }} title="Wallet">
                                                                <Wallet size={20} strokeWidth={1.5} />
                                                            </div>
                                                            <span className="text-[9px] font-bold tracking-widest uppercase opacity-70">Wallet</span>
                                                        </div>
                                                    )}
                                                    {item.show_upi && (
                                                        <div className="flex flex-col items-center gap-1">
                                                            <div className="w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm" style={{ borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }} title="UPI">
                                                                <Smartphone size={20} strokeWidth={1.5} />
                                                            </div>
                                                            <span className="text-[9px] font-bold tracking-widest uppercase opacity-70">UPI</span>
                                                        </div>
                                                    )}
                                                    {item.show_netbanking && (
                                                        <div className="flex flex-col items-center gap-1">
                                                            <div className="w-12 h-8 border rounded-md flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity shadow-sm" style={{ borderColor: `${textColor}33`, backgroundColor: `${textColor}05` }} title="Netbanking">
                                                                <Landmark size={20} strokeWidth={1.5} />
                                                            </div>
                                                            <span className="text-[9px] font-bold tracking-widest uppercase opacity-70">NB</span>
                                                        </div>
                                                    )}
                                                </div>
                                            </li>
                                        );
                                    } else if (item.type === 'app_links') {
                                        return (
                                            <li key={item.id} className="mt-2 flex flex-col gap-2">
                                                {item.ios_url && (
                                                    <a href={item.ios_url} target="_blank" rel="noreferrer" className="inline-block transition-opacity hover:opacity-80">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="Download on the App Store" className="h-10 w-auto" />
                                                    </a>
                                                )}
                                                {item.android_url && (
                                                    <a href={item.android_url} target="_blank" rel="noreferrer" className="inline-block transition-opacity hover:opacity-80">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" className="h-10 w-auto" />
                                                    </a>
                                                )}
                                            </li>
                                        );
                                    } else if (item.type === 'store_map') {
                                        return (
                                            <li key={item.id} className="mt-2">
                                                {item.label && <div className="opacity-80 mb-1">{item.label}</div>}
                                                {item.content && <p className="opacity-80 whitespace-pre-line leading-relaxed mb-2">{item.content}</p>}
                                                {item.url && (
                                                    <a href={item.url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1.5 hover:underline opacity-80 hover:opacity-100 transition-all">
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        Get Directions
                                                    </a>
                                                )}
                                            </li>
                                        );
                                    } else if (item.type === 'business_hours') {
                                        return (
                                            <li key={item.id} className="mt-2">
                                                {item.label && <div className="opacity-80 mb-2">{item.label}</div>}
                                                {item.content && (
                                                    <div className="opacity-80 whitespace-pre-line leading-relaxed">
                                                        {item.content}
                                                    </div>
                                                )}
                                            </li>
                                        );
                                    }
                                    return null;
                                })}
                            </ul>
                        </div>
                    ))}
                </div>
            </div>

            <div className="border-t" style={{ borderColor: `${textColor}22` }}>
                <div className="container mx-auto px-4 md:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p className="text-sm opacity-80 text-center md:text-left">
                        {settings.footer_bottom_text}
                    </p>
                    
                    {showSocial && (
                        <div className="flex space-x-4 mb-4 md:mb-0">
                            {availableSocials.map(social => (
                                <a key={social.key} href={settings.general[social.key]} target="_blank" rel="noreferrer" className="opacity-80 hover:opacity-100 transition-opacity">
                                    {social.icon}
                                </a>
                            ))}
                            {settings.general?.social_custom_links?.map((customLink: any, idx: number) => (
                                customLink.url && (
                                    <a key={`custom-${idx}`} href={customLink.url} target="_blank" rel="noreferrer" className="opacity-80 hover:opacity-100 transition-opacity flex items-center group relative" title={customLink.label}>
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        <span className="sr-only">{customLink.label}</span>
                                    </a>
                                )
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </footer>
    );
}
