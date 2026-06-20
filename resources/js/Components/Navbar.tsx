import { Link, router } from '@inertiajs/react';
import { ShoppingBag, User, LogOut, ChevronDown, Heart, Search, TrendingUp, Menu, X } from 'lucide-react';
import { useCartStore } from '@/store/cart';
import { useAuthStore } from '@/store/auth';
import { useState, useEffect } from 'react';
import { useUIStore } from '@/store/ui';
import { useWishlistStore } from '@/store/wishlist';
import api from '@/lib/api';

const SearchModalContent = ({ closeSearch }: { closeSearch: () => void }) => {
    const [query, setQuery] = useState('');
    const [suggestions, setSuggestions] = useState<string[]>([]);
    
    useEffect(() => {
        const fetchSuggestions = async () => {
            try {
                const res = await api.get(`/api/search-suggestions?q=${encodeURIComponent(query)}`);
                setSuggestions(res.data);
            } catch (e) {
                console.error(e);
            }
        };

        const timer = setTimeout(() => {
            fetchSuggestions();
        }, 300);

        return () => clearTimeout(timer);
    }, [query]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (query.trim()) {
            closeSearch();
            router.get('/search', { q: query.trim() });
        }
    };

    const handleSuggestionClick = (suggestion: string) => {
        closeSearch();
        router.get('/search', { q: suggestion });
    };

    return (
        <div className="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative overflow-hidden transition-all duration-300 flex flex-col">
            <form onSubmit={handleSubmit} className="p-4 px-6 flex items-center gap-4 border-b border-gray-100">
                <Search className="w-6 h-6 text-gray-400 shrink-0" />
                <input 
                    type="text" 
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    autoFocus
                    placeholder="Search for products, collections..." 
                    className="flex-1 text-xl font-medium focus:outline-none focus:ring-0 border-none p-0 bg-transparent"
                />
                <button type="button" onClick={closeSearch} className="text-gray-400 hover:text-gray-600 px-2 font-bold text-2xl">
                    &times;
                </button>
            </form>
            
            {suggestions.length > 0 && (
                <div className="max-h-96 overflow-y-auto">
                    <ul className="py-2">
                        {suggestions.map((suggestion, idx) => (
                            <li key={idx}>
                                <button
                                    type="button"
                                    onClick={() => handleSuggestionClick(suggestion)}
                                    className="w-full text-left px-6 py-3 hover:bg-gray-50 flex items-center gap-4 transition-colors"
                                >
                                    <TrendingUp className="w-5 h-5 text-gray-400" />
                                    <span className="font-medium text-gray-700 text-lg">{suggestion}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default function Navbar({ settings }: { settings?: any }) {
    const cart = useCartStore();
    const { user, logout } = useAuthStore();
    const { openAuthModal, isSearchOpen, openSearch, closeSearch } = useUIStore();
    const { items: wishlistItems } = useWishlistStore();
    const [mounted, setMounted] = useState(false);
    const [categories, setCategories] = useState<any[]>([]);
    const [hiddenMenuId, setHiddenMenuId] = useState<string | null>(null);
    const [forceOpenMenuId, setForceOpenMenuId] = useState<string | null>(null);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [activeMobileMenuId, setActiveMobileMenuId] = useState<string | null>(null);

    const isMegaMenu = settings?.navbar_style === 'mega_menu';
    const isCustom = settings?.navbar_style === 'custom';

    // Custom settings
    const alignment = settings?.nav_alignment || 'left';
    const position = settings?.nav_position || 'inline';
    let menuItems = [];
    try {
        if (settings?.menu_structure) {
            menuItems = JSON.parse(settings.menu_structure);
        }
    } catch (e) { }

    useEffect(() => {
        setMounted(true);
        if (isMegaMenu) {
            const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api';
            fetch(`${apiUrl}/categories`)
                .then(res => res.json())
                .then(data => setCategories(data))
                .catch(err => console.error("Failed to load categories", err));
        }
    }, [isMegaMenu]);

    if (!mounted) return <nav className="h-16 border-b" />;

    const storeName = settings?.store_name || "VYORA";
    const logoRelPath = settings?.main_logo;

    const authAppearance = typeof settings?.auth_appearance === 'string'
        ? JSON.parse(settings.auth_appearance)
        : (settings?.auth_appearance || {});
    const isModalMode = authAppearance.ux_mode === 'modal';

    // Alignment classes based on setting
    let alignmentClasses = "flex items-center space-x-6 ml-10 flex-1";
    if (alignment === 'center') alignmentClasses = "flex items-center justify-center space-x-6 flex-1";
    if (alignment === 'right') alignmentClasses = "flex items-center justify-end space-x-6 flex-1";

    const hoverStyle = settings?.nav_hover_style || 'none';

    // Generates isolated pseudo-classes for exact hover accuracy without bubbling!
    const getHoverClasses = (isChild = false) => {
        if (hoverStyle === 'none') return '';
        const bottomPos = isChild ? 'before:bottom-0' : 'before:bottom-2';

        if (hoverStyle === 'underline') {
            return `before:absolute ${bottomPos} before:left-0 before:w-full before:h-[2px] before:bg-black before:opacity-0 hover:before:opacity-100 before:transition-opacity before:duration-300`;
        }
        if (hoverStyle === 'left_to_right') {
            return `before:absolute ${bottomPos} before:left-0 before:w-0 before:h-[2px] before:bg-black hover:before:w-full before:transition-all before:duration-300 before:ease-out`;
        }
        return '';
    };

    const renderDynamicLink = (item: any, isChild = false, parentId: string | null = null) => {
        let href = '/shop';
        if (item.type === 'link') href = item.link || '/';
        if (item.type === 'category') href = `/shop?category=${item.ref_id}`;
        if (item.type === 'collection') href = `/shop?collection=${item.ref_id}`;
        if (item.type === 'page') href = `/${item.ref_id}`;

        const handleClick = () => {
            if (parentId) {
                setHiddenMenuId(parentId);
                setForceOpenMenuId(null);
            }
        };

        if (item.type === 'image') {
            return (
                <Link href={item.link || '/shop'} className="block w-full group/promo" onClick={handleClick}>
                    <div className="w-full flex flex-col items-center justify-center relative cursor-pointer">
                        {item.image_url ? (
                            <div className="relative inline-block w-full">
                                <img src={item.image_url} alt={item.label || 'Promo'} className="max-w-full h-auto object-contain mx-auto rounded-xl shadow-sm" />
                                {item.label && (
                                    <div className="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent z-10 pointer-events-none rounded-b-xl">
                                        <span className="text-sm font-bold text-white block text-center">{item.label}</span>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="w-full aspect-[4/5] bg-gray-50 rounded-xl border border-gray-100 flex flex-col items-center justify-center p-4 text-center overflow-hidden">
                                <span className="text-xs font-bold tracking-widest uppercase text-gray-400 block mb-1">Promo</span>
                                <span className="text-sm font-medium text-gray-900">{item.label}</span>
                                {item.label && (
                                    <div className="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent z-10 pointer-events-none">
                                        <span className="text-sm font-bold text-white block">{item.label}</span>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </Link>
            );
        }

        const className = isChild
            ? `text-sm text-gray-500 hover:text-black transition-colors py-1 w-fit relative block ${getHoverClasses(true)}`
            : `text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`;

        return (
            <Link href={href} className={className} onClick={handleClick}>
                {item.label}
            </Link>
        );
    };

    const DynamicNavItems = () => {
        if (!isCustom) return null;

        return (
            <>
                {menuItems.map((item: any) => (
                    <div key={item.id} className="group" onMouseLeave={() => { setHiddenMenuId(null); setForceOpenMenuId(null); }}>
                        {item.type === 'mega_menu' ? (
                            <>
                                {item.root_type && item.root_type !== '' ? (
                                    <Link href={
                                        item.root_type === 'url' ? (item.root_url || '#') :
                                            item.root_type === 'category' ? `/shop?category=${item.root_ref_id}` :
                                                item.root_type === 'collection' ? `/shop?collection=${item.root_ref_id}` :
                                                    item.root_type === 'page' ? `/${item.root_ref_id}` : '#'
                                    } className={`cursor-pointer text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`} onClick={() => setForceOpenMenuId(null)}>
                                        {item.label} 
                                        <span onClick={(e) => { e.preventDefault(); e.stopPropagation(); setForceOpenMenuId(forceOpenMenuId === item.id ? null : item.id); }} className="p-1 -mr-1">
                                            <ChevronDown className="w-3 h-3 text-gray-400 group-hover:text-black transition-colors" />
                                        </span>
                                    </Link>
                                ) : (
                                    <div className={`cursor-pointer text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5 relative ${getHoverClasses(false)}`}>
                                        {item.label} 
                                        <span onClick={(e) => { e.preventDefault(); e.stopPropagation(); setForceOpenMenuId(forceOpenMenuId === item.id ? null : item.id); }} className="p-1 -mr-1">
                                            <ChevronDown className="w-3 h-3 text-gray-400 group-hover:text-black transition-colors" />
                                        </span>
                                    </div>
                                )}
                                <div className={`absolute left-0 top-full mt-0 w-full bg-white border-b border-t shadow-xl transition-all duration-300 transform origin-top z-[100] ${(hiddenMenuId === item.id && forceOpenMenuId !== item.id) ? 'opacity-0 invisible pointer-events-none' : forceOpenMenuId === item.id ? 'opacity-100 visible translate-y-0' : 'opacity-0 invisible group-hover:opacity-100 group-hover:visible -translate-y-2 group-hover:translate-y-0'}`}>
                                    <div className="max-w-7xl mx-auto px-4 py-8">
                                        {/* tailwind scanner safelist: grid-cols-2 grid-cols-3 grid-cols-4 grid-cols-5 grid-cols-6 */}
                                        <div className={`grid gap-8 grid-cols-${item.columns || 4} items-start`}>
                                            {item.layout_columns?.map((col: any) => (
                                                <div key={col.id || Math.random()} className="flex flex-col gap-6">
                                                    {col.blocks?.map((block: any) => (
                                                        <div key={block.id || Math.random()}>
                                                            {block.type === 'image' ? (
                                                                renderDynamicLink(block, true, item.id)
                                                            ) : (
                                                                <div>
                                                                    {block.label && (
                                                                        <div className="border-b border-gray-100 pb-2 mb-3">
                                                                            {block.link ? (
                                                                                <Link href={block.link} className="text-sm font-black text-gray-900 uppercase tracking-wide hover:text-black" onClick={() => { setHiddenMenuId(item.id); setForceOpenMenuId(null); }}>
                                                                                    {block.label}
                                                                                </Link>
                                                                            ) : (
                                                                                <span className="text-sm font-black text-gray-900 uppercase tracking-wide">{block.label}</span>
                                                                            )}
                                                                        </div>
                                                                    )}
                                                                    {block.links && block.links.length > 0 && (
                                                                        <ul className="space-y-1.5">
                                                                            {block.links.map((link: any) => (
                                                                                <li key={link.id || Math.random()}>
                                                                                    {renderDynamicLink(link, true, item.id)}
                                                                                </li>
                                                                            ))}
                                                                        </ul>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </>
                        ) : (
                            renderDynamicLink(item)
                        )}
                    </div>
                ))}
            </>
        );
    };

    const ActionsComponent = () => (
        <div className="flex items-center space-x-6 ml-auto shrink-0">
            {user ? (
                <div className="flex items-center space-x-4">
                    <Link href="/orders" className="text-sm font-medium hover:text-gray-600">
                        My Orders
                    </Link>
                    <Link href="/account" className="text-gray-900 hover:text-gray-600 transition-colors">
                        <User className="w-5 h-5" />
                    </Link>
                </div>
            ) : (
                isModalMode ? (
                    <button
                        onClick={() => openAuthModal('login')}
                        className="text-sm font-medium hover:text-gray-600 flex items-center gap-1"
                    >
                        <User className="w-4 h-4" /> Sign In
                    </button>
                ) : (
                    <Link href="/login" className="text-sm font-medium hover:text-gray-600 flex items-center gap-1">
                        <User className="w-4 h-4" /> Sign In
                    </Link>
                )
            )}

            <button onClick={openSearch} className="text-gray-900 hover:text-gray-600 transition-colors">
                <Search className="w-5 h-5" />
            </button>

            <Link 
                href={user ? "/wishlist" : "#"} 
                onClick={(e) => {
                    if (!user) {
                        e.preventDefault();
                        openAuthModal();
                    }
                }}
                className="relative text-gray-900 hover:text-gray-600 transition-colors"
            >
                <Heart className="w-5 h-5" />
                {wishlistItems.length > 0 && (
                    <span className="absolute -top-1 -right-1 bg-black text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full font-bold">
                        {wishlistItems.length}
                    </span>
                )}
            </Link>

            <Link href="/cart" className="relative text-gray-900 hover:text-gray-600">
                <ShoppingBag className="w-5 h-5" />
                {cart.items.length > 0 && (
                    <span className="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">
                        {cart.items.length}
                    </span>
                )}
            </Link>
        </div>
    );

    return (
        <header className="bg-white sticky top-0 z-50 shadow-sm">
            {/* Top Bar / Inline Position */}
            <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between relative">
                <div className="flex items-center gap-4">
                    {/* Mobile Menu Toggle */}
                    <button 
                        className="md:hidden text-gray-900 hover:text-gray-600 transition-colors"
                        onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                    >
                        {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                    </button>

                    {/* Logo */}
                    <Link href="/" className="flex items-center gap-2 shrink-0">
                        {logoRelPath ? (
                            <img src={`/${logoRelPath}`} alt={storeName} className="h-8 w-auto object-contain" />
                        ) : (
                            <span className="text-xl font-bold tracking-tighter" style={{ fontFamily: 'var(--font-heading)' }}>
                                {storeName}
                            </span>
                        )}
                    </Link>
                </div>

                {/* Inline Nav Links */}
                {position === 'inline' && (
                    <div className={`${alignmentClasses} hidden md:flex`}>
                        {isMegaMenu && (
                            <>
                                {categories.map((cat) => (
                                    <div key={cat.id} className="group" onMouseLeave={() => setHiddenMenuId(null)}>
                                        <Link href={`/shop?category=${cat.slug}`} className="text-sm font-medium hover:text-gray-600 flex items-center gap-1 py-5">
                                            {cat.name} 
                                            {cat.children && cat.children.length > 0 && (
                                                <span onClick={(e) => { e.preventDefault(); e.stopPropagation(); setForceOpenMenuId(forceOpenMenuId === cat.id ? null : cat.id); }} className="p-1 -mr-1">
                                                    <ChevronDown className="w-3 h-3 text-gray-400 group-hover:text-black transition-colors" />
                                                </span>
                                            )}
                                        </Link>

                                        {/* Mega Menu Dropdown */}
                                        {cat.children && cat.children.length > 0 && (
                                            <div className={`absolute left-0 top-[64px] w-full bg-white border-b border-t shadow-xl transition-all duration-300 transform origin-top z-[100] ${(hiddenMenuId === cat.id && forceOpenMenuId !== cat.id) ? 'opacity-0 invisible pointer-events-none' : forceOpenMenuId === cat.id ? 'opacity-100 visible translate-y-0' : 'opacity-0 invisible group-hover:opacity-100 group-hover:visible -translate-y-2 group-hover:translate-y-0'}`}>
                                                <div className="max-w-7xl mx-auto px-4 py-8">
                                                    <div className="flex gap-16">
                                                        <div className="flex-1 grid grid-cols-3 gap-8">
                                                            {cat.children.map((sub: any) => (
                                                                <div key={sub.id}>
                                                                    <Link href={`/shop?category=${sub.slug}`} className="text-sm font-bold text-gray-900 border-b pb-2 mb-3 block hover:text-gray-600 uppercase tracking-wide" onClick={() => setHiddenMenuId(cat.id)}>
                                                                        {sub.name}
                                                                    </Link>
                                                                    {sub.children && sub.children.length > 0 && (
                                                                        <ul className="space-y-2.5 mt-4">
                                                                            {sub.children.map((deep: any) => (
                                                                                <li key={deep.id}>
                                                                                    <Link href={`/shop?category=${deep.slug}`} className="text-sm text-gray-600 hover:text-black transition-colors block font-medium" onClick={() => setHiddenMenuId(cat.id)}>
                                                                                        {deep.name}
                                                                                    </Link>
                                                                                </li>
                                                                            ))}
                                                                        </ul>
                                                                    )}
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </>
                        )}

                        {isCustom && <DynamicNavItems />}

                        {!isMegaMenu && !isCustom && (
                            <Link href="/shop" className="text-sm font-medium hover:text-gray-600 py-5">
                                Shop
                            </Link>
                        )}
                    </div>
                )}

                <ActionsComponent />
            </div>

            {/* Below Nav Position */}
            {position === 'below' && isCustom && (
                <div className="border-t border-gray-100 bg-gray-50/50 hidden md:block">
                    <div className="max-w-7xl mx-auto px-4">
                        <div className={`flex items-center ${alignment === 'left' ? 'justify-start' : alignment === 'right' ? 'justify-end' : 'justify-center'} space-x-8 relative`}>
                            <DynamicNavItems />
                        </div>
                    </div>
                </div>
            )}

            {/* Mobile Menu Overlay */}
            {isMobileMenuOpen && (
                <div className="md:hidden absolute top-16 left-0 w-full bg-white border-b shadow-lg overflow-y-auto z-50 flex flex-col" style={{ maxHeight: 'calc(100vh - 4rem)' }}>
                    <div className="p-4 flex flex-col gap-4">
                        {isMegaMenu && categories.map((cat) => (
                            <div key={cat.id} className="border-b pb-2">
                                <div 
                                    className="flex items-center justify-between font-bold text-gray-900 py-2 cursor-pointer"
                                    onClick={() => setActiveMobileMenuId(activeMobileMenuId === cat.id ? null : cat.id)}
                                >
                                    <span>{cat.name}</span>
                                    {cat.children && cat.children.length > 0 && (
                                        <ChevronDown className={`w-4 h-4 transition-transform ${activeMobileMenuId === cat.id ? 'rotate-180' : ''}`} />
                                    )}
                                </div>
                                {activeMobileMenuId === cat.id && cat.children && (
                                    <div className="pl-4 py-2 flex flex-col gap-3">
                                        {cat.children.map((sub: any) => (
                                            <div key={sub.id}>
                                                <Link href={`/shop?category=${sub.slug}`} className="font-semibold text-gray-800 text-sm py-1 block" onClick={() => setIsMobileMenuOpen(false)}>
                                                    {sub.name}
                                                </Link>
                                                {sub.children && sub.children.length > 0 && (
                                                    <div className="pl-3 mt-1 flex flex-col gap-2">
                                                        {sub.children.map((deep: any) => (
                                                            <Link key={deep.id} href={`/shop?category=${deep.slug}`} className="text-gray-500 text-sm hover:text-black py-1 block" onClick={() => setIsMobileMenuOpen(false)}>
                                                                {deep.name}
                                                            </Link>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                        
                        {isCustom && menuItems.map((item: any) => (
                            <div key={item.id} className="border-b pb-2">
                                {item.type === 'mega_menu' ? (
                                    <>
                                        <div 
                                            className="flex items-center justify-between font-bold text-gray-900 py-2 cursor-pointer"
                                            onClick={() => setActiveMobileMenuId(activeMobileMenuId === item.id ? null : item.id)}
                                        >
                                            <span>{item.label}</span>
                                            <ChevronDown className={`w-4 h-4 transition-transform ${activeMobileMenuId === item.id ? 'rotate-180' : ''}`} />
                                        </div>
                                        {activeMobileMenuId === item.id && (
                                            <div className="pl-4 py-2 flex flex-col gap-4">
                                                {item.layout_columns?.map((col: any) => (
                                                    <div key={col.id || Math.random()} className="flex flex-col gap-3">
                                                        {col.blocks?.map((block: any) => (
                                                            <div key={block.id || Math.random()}>
                                                                {block.type === 'image' ? (
                                                                    <div className="w-[150px] mt-2" onClick={() => setIsMobileMenuOpen(false)}>
                                                                        {renderDynamicLink(block, true)}
                                                                    </div>
                                                                ) : (
                                                                    <div>
                                                                        {block.label && (
                                                                            <div className="font-semibold text-gray-800 text-sm mb-2" onClick={() => setIsMobileMenuOpen(false)}>
                                                                                {block.link ? <Link href={block.link}>{block.label}</Link> : block.label}
                                                                            </div>
                                                                        )}
                                                                        {block.links && (
                                                                            <div className="pl-2 flex flex-col gap-2">
                                                                                {block.links.map((link: any) => (
                                                                                    <div key={link.id || Math.random()} onClick={() => setIsMobileMenuOpen(false)}>
                                                                                        {renderDynamicLink(link, true)}
                                                                                    </div>
                                                                                ))}
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        ))}
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <div className="font-bold text-gray-900 py-2" onClick={() => setIsMobileMenuOpen(false)}>
                                        {renderDynamicLink(item)}
                                    </div>
                                )}
                            </div>
                        ))}
                        
                        {!isMegaMenu && !isCustom && (
                            <Link href="/shop" className="font-bold text-gray-900 py-2 border-b block" onClick={() => setIsMobileMenuOpen(false)}>
                                Shop
                            </Link>
                        )}
                    </div>
                </div>
            )}

            {/* Search Modal Overlay */}
            {isSearchOpen && (
                <div className="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm flex items-start justify-center pt-24 px-4">
                    <div 
                        className="absolute inset-0" 
                        onClick={closeSearch}
                    ></div>
                    <SearchModalContent closeSearch={closeSearch} />
                </div>
            )}
        </header>
    );
}
