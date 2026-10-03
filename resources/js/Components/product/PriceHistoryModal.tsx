import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, Legend, ResponsiveContainer } from 'recharts';
import { X, TrendingUp, TrendingDown, Minus } from 'lucide-react';
import clsx from 'clsx';
import { usePage } from '@inertiajs/react';

interface PriceHistoryModalProps {
    isOpen: boolean;
    onClose: () => void;
    skuId: number;
    skuName: string;
}

interface PriceHistory {
    id: number;
    sku_id: number;
    price: string;
    mrp: string;
    created_at: string;
}

export default function PriceHistoryModal({ isOpen, onClose, skuId, skuName }: PriceHistoryModalProps) {
    const [history, setHistory] = useState<PriceHistory[]>([]);
    const [loading, setLoading] = useState(false);
    const { settings } = usePage<any>().props;

    const bgColor = settings?.price_history_bg_color || '#ffffff';
    const textColor = settings?.price_history_text_color || '#18181b';
    const salesColor = settings?.price_history_sales_color || '#10b981';
    const mrpColor = settings?.price_history_mrp_color || '#ef4444';
    const showTrend = (settings?.price_history_show_trend ?? '1') === '1';
    const graphPosition = settings?.price_history_graph_position || 'top';

    useEffect(() => {
        if (isOpen && skuId) {
            setLoading(true);
            axios.get(`/api/skus/${skuId}/price-history`)
                .then(res => {
                    setHistory(res.data);
                })
                .catch(err => console.error(err))
                .finally(() => setLoading(false));
        }
    }, [isOpen, skuId]);

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };

        if (isOpen) {
            window.addEventListener('keydown', handleKeyDown);
        }

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    const chartData = history.map(h => ({
        date: new Date(h.created_at).toLocaleDateString('en-IN', { month: 'short', day: 'numeric' }),
        SalesPrice: parseFloat(h.price),
        MRP: h.mrp ? parseFloat(h.mrp) : null,
        fullDate: new Date(h.created_at).toLocaleString(),
    }));

    const chartNode = (
        <div className="h-64 w-full">
            <ResponsiveContainer width="100%" height="100%">
                <LineChart data={chartData} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                    <CartesianGrid strokeDasharray="3 3" opacity={0.2} stroke={textColor} />
                    <XAxis 
                        dataKey="fullDate" 
                        tickFormatter={(val) => new Date(val).toLocaleDateString('en-IN', { month: 'short', day: 'numeric' })} 
                        tick={{fontSize: 12, fill: textColor}} 
                    />
                    <YAxis tick={{fontSize: 12, fill: textColor}} domain={['auto', 'auto']} tickFormatter={(value) => `₹${value}`} />
                    <RechartsTooltip 
                        formatter={(value: number, name: string) => [`₹${value} (${name})`, undefined]}
                        labelFormatter={(label, payload) => payload?.[0]?.payload?.fullDate || label}
                        contentStyle={{ backgroundColor: bgColor, color: textColor, borderColor: textColor, opacity: 0.9 }}
                    />
                    <Legend wrapperStyle={{ color: textColor }} />
                    <Line type="monotone" dataKey="SalesPrice" name="Sales Price" stroke={salesColor} strokeWidth={2} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                    <Line type="monotone" dataKey="MRP" name="MRP" stroke={mrpColor} strokeWidth={2} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );

    const tableNode = (
        <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse" style={{ color: textColor }}>
                <thead>
                    <tr style={{ borderBottom: `1px solid ${textColor}33` }}>
                        <th className="p-3 text-sm font-semibold opacity-80">Date</th>
                        <th className="p-3 text-sm font-semibold opacity-80 text-right">MRP</th>
                        <th className="p-3 text-sm font-semibold opacity-80 text-right">Sales Price</th>
                    </tr>
                </thead>
                <tbody>
                    {history.map((record, index) => {
                        const prevRecord = index > 0 ? history[index - 1] : null;
                        
                        const getTrend = (current: string | null, prev: string | null) => {
                            if (!showTrend || !prev || !current) return null;
                            const currNum = parseFloat(current);
                            const prevNum = parseFloat(prev);
                            if (currNum > prevNum) return <TrendingUp className="w-4 h-4 text-red-500 inline ml-1" />;
                            if (currNum < prevNum) return <TrendingDown className="w-4 h-4 text-green-500 inline ml-1" />;
                            return <Minus className="w-4 h-4 opacity-50 inline ml-1" />;
                        };

                        return (
                            <tr key={record.id} className="transition-colors" style={{ borderBottom: `1px solid ${textColor}1A` }}>
                                <td className="p-3 text-sm opacity-90">
                                    {new Date(record.created_at).toLocaleString('en-IN', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                </td>
                                <td className="p-3 text-sm opacity-90 text-right">
                                    {record.mrp ? `₹${parseFloat(record.mrp).toLocaleString('en-IN')}` : '-'}
                                    {getTrend(record.mrp, prevRecord?.mrp || null)}
                                </td>
                                <td className="p-3 text-sm font-bold text-right">
                                    ₹{parseFloat(record.price).toLocaleString('en-IN')}
                                    {getTrend(record.price, prevRecord?.price || null)}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );

    return (
        <div 
            className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm transition-all"
            onClick={onClose}
        >
            <div 
                className="rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]" 
                style={{ backgroundColor: bgColor, color: textColor }}
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-4 border-b border-black/10 dark:border-white/10 flex justify-between items-center gap-4">
                    <h3 className="text-lg font-bold truncate flex-1" title={`Price History (Last 30 Days) - ${skuName}`}>Price History (Last 30 Days) - {skuName}</h3>
                    <button onClick={onClose} className="p-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-full transition-colors shrink-0" style={{ color: textColor }}>
                        <X className="w-5 h-5" />
                    </button>
                </div>
                
                <div className="p-6 overflow-y-auto custom-scrollbar">
                    {loading ? (
                        <div className="flex justify-center items-center py-20">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2" style={{ borderColor: textColor }}></div>
                        </div>
                    ) : history.length === 0 ? (
                        <div className="text-center py-10 opacity-70">
                            No price history available for the last 30 days.
                        </div>
                    ) : (
                        <div className={clsx("flex gap-8", graphPosition === 'bottom' ? "flex-col-reverse" : "flex-col")}>
                            {chartNode}
                            {tableNode}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
