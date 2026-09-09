import React, { useState, useRef, KeyboardEvent } from 'react';
import { useAuthStore } from '@/store/auth';
import api from '@/lib/api';
import { router, usePage } from '@inertiajs/react';
import { User, Mail, Lock, Phone } from 'lucide-react';
import CountryCodePicker from './CountryCodePicker';

interface RegisterFormProps {
    settings: any;
    onSuccess?: () => void;
    onSwitchToLogin?: () => void;
    isModal?: boolean;
}

export default function RegisterForm({ settings, onSuccess, onSwitchToLogin, isModal }: RegisterFormProps) {
    
    const login = useAuthStore((state) => state.login);

    // Parse structured settings
    const parse = (val: any) => {
        if (typeof val === 'string') {
            try { return JSON.parse(val); } catch { return {}; }
        }
        return val || {};
    };

    const rawFields = parse(settings.auth_fields);
    
    const { auth } = usePage().props as any;
    const socialProviders: string[] = auth?.social_providers || [];
    
    const authHeader = parse(settings.auth_header);
    const authFooter = parse(settings.auth_footer);
    const authAppearance = parse(settings.auth_appearance);
    
    const parseBool = (val: any, defaultVal: boolean) => {
        if (val === undefined || val === null) return defaultVal;
        if (typeof val === 'boolean') return val;
        if (typeof val === 'string') return val === '1' || val.toLowerCase() === 'true' || val.toLowerCase() === 'on';
        if (typeof val === 'number') return val === 1;
        return !!val;
    };

    // Normalize fields for frontend
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
        name: normalize(rawFields.name, true, true),
        email: normalize(rawFields.email, true, true),
        phone: normalize(rawFields.phone, false, false),
    };

    const isPhoneVisible = authFields.phone.visible || authFields.phone.auth_type !== 'data_entry';
    const isNameVisible = authFields.name.visible;
    const isEmailVisible = authFields.email.visible;

    const [form, setForm] = useState({ 
        name: '', 
        email: '', 
        phone: '', 
        password: '', 
        password_confirmation: '',
        has_consented_to_terms: false,
        has_consented_to_marketing: false,
    });
    const [countryCode, setCountryCode] = useState('+91');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    
    // OTP State
    const [isOtpSent, setIsOtpSent] = useState(false);
    const [otpArray, setOtpArray] = useState<string[]>(Array(6).fill(''));
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);
    const [finalSubmitPhone, setFinalSubmitPhone] = useState('');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            let finalPhone = form.phone.trim();
            if (finalPhone) {
                // Strip leading plus
                if (finalPhone.startsWith('+')) {
                    finalPhone = finalPhone.substring(1);
                }
                const rawCode = countryCode.replace('+', '');
                if (finalPhone.startsWith(rawCode)) {
                    finalPhone = finalPhone.substring(rawCode.length);
                }
                finalPhone = `${countryCode}${finalPhone}`;
            }

            const isOtpRequired = authFields.phone.auth_type === 'whatsapp_otp' || authFields.phone.auth_type === 'sms_otp';

            if (isOtpRequired && finalPhone) {
                const res = await api.post('/api/register/send-otp', { ...form, phone: finalPhone });
                if (res.data.requires_otp) {
                    setIsOtpSent(true);
                    setFinalSubmitPhone(finalPhone);
                }
            } else {
                const res = await api.post('/api/register', { ...form, phone: finalPhone });
                login(res.data.access_token, res.data.user);
                if (onSuccess) onSuccess();
                else router.visit('/');
            }
        } catch (err: any) {
            if (err.response?.data?.errors) {
                const firstError = Object.values(err.response.data.errors)[0] as string[];
                setError(firstError[0]);
            } else {
                setError(err.response?.data?.error || err.response?.data?.message || 'Something went wrong');
            }
        } finally {
            setLoading(false);
        }
    };

    const handleVerifyOtp = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        
        const finalOtp = otpArray.join('');

        try {
            const res = await api.post('/api/register/verify-otp', {
                phone: finalSubmitPhone,
                otp: finalOtp,
            });
            login(res.data.access_token, res.data.user);
            if (onSuccess) onSuccess();
            else router.visit('/');
        } catch (err: any) {
            setError(err.response?.data?.message || err.response?.data?.error || 'Invalid OTP. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleOtpChange = (index: number, value: string) => {
        // Only allow numbers
        const val = value.replace(/[^0-9]/g, '');
        if (!val && value !== '') return;

        const newOtp = [...otpArray];
        // Handle pasting multiple numbers
        if (val.length > 1) {
            for (let i = 0; i < val.length && index + i < 6; i++) {
                newOtp[index + i] = val[i];
            }
            setOtpArray(newOtp);
            // Focus the next empty input or the last input
            const nextEmptyIndex = newOtp.findIndex(v => v === '');
            const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5;
            inputRefs.current[focusIndex]?.focus();
            return;
        }

        newOtp[index] = val;
        setOtpArray(newOtp);

        // Auto focus next input
        if (val !== '' && index < 5) {
            inputRefs.current[index + 1]?.focus();
        }
    };

    const handleOtpKeyDown = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace') {
            if (otpArray[index] === '' && index > 0) {
                // Focus previous input if current is empty
                const newOtp = [...otpArray];
                newOtp[index - 1] = '';
                setOtpArray(newOtp);
                inputRefs.current[index - 1]?.focus();
            } else if (otpArray[index] !== '') {
                // Just clear current input
                const newOtp = [...otpArray];
                newOtp[index] = '';
                setOtpArray(newOtp);
            }
        }
    };

    return (
        <div className="w-full">
            {/* Dynamic Header Section */}
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
                                {authHeader.text || 'Create Account'}
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
                        <p className="text-sm font-medium text-red-800 leading-tight">{error}</p>
                    </div>
                )}

                {isOtpSent ? (
                    <form onSubmit={handleVerifyOtp} className="space-y-6">
                        <div className="text-center mb-6">
                            <h3 className="text-lg font-bold text-gray-900 mb-2">Verify your phone</h3>
                            <p className="text-sm text-gray-500">We've sent a 6-digit verification code to <span className="font-semibold text-gray-900">{finalSubmitPhone}</span>.</p>
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
                            className="w-full py-3.5 px-4 bg-black text-white rounded-xl font-bold text-sm tracking-wide hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:shadow-lg hover:shadow-black/20"
                            style={{ backgroundColor: authAppearance.button_color || 'var(--color-primary)' }}
                        >
                            {loading ? (
                                <span className="flex items-center justify-center gap-2">
                                    <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    Verifying...
                                </span>
                            ) : 'Verify & Register'}
                        </button>
                        
                        <div className="text-center">
                            <button type="button" onClick={() => setIsOtpSent(false)} className="text-xs font-semibold text-gray-500 hover:text-black">
                                Change phone number
                            </button>
                        </div>
                    </form>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                    {isNameVisible && (
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Full Name</label>
                            <div className="relative group">
                                <User className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="text" 
                                    name="name"
                                    autoComplete="name"
                                    required={authFields.name.required}
                                    placeholder="Enter your full name"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={form.name}
                                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                                />
                            </div>
                        </div>
                    )}

                    {isEmailVisible && (
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Email Address</label>
                            <div className="relative group">
                                <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="email" 
                                    name="email"
                                    autoComplete="email"
                                    required={authFields.email.required}
                                    placeholder="name@example.com"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={form.email}
                                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                                />
                            </div>
                        </div>
                    )}

                    {isPhoneVisible && (
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">
                                Phone 
                                {authFields.phone.auth_type === 'sms_otp' && <span className="text-[10px] text-gray-300 ml-2">(SMS OTP)</span>}
                                {authFields.phone.auth_type === 'whatsapp_otp' && <span className="text-[10px] text-gray-300 ml-2">(WhatsApp OTP)</span>}
                            </label>
                            <div className="flex bg-gray-50/50 border border-gray-200 rounded-xl focus-within:border-black focus-within:bg-white transition-all group">
                                <CountryCodePicker value={countryCode} onChange={setCountryCode} />
                                <div className="relative flex-1">
                                    <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                    <input
                                        type="tel"
                                        name="phone"
                                        autoComplete="tel"
                                        required={authFields.phone.required}
                                        placeholder="555 000 0000"
                                        className="w-full bg-transparent pl-9 pr-4 py-3 text-sm font-medium text-gray-900 focus:outline-none"
                                        value={form.phone}
                                        onChange={(e) => setForm({ ...form, phone: e.target.value })}
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Password</label>
                            <div className="relative group">
                                <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="password" required
                                    name="password"
                                    autoComplete="new-password"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={form.password}
                                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                                />
                            </div>
                        </div>
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Confirm</label>
                            <div className="relative group">
                                <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="password" required
                                    name="password_confirmation"
                                    autoComplete="new-password"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={form.password_confirmation}
                                    onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="pt-2 space-y-3">
                        <label className="flex items-start gap-3 cursor-pointer group">
                            <div className="relative flex items-start mt-0.5">
                                <input 
                                    type="checkbox" 
                                    required 
                                    className="peer w-4 h-4 border-2 border-gray-300 rounded appearance-none checked:bg-black checked:border-black transition-all cursor-pointer"
                                    checked={form.has_consented_to_terms}
                                    onChange={(e) => setForm({ ...form, has_consented_to_terms: e.target.checked })}
                                />
                                <svg className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="3"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div className="text-sm text-gray-600 group-hover:text-black transition-colors">
                                I agree to the <a href="/policy/terms-of-service" className="font-bold underline decoration-gray-300 underline-offset-2 hover:decoration-black transition-colors">Terms of Service</a> and <a href="/policy/privacy-policy" className="font-bold underline decoration-gray-300 underline-offset-2 hover:decoration-black transition-colors">Privacy Policy</a> <span className="text-red-500">*</span>
                            </div>
                        </label>

                        <label className="flex items-start gap-3 cursor-pointer group">
                            <div className="relative flex items-start mt-0.5">
                                <input 
                                    type="checkbox" 
                                    className="peer w-4 h-4 border-2 border-gray-300 rounded appearance-none checked:bg-black checked:border-black transition-all cursor-pointer"
                                    checked={form.has_consented_to_marketing}
                                    onChange={(e) => setForm({ ...form, has_consented_to_marketing: e.target.checked })}
                                />
                                <svg className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="3"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div className="text-sm text-gray-600 group-hover:text-black transition-colors">
                                I consent to receiving marketing emails and exclusive offers. (Optional)
                            </div>
                        </label>
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-2"
                    >
                        {loading ? <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <span>Create account</span>}
                    </button>
                </form>
                )}

                {/* Social Login Buttons */}
                {socialProviders.length > 0 && (
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

                <div className="mt-8 pt-6 border-t border-gray-50 text-center">
                    <p className="text-xs text-gray-400 font-bold uppercase tracking-widest">
                        Already have an account? 
                        <button 
                            type="button"
                            onClick={() => onSwitchToLogin ? onSwitchToLogin() : router.visit('/login')}
                            className="text-black font-black ml-2 hover:underline underline-offset-4 decoration-2"
                        >
                            Sign in
                        </button>
                    </p>
                </div>
            </div>

            {/* Dynamic Footer Section */}
            {!isModal && (
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
