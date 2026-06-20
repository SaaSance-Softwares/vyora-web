
import { useUIStore } from '@/store/ui';
import { router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import RegisterForm from '@/components/auth/RegisterForm';

export default function RegisterPage() {
    const { settings: settings } = usePage().props as any;
    
    const { openAuthModal } = useUIStore();

    // Parse logic for settings
    const parse = (val: any) => {
        if (typeof val === 'string') {
            try { return JSON.parse(val); } catch { return {}; }
        }
        return val || {};
    };

    const authAppearance = parse(settings.auth_appearance);
    const isModalMode = authAppearance.ux_mode === 'modal';

    useEffect(() => {
        if (isModalMode) {
            router.visit('/');
            openAuthModal('register');
        }
    }, [isModalMode, router, openAuthModal]);

    if (isModalMode) return null;

    return (
        <div className="min-h-screen">
            <div className="max-w-[1280px] mx-auto px-4 py-16 flex items-center justify-center">
                <div className="max-w-[460px] w-full">
                    <RegisterForm settings={settings} />
                </div>
            </div>
        </div>
    );
}
