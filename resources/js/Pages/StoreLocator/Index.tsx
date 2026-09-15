import React from 'react';
import { Head } from '@inertiajs/react';
import { MapPin, PhoneCall, Clock, Calendar } from 'lucide-react';

interface StoreLocation {
    id: number;
    name: string;
    store_image: string | null;
    address: string;
    address_line_2: string | null;
    city: string;
    state: string;
    pincode: string;
    contact_phone: string | null;
    contact_email: string | null;
    map_link: string | null;
    start_date: string | null;
    end_date: string | null;
    operating_hours: string | null;
    type: string;
}

export default function StoreLocator({ stores }: { stores: StoreLocation[] }) {
    return (
        <div className="min-h-screen bg-gray-50 pb-20">
            <Head>
                <title>Our Stores</title>
                {stores.length === 0 && <meta name="robots" content="noindex, nofollow" />}
            </Head>
            
            {/* Header */}
            <div className="bg-black text-white pt-24 pb-20 px-6 relative overflow-hidden">
                <div className="absolute inset-0 bg-noise opacity-10 pointer-events-none"></div>
                <div className="max-w-7xl mx-auto text-center relative z-10">
                    <h1 className="text-4xl md:text-5xl font-black mb-6 tracking-tight uppercase">Our Stores</h1>
                    <p className="text-gray-400 max-w-xl mx-auto text-base sm:text-lg">
                        Find a location near you to experience our collection in person.
                    </p>
                </div>
            </div>

            {/* Store Grid */}
            <div className="max-w-7xl mx-auto px-6 -mt-10 relative z-20">
                {stores.length === 0 ? (
                    <div className="bg-white rounded-3xl shadow-xl p-16 text-center border border-gray-100">
                        <MapPin className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <h2 className="text-2xl font-bold text-gray-900 mb-2">No Stores Currently Available</h2>
                        <p className="text-gray-500">Check back soon for new locations.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {stores.map((store) => (
                            <div key={store.id} className="bg-white rounded-3xl overflow-hidden shadow-lg shadow-gray-100 border border-gray-100 flex flex-col hover:-translate-y-1 transition-transform duration-300">
                                
                                {/* Image */}
                                <div className="h-64 bg-gray-200 relative">
                                    {store.store_image ? (
                                        <img src={`/storage/${store.store_image}`} alt={store.name} className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-gray-100">
                                            <MapPin className="w-12 h-12 text-gray-300" />
                                        </div>
                                    )}
                                    <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-black">
                                        {store.type === 'temporary' ? 'Pop-up / Stall' : 'Permanent Store'}
                                    </div>
                                </div>

                                {/* Content */}
                                <div className="p-8 flex-grow flex flex-col">
                                    <h2 className="text-xl font-bold text-gray-900 mb-2">{store.name}</h2>
                                    
                                    <div className="space-y-4 mb-8 flex-grow">
                                        <div className="flex items-start gap-3">
                                            <MapPin className="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                                            <p className="text-sm text-gray-600 leading-relaxed">
                                                {store.address}
                                                {store.address_line_2 && <><br />{store.address_line_2}</>}
                                                <br />{store.city}, {store.state} {store.pincode}
                                            </p>
                                        </div>

                                        
                                        {store.operating_hours && (
                                            <div className="flex items-start gap-3">
                                                <Clock className="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                                                <p className="text-sm text-gray-600">
                                                    {store.operating_hours}
                                                </p>
                                            </div>
                                        )}
{(store.start_date || store.end_date) && store.type === 'temporary' && (
                                            <div className="flex items-start gap-3">
                                                <Calendar className="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                                                <p className="text-sm text-gray-600">
                                                    {store.start_date && <span>From: {new Date(store.start_date).toLocaleDateString()}</span>}
                                                    {store.start_date && store.end_date && <br />}
                                                    {store.end_date && <span>Until: {new Date(store.end_date).toLocaleDateString()}</span>}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="flex items-center gap-3 pt-4 border-t border-gray-100">
                                        {store.map_link ? (
                                            <a 
                                                href={store.map_link} 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                className="flex-grow flex items-center justify-center h-12 rounded-xl text-[11px] font-black uppercase tracking-widest hover:opacity-90 transition-opacity shadow-sm"
                                                style={{ backgroundColor: 'var(--primary)', color: 'var(--secondary)' }}
                                            >
                                                Visit Store
                                            </a>
                                        ) : (
                                            <div className="flex-grow bg-gray-100 text-gray-400 flex items-center justify-center h-12 rounded-xl text-[11px] font-black uppercase tracking-widest cursor-not-allowed">
                                                No Map Link
                                            </div>
                                        )}

                                        {store.contact_phone && (
                                            <a 
                                                href={`tel:${store.contact_phone}`}
                                                className="w-12 h-12 flex items-center justify-center rounded-xl hover:opacity-90 transition-opacity shrink-0 shadow-sm"
                                                style={{ backgroundColor: 'var(--primary)', color: 'var(--secondary)' }}
                                                title={`Call ${store.contact_phone}`}
                                            >
                                                <PhoneCall className="w-4 h-4" />
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
