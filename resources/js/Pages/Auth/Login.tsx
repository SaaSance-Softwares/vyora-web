
import { useUIStore } from '@/store/ui';
import { router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import LoginForm from '@/components/auth/LoginForm';

export default function LoginPage() {
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
            openAuthModal('login');
        }
    }, [isModalMode, router, openAuthModal]);

    if (isModalMode) return null;

    return (
        <div className="min-h-screen">
            <div className="max-w-[1280px] mx-auto px-4 py-16 flex items-center justify-center">
                <div className="max-w-[420px] w-full">
                    <LoginForm settings={settings} />
                </div>
            </div>
        </div>
    );
}
