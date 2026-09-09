import { useState, useEffect } from 'react';
import { MapPin, Plus, Check, AlertCircle } from 'lucide-react';
import api from '@/lib/api';
import { useAuthStore } from '@/store/auth';
import CountryCodePicker from '@/Components/auth/CountryCodePicker';

interface Address { id: number; name: string; phone: string; address_line1: string; address_line2?: string; city: string; district?: string; state: string; zip_code: string; country: string; is_default: boolean; }

interface Props {
    selectedId: number | null;
    onChange: (addr: Address) => void;
}

function Inp({ label, value, onChange, placeholder, req, readOnly }: { label: string; value: string; onChange: (v: string) => void; placeholder?: string; req?: boolean; readOnly?: boolean }) {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">{label}{req && <span className="text-red-500 ml-0.5">*</span>}</label>
            <input value={value} onChange={e => onChange(e.target.value)} placeholder={placeholder} readOnly={readOnly}
                className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all read-only:bg-gray-100 read-only:text-gray-600 read-only:cursor-not-allowed read-only:focus:ring-0 read-only:focus:border-gray-200" />
        </div>
    );
}

export default function CheckoutAddress({ selectedId, onChange }: Props) {
    const { user } = useAuthStore();
    const [addresses, setAddresses] = useState<Address[]>([]);
    const [loading, setLoading] = useState(false);
    const [showForm, setShowForm] = useState(false);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [form, setForm] = useState({ name: '', phone: '', line1: '', line2: '', pincode: '', state: '', city: '', district: '', country: 'India' });
    const [locked, setLocked] = useState({ city: false, district: false, state: false, country: true });
    const [activeCountryId, setActiveCountryId] = useState<number | null>(null);

    useEffect(() => {
        api.get('/api/localization/countries').then(res => {
            if (res.data && res.data.length > 0) {
                setActiveCountryId(res.data[0].id);
                setForm(p => ({ ...p, country: res.data[0].name }));
            }
        }).catch(() => {});
    }, []);
    
    useEffect(() => {
        const fetchPincode = async () => {
            if (form.pincode.length === 6 && activeCountryId) {
                try {
                    const res = await api.get(`/api/localization/postal-code/${activeCountryId}/${form.pincode}`);
                    if (res.data.found) {
                        setForm(p => ({
                            ...p,
                            city: res.data.city || p.city,
                            district: res.data.district || p.district,
                            state: res.data.state || p.state,
                        }));
                        setLocked(p => ({
                            ...p,
                            city: !!res.data.city,
                            district: !!res.data.district,
                            state: !!res.data.state,
                        }));
                    } else {
                        setLocked(p => ({ ...p, city: false, district: false, state: false }));
                    }
                } catch (e) {
                    setLocked(p => ({ ...p, city: false, district: false, state: false }));
                }
            } else if (form.pincode.length < 6) {
                setLocked(p => ({ ...p, city: false, district: false, state: false }));
            }
        };
        fetchPincode();
    }, [form.pincode]);
    const [countryCode, setCountryCode] = useState('+91');
    const f = (k: string, v: string) => setForm(p => ({ ...p, [k]: v }));

    const load = async () => {
        if (!user) return;
        setLoading(true);
        try {
            const r = await api.get('/api/account/addresses');
            const list: Address[] = r.data;
            setAddresses(list);
            // Auto-select default or single address
            const def = list.find(a => a.is_default) || (list.length === 1 ? list[0] : null);
            if (def && !selectedId) onChange(def);
        } catch {}
        finally { setLoading(false); }
    };

    useEffect(() => { load(); }, [user]);

    const save = async () => {
        if (!form.name || !form.phone || !form.line1 || !form.city || !form.state || !form.pincode) {
            setErr('Please fill all required fields.'); return;
        }
        setSaving(true); setErr('');

        let finalPhone = form.phone.trim();
        if (finalPhone.startsWith(countryCode)) {
            finalPhone = finalPhone.substring(countryCode.length);
        }
        const rawCode = countryCode.replace('+', '');
        if (finalPhone.startsWith(rawCode)) {
            finalPhone = finalPhone.substring(rawCode.length);
        }
        finalPhone = `${countryCode}${finalPhone}`;

        try {
            await api.post('/api/account/addresses', { ...form, phone: finalPhone });
            setShowForm(false);
            setForm({ name: '', phone: '', line1: '', line2: '', pincode: '', state: '', city: '', district: '', country: form.country });
            setLocked({ city: false, district: false, state: false, country: true });
            await load();
        } catch (e: any) { setErr(e.response?.data?.message || 'Could not save.'); }
        finally { setSaving(false); }
    };

    return (
        <div className="space-y-3">
            {loading && <div className="h-20 bg-gray-50 rounded-2xl animate-pulse" />}

            {!loading && addresses.map(addr => (
                <button key={addr.id} onClick={() => onChange(addr)} type="button"
                    className={`w-full text-left p-4 rounded-2xl border-2 transition-all ${selectedId === addr.id ? 'border-gray-900 bg-gray-50' : 'border-gray-100 hover:border-gray-300 bg-white'}`}>
                    <div className="flex justify-between items-start">
                        <div>
                            <p className="text-sm font-bold text-gray-900">{addr.name} <span className="text-gray-400 font-normal">· {addr.phone}</span></p>
                            <p className="text-xs text-gray-500 mt-1 leading-relaxed">
                                {addr.address_line1}{addr.address_line2 ? `, ${addr.address_line2}` : ''}, {addr.city}{addr.district ? `, ${addr.district}` : ''}, {addr.state} – {addr.zip_code}
                                <span className="block mt-1 text-gray-500">{addr.country}</span>
                            </p>
                        </div>
                        <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 ml-4 mt-0.5 transition-all ${selectedId === addr.id ? 'border-gray-900 bg-gray-900' : 'border-gray-300'}`}>
                            {selectedId === addr.id && <Check className="w-3 h-3 text-white" strokeWidth={3} />}
                        </div>
                    </div>
                    {addr.is_default && <span className="mt-2 inline-block text-[9px] font-black uppercase tracking-widest bg-gray-900 text-white px-2 py-0.5 rounded-full">Default</span>}
                </button>
            ))}

            {showForm && (
                <div className="p-4 rounded-2xl border border-gray-200 bg-gray-50 space-y-3">
                    <p className="text-xs font-black text-gray-900 uppercase tracking-wider">New Delivery Address</p>
                    <div className="grid grid-cols-1 gap-3">
                        <Inp label="Full Name" value={form.name} onChange={v => f('name', v)} placeholder="Recipient" req />
                        
                        <div className="flex flex-col gap-1">
                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Phone<span className="text-red-500 ml-0.5">*</span></label>
                            <div className="flex bg-white rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-gray-900 focus-within:border-gray-900 border border-gray-200 transition-all">
                                <CountryCodePicker value={countryCode} onChange={setCountryCode} />
                                <input 
                                    value={form.phone} 
                                    onChange={e => f('phone', e.target.value)} 
                                    placeholder="10-digit"
                                    className="flex-1 bg-transparent px-3.5 py-2.5 text-sm focus:outline-none" 
                                />
                            </div>
                        </div>

                        <div className="col-span-1"><Inp label="Address Line 1" value={form.line1} onChange={v => f('line1', v)} placeholder="House, Street" req /></div>
                        <div className="col-span-1"><Inp label="Address Line 2" value={form.line2} onChange={v => f('line2', v)} placeholder="Area, Landmark (optional)" /></div>
                        <div className="col-span-1"><Inp label="Pincode" value={form.pincode} onChange={v => f('pincode', v)} placeholder="6-digit pincode" req /></div>
                        <div className="col-span-1"><Inp label="State" value={form.state} onChange={v => f('state', v)} placeholder="State" req readOnly={locked.state} /></div>
                        <div className="col-span-1"><Inp label="City" value={form.city} onChange={v => f('city', v)} placeholder="City" req readOnly={locked.city} /></div>
                        <div className="col-span-1"><Inp label="District" value={form.district} onChange={v => f('district', v)} placeholder="District (optional)" readOnly={locked.district} /></div>
                        <div className="col-span-1"><Inp label="Country" value={form.country} onChange={v => f('country', v)} placeholder="Country" req readOnly={locked.country} /></div>
                    </div>
                    {err && <p className="text-xs text-red-500 flex items-center gap-1"><AlertCircle className="w-3.5 h-3.5" />{err}</p>}
                    <div className="flex gap-2">
                        <button onClick={save} disabled={saving} className="flex-1 bg-black text-white text-xs font-bold uppercase tracking-wider py-2.5 rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50">
                            {saving ? 'Saving…' : 'Save & Use This Address'}
                        </button>
                        <button onClick={() => { setShowForm(false); setErr(''); }} className="text-xs text-gray-500 font-medium px-4 py-2.5 rounded-xl hover:bg-gray-100 transition-all">Cancel</button>
                    </div>
                </div>
            )}

            {!showForm && user && (
                <button onClick={() => setShowForm(true)} type="button"
                    className="w-full flex items-center justify-center gap-2 border border-dashed border-gray-300 hover:border-black text-gray-500 hover:text-black text-xs font-bold uppercase tracking-wider py-3 rounded-2xl transition-all">
                    <Plus className="w-4 h-4" /> Add New Address
                </button>
            )}
        </div>
    );
}
