import { useState, useRef, KeyboardEvent } from 'react';
import { useAuthStore } from '@/store/auth';
import api from '@/lib/api';
import { router, usePage, Link } from '@inertiajs/react';
import { Mail, Lock, Phone } from 'lucide-react';
import CountryCodePicker from './CountryCodePicker';

interface LoginFormProps {
    settings: any;
    onSuccess?: () => void;
    onSwitchToRegister?: () => void;
    isModal?: boolean;
}

export default function LoginForm({ settings, onSuccess, onSwitchToRegister, isModal }: LoginFormProps) {
    
    const login = useAuthStore((state) => state.login);

    const [identifier, setIdentifier] = useState('');
    const [password, setPassword] = useState('');
    const [countryCode, setCountryCode] = useState('+91');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    
    // Auth fields parsing
    const parse = (val: any) => {
        if (typeof val === 'string') {
            try { return JSON.parse(val); } catch { return {}; }
        }
        return val || {};
    };

    const authHeader = parse(settings.auth_header);
    const authFooter = parse(settings.auth_footer);
    const authAppearance = parse(settings.auth_appearance);
    
    const { auth } = usePage().props as any;
    const socialProviders: string[] = auth?.social_providers || [];

    const rawFields = parse(settings.auth_fields);
    
    const parseBool = (val: any, defaultVal: boolean) => {
        if (val === undefined || val === null) return defaultVal;
        if (typeof val === 'boolean') return val;
        if (typeof val === 'string') return val === '1' || val.toLowerCase() === 'true' || val.toLowerCase() === 'on';
        if (typeof val === 'number') return val === 1;
        return !!val;
    };

    const normalize = (field: any, defaultVisible = true, defaultRequired = false) => {
        if (typeof field === 'object' && field !== null) {
            return {
                ...field,
                visible: parseBool(field.visible, defaultVisible),
                required: parseBool(field.required, defaultRequired),
                auth_type: field.auth_type || 'data_entry'
            };
        }
        if (field === undefined) return { visible: defaultVisible, required: defaultRequired, auth_type: 'data_entry' };
        return { visible: parseBool(field, defaultVisible), required: defaultRequired, auth_type: 'data_entry' };
    };

    const authFields = {
        email: normalize(rawFields.email, true, true),
        phone: normalize(rawFields.phone, false, false),
    };

    const isEmailVisible = authFields.email.visible;
    const isPhoneVisible = authFields.phone.visible || authFields.phone.auth_type !== 'data_entry';
    const isPhoneOtp = authFields.phone.auth_type === 'whatsapp_otp' || authFields.phone.auth_type === 'sms_otp';

    const defaultMethod = isPhoneVisible ? 'phone' : 'email';
    const [loginMethod, setLoginMethod] = useState<'email' | 'phone'>(defaultMethod);
    const [phoneLoginType, setPhoneLoginType] = useState<'otp' | 'password'>(isPhoneOtp ? 'otp' : 'password');
    const [isOtpSent, setIsOtpSent] = useState(false);
    const [otpArray, setOtpArray] = useState<string[]>(['', '', '', '', '', '']);
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

    let fieldLabel = 'Email Address';
    let fieldPlaceholder = 'name@example.com';
    let FieldIcon = Mail;
    
    if (loginMethod === 'phone') {
        fieldLabel = 'Phone Number';
        fieldPlaceholder = '555 000 0000';
        FieldIcon = Phone;
    }

    const formatPhone = () => {
        let finalIdentifier = identifier.trim();
        if (finalIdentifier.startsWith('+')) {
            finalIdentifier = finalIdentifier.substring(1);
        }
        const rawCode = countryCode.replace('+', '');
        if (finalIdentifier.startsWith(rawCode)) {
            finalIdentifier = finalIdentifier.substring(rawCode.length);
        }
        return `${countryCode}${finalIdentifier}`;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const finalIdentifier = loginMethod === 'phone' ? formatPhone() : identifier.trim();

            if (loginMethod === 'phone' && phoneLoginType === 'otp') {
                // Send OTP logic
                await api.post('/api/login/send-otp', { phone: finalIdentifier });
                setIsOtpSent(true);
            } else {
                // Password logic
                const res = await api.post('/api/login', { identifier: finalIdentifier, password });
                login(res.data.access_token, res.data.user);
                if (onSuccess) onSuccess();
                else router.visit('/');
            }
        } catch (err: any) {
            setError(err.response?.data?.message || err.response?.data?.error || err.response?.data?.errors?.identifier?.[0] || err.response?.data?.errors?.phone?.[0] || 'Authentication failed');
        } finally {
            setLoading(false);
        }
    };

    const handleVerifyOtp = async (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        setLoading(true);
        setError('');

        const finalOtp = otpArray.join('');
        const finalSubmitPhone = formatPhone();

        try {
            const res = await api.post('/api/login/verify-otp', {
                phone: finalSubmitPhone,
                otp: finalOtp,
            });
            login(res.data.access_token, res.data.user);
            if (onSuccess) onSuccess();
            else router.visit('/');
        } catch (err: any) {
            if (err.response?.data?.errors) {
                const firstError = Object.values(err.response.data.errors)[0] as string[];
                setError(firstError[0]);
            } else {
                setError(err.response?.data?.error || err.response?.data?.message || 'Verification failed');
            }
        } finally {
            setLoading(false);
        }
    };

    const handleOtpChange = (index: number, value: string) => {
        const val = value.replace(/[^0-9]/g, '');
        if (!val && value !== '') return;

        const newOtp = [...otpArray];
        if (val.length > 1) {
            for (let i = 0; i < val.length && index + i < 6; i++) {
                newOtp[index + i] = val[i];
            }
            setOtpArray(newOtp);
            const nextEmptyIndex = newOtp.findIndex(v => v === '');
            const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
            inputRefs.current[focusIndex]?.focus();
            return;
        }

        newOtp[index] = val;
        setOtpArray(newOtp);

        if (val !== '' && index < 5) {
            inputRefs.current[index + 1]?.focus();
        }
    };

    const handleOtpKeyDown = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace') {
            if (otpArray[index] === '' && index > 0) {
                const newOtp = [...otpArray];
                newOtp[index - 1] = '';
                setOtpArray(newOtp);
                inputRefs.current[index - 1]?.focus();
            } else if (otpArray[index] !== '') {
                const newOtp = [...otpArray];
                newOtp[index] = '';
                setOtpArray(newOtp);
            }
        }
    };

    return (
        <div className="w-full">
            <div className={`text-center mb-8 space-y-3 ${isModal ? 'pt-2' : ''}`}>
                {(authHeader.order || ['image', 'text']).map((item: string) => (
                    <div key={item}>
                        {item === 'image' && authHeader.image && (
                            <img 
                                src={authHeader.image} 
                                alt="Logo" 
                                className="mx-auto h-auto object-contain" 
                                style={{ width: authHeader.image_width ? `${authHeader.image_width}px` : '100px' }}
                            />
                        )}
                        {item === 'text' && (
                            <h1 className="text-3xl font-bold tracking-tight text-gray-900" style={{ fontFamily: 'var(--font-heading)' }}>
                                {authHeader.text || 'Sign In'}
                            </h1>
                        )}
                    </div>
                ))}
            </div>

            <div className={!isModal ? "bg-white p-8 rounded-2xl shadow-sm border border-gray-200" : ""}
                 style={!isModal ? { 
                     borderRadius: authAppearance.border_radius ? `${authAppearance.border_radius}px` : undefined,
                     borderColor: authAppearance.border_color
                 } : {}}>
                
                {error && (
                    <div className="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                        <div className="w-1.5 h-1.5 rounded-full bg-red-500 mt-1.5 shrink-0"></div>
                        <p className="text-sm font-medium text-red-800 leading-tight">
                            {error}
                            {error.toLowerCase().includes('no account found') && (
                                <span className="block mt-2">
                                    <button 
                                        type="button"
                                        onClick={() => onSwitchToRegister ? onSwitchToRegister() : router.visit('/register')}
                                        className="font-bold text-emerald-600 hover:text-emerald-700 underline decoration-2 underline-offset-2"
                                    >
                                        Create account
                                    </button>
                                </span>
                            )}
                        </p>
                    </div>
                )}

                {isOtpSent ? (
                    <form onSubmit={handleVerifyOtp} className="space-y-6">
                        <div className="text-center mb-6">
                            <h3 className="text-lg font-bold text-gray-900 mb-2">Verify your phone</h3>
                            <p className="text-sm text-gray-500">We've sent a 6-digit verification code to <span className="font-semibold text-gray-900">{formatPhone()}</span>.</p>
                        </div>
                        
                        <div className="space-y-3">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1 block text-center">Enter 6-Digit Code</label>
                            <div className="flex items-center justify-center gap-3">
                                {otpArray.map((digit, index) => (
                                    <input
                                        key={index}
                                        ref={(el) => (inputRefs.current[index] = el)}
                                        type="text"
                                        inputMode="numeric"
                                        pattern="[0-9]*"
                                        maxLength={6}
                                        autoComplete="one-time-code"
                                        className="w-12 h-14 bg-gray-50/50 border border-gray-200 rounded-xl text-center text-xl font-bold text-gray-900 focus:border-black focus:bg-white transition-all outline-none shadow-sm"
                                        value={digit}
                                        onPaste={(e) => {
                                            e.preventDefault();
                                            const pastedData = e.clipboardData.getData('text/plain').replace(/[^0-9]/g, '').slice(0, 6);
                                            if (pastedData) {
                                                const newOtp = [...otpArray];
                                                for (let i = 0; i < pastedData.length; i++) {
                                                    newOtp[i] = pastedData[i];
                                                }
                                                setOtpArray(newOtp);
                                                const nextEmptyIndex = newOtp.findIndex(v => v === '');
                                                const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
                                                inputRefs.current[focusIndex]?.focus();
                                            }
                                        }}
                                        onChange={(e) => handleOtpChange(index, e.target.value)}
                                        onKeyDown={(e) => handleOtpKeyDown(index, e)}
                                    />
                                ))}
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={loading || otpArray.join('').length !== 6}
                            className="w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                        >
                            {loading ? <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <span>Verify & Login</span>}
                        </button>
                        
                        <div className="text-center">
                            <button
                                type="button"
                                onClick={() => {
                                    setIsOtpSent(false);
                                    setOtpArray(['', '', '', '', '', '']);
                                }}
                                className="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors"
                            >
                                ← Change Phone Number
                            </button>
                        </div>
                    </form>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {isEmailVisible && isPhoneVisible && (
                            <div className="flex p-1 bg-gray-100/80 rounded-xl mb-4 relative z-0">
                                <button
                                    type="button"
                                    onClick={() => { setLoginMethod('phone'); setIdentifier(''); }}
                                    className={`flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all ${loginMethod === 'phone' ? 'bg-white text-black shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}
                                >
                                    Phone
                                </button>
                                <button
                                    type="button"
                                    onClick={() => { setLoginMethod('email'); setIdentifier(''); }}
                                    className={`flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all ${loginMethod === 'email' ? 'bg-white text-black shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}
                                >
                                    Email
                                </button>
                            </div>
                        )}

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">{fieldLabel}</label>
                            {loginMethod === 'phone' ? (
                                <div className="flex bg-gray-50/50 border border-gray-200 rounded-xl focus-within:border-black focus-within:bg-white transition-all group">
                                    <CountryCodePicker value={countryCode} onChange={setCountryCode} />
                                    <div className="relative flex-1">
                                        <FieldIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                        <input
                                            type="tel" required
                                            name="phone"
                                            autoComplete="tel"
                                            placeholder={fieldPlaceholder}
                                            maxLength={10}
                                            className="w-full bg-transparent pl-9 pr-4 py-3 text-sm font-medium text-gray-900 focus:outline-none"
                                            value={identifier}
                                            onChange={(e) => {
                                                const val = e.target.value.replace(/[^0-9]/g, '');
                                                if (val.length <= 10) setIdentifier(val);
                                            }}
                                        />
                                    </div>
                                </div>
                            ) : (
                                <div className="relative group">
                                    <FieldIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                    <input
                                        type="email" required
                                        name="email"
                                        autoComplete="email"
                                        placeholder={fieldPlaceholder}
                                        className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                        value={identifier}
                                        onChange={(e) => setIdentifier(e.target.value)}
                                    />
                                </div>
                            )}
                        </div>

                        {((loginMethod === 'email') || (loginMethod === 'phone' && phoneLoginType === 'password')) && (
                            <div className="space-y-1.5">
                                <div className="flex items-center justify-between ml-1">
                                    <label className="text-xs font-bold uppercase tracking-widest text-gray-400">Password</label>
                                    <button type="button" className="text-[10px] font-black uppercase tracking-widest text-gray-300 hover:text-black transition-colors">Forgot?</button>
                                </div>
                                <div className="relative group">
                                    <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                    <input
                                        type="password" required
                                        name="password"
                                        autoComplete="current-password"
                                        placeholder="••••••••"
                                        className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                    />
                                </div>
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-2"
                        >
                            {loading ? <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <span>{loginMethod === 'phone' && phoneLoginType === 'otp' ? `Get OTP on ${authFields.phone.auth_type === 'whatsapp_otp' ? 'WhatsApp' : 'SMS'}` : 'Sign in now'}</span>}
                        </button>
                        
                        {loginMethod === 'phone' && isPhoneOtp && (
                            <div className="text-center pt-2">
                                <button
                                    type="button"
                                    onClick={() => setPhoneLoginType(prev => prev === 'otp' ? 'password' : 'otp')}
                                    className="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-black transition-colors underline decoration-2 underline-offset-4"
                                >
                                    {phoneLoginType === 'otp' ? 'Login with Password instead' : 'Login with OTP instead'}
                                </button>
                            </div>
                        )}
                    </form>
                )}

                {/* Social Login Buttons */}
                {!isOtpSent && socialProviders.length > 0 && (
                    <div className="mt-8 space-y-4">
                        <div className="relative">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-gray-100"></div>
                            </div>
                            <div className="relative flex justify-center text-[10px] uppercase">
                                <span className="bg-white px-3 text-gray-300 font-black tracking-[0.2em] italic">Social Auth</span>
                            </div>
                        </div>
                        <div className="flex flex-wrap justify-center gap-3">
                            {socialProviders.includes('google') && (
                                <a href="/auth/google/redirect" className="w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors">
                                    <img src="https://www.svgrepo.com/show/303108/google-icon-logo.svg" className="w-3.5 h-3.5" />
                                    <span>Google</span>
                                </a>
                            )}
                            {socialProviders.includes('snapchat') && (
                                <a href="/auth/snapchat/redirect" className="w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors">
                                    <img src="https://upload.wikimedia.org/wikipedia/en/c/c4/Snapchat_logo.svg" className="w-4 h-4 object-contain" alt="Snapchat" />
                                    <span>Snapchat</span>
                                </a>
                            )}
                            {socialProviders.includes('apple') && (
                                <a href="/auth/apple/redirect" className="w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors">
                                    <img src="https://www.svgrepo.com/show/511330/apple-173.svg" className="w-3.5 h-3.5" />
                                    <span>Apple</span>
                                </a>
                            )}
                            {socialProviders.includes('facebook') && (
                                <a href="/auth/facebook/redirect" className="w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors">
                                    <img src="https://www.svgrepo.com/show/303114/facebook-3-logo.svg" className="w-3.5 h-3.5" />
                                    <span>Facebook</span>
                                </a>
                            )}
                            {socialProviders.includes('github') && (
                                <a href="/auth/github/redirect" className="w-[calc(50%-6px)] flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-colors">
                                    <img src="https://www.svgrepo.com/show/512317/github-142.svg" className="w-3.5 h-3.5" />
                                    <span>GitHub</span>
                                </a>
                            )}
                        </div>
                    </div>
                )}

                {!isOtpSent && (
                    <div className="mt-8 pt-6 border-t border-gray-50 text-center">
                        <p className="text-xs text-gray-400 font-bold uppercase tracking-widest">
                            New here? 
                            <button 
                                type="button"
                                onClick={() => onSwitchToRegister ? onSwitchToRegister() : router.visit('/register')}
                                className="text-black font-black ml-2 hover:underline underline-offset-4 decoration-2"
                            >
                                Create account
                            </button>
                        </p>
                    </div>
                )}
            </div>

            {/* Dynamic Footer Section */}
            {!isModal && !isOtpSent && (
                <div className="mt-10 text-center space-y-4">
                    {(authFooter.order || ['image', 'text']).map((item: string) => (
                        <div key={item}>
                            {item === 'image' && authFooter.image && (
                                <img 
                                    src={authFooter.image} 
                                    alt="Footer Logo" 
                                    className="mx-auto h-auto object-contain" 
                                    style={{ width: authFooter.image_width ? `${authFooter.image_width}px` : '80px' }}
                                />
                            )}
                            {item === 'text' && (
                                <p className="text-[10px] text-gray-300 font-black uppercase tracking-[0.2em] leading-relaxed italic">
                                    {authFooter.text}
                                </p>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
