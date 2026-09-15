import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import api from '@/lib/api';
import { Lock } from 'lucide-react';

export default function ResetPassword({ token, email }: { token: string, email: string }) {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        
        try {
            await api.post('/api/reset-password', {
                token,
                email,
                password,
                password_confirmation: passwordConfirmation
            });
            setSuccess(true);
            setTimeout(() => {
                router.visit('/login');
            }, 3000);
        } catch (err: any) {
            setError(err.response?.data?.message || err.response?.data?.errors?.email?.[0] || err.response?.data?.errors?.password?.[0] || 'Failed to reset password.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
            <Head title="Reset Password" />
            
            <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900" style={{ fontFamily: 'var(--font-heading)' }}>
                        Reset Password
                    </h1>
                </div>

                {success ? (
                    <div className="text-center space-y-4">
                        <div className="mx-auto w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mb-4">
                            <Lock className="w-6 h-6 text-green-600" />
                        </div>
                        <h3 className="text-lg font-bold text-gray-900">Password Reset Successful</h3>
                        <p className="text-sm text-gray-500">Your password has been successfully reset. Redirecting to login...</p>
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {error && (
                            <div className="p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                                <div className="w-1.5 h-1.5 rounded-full bg-red-500 mt-1.5 shrink-0"></div>
                                <p className="text-sm font-medium text-red-800 leading-tight">{error}</p>
                            </div>
                        )}

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Email Address</label>
                            <input
                                type="email"
                                value={email}
                                disabled
                                className="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-500 cursor-not-allowed"
                            />
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">New Password</label>
                            <div className="relative group">
                                <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="password" required
                                    name="password"
                                    placeholder="••••••••"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    minLength={8}
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold uppercase tracking-widest text-gray-400 ml-1">Confirm New Password</label>
                            <div className="relative group">
                                <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-black transition-colors" />
                                <input
                                    type="password" required
                                    name="password_confirmation"
                                    placeholder="••••••••"
                                    className="w-full bg-gray-50/50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm font-medium text-gray-900 focus:border-black focus:bg-white transition-all outline-none"
                                    value={passwordConfirmation}
                                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                                    minLength={8}
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={loading || password.length < 8 || password !== passwordConfirmation}
                            className="w-full bg-black text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] hover:shadow-lg hover:shadow-black/10 disabled:opacity-50 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-4"
                        >
                            {loading ? <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <span>Reset Password</span>}
                        </button>
                    </form>
                )}
            </div>
        </div>
    );
}
