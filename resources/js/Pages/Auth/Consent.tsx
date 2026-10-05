import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function ConsentPage() {
    const { settings } = usePage().props as any;
    
    const { data, setData, post, processing, errors } = useForm({
        terms: false,
        marketing: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/consent');
    };

    return (
        <div className="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <Head title="Action Required - Terms of Service" />
            
            <div className="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-sm border border-gray-100">
                <div>
                    <h2 className="mt-2 text-center text-3xl font-extrabold text-gray-900">
                        Action Required
                    </h2>
                    <p className="mt-4 text-center text-sm text-gray-600">
                        To continue using our services, we require your consent to our updated policies.
                    </p>
                </div>
                
                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    <div className="space-y-4">
                        
                        <label className="flex items-start gap-3 cursor-pointer group">
                            <div className="flex h-6 items-center">
                                <input
                                    type="checkbox"
                                    name="terms"
                                    required
                                    checked={data.terms}
                                    onChange={(e) => setData('terms', e.target.checked)}
                                    className="h-5 w-5 rounded border-gray-300 text-black focus:ring-black cursor-pointer"
                                />
                            </div>
                            <div className="text-sm">
                                <p className="text-gray-700 font-medium">
                                    I agree to the <a href="/policy/terms-of-service" target="_blank" className="font-semibold underline">Terms of Service</a> and <a href="/policy/privacy-policy" target="_blank" className="font-semibold underline">Privacy Policy</a> <span className="text-red-500">*</span>
                                </p>
                            </div>
                        </label>
                        
                        {errors.terms && (
                            <p className="text-sm text-red-600 pl-8">{errors.terms}</p>
                        )}

                        <label className="flex items-start gap-3 cursor-pointer group">
                            <div className="flex h-6 items-center">
                                <input
                                    type="checkbox"
                                    name="marketing"
                                    checked={data.marketing}
                                    onChange={(e) => setData('marketing', e.target.checked)}
                                    className="h-5 w-5 rounded border-gray-300 text-black focus:ring-black cursor-pointer"
                                />
                            </div>
                            <div className="text-sm">
                                <p className="text-gray-600">
                                    I consent to receiving marketing emails and exclusive offers. (Optional)
                                </p>
                            </div>
                        </label>
                        
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={processing || !data.terms}
                            className="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Continue'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
