import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

function useCountdown(endDate: string, timeZone: string) {
    const [time, setTime] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0, expired: false });

    useEffect(() => {
        const calc = () => {
            if (!endDate) return;

            let targetNowTime = Date.now();
            if (timeZone) {
                try {
                    const now = new Date();
                    const formatter = new Intl.DateTimeFormat('en-US', {
                        timeZone,
                        year: 'numeric', month: 'numeric', day: 'numeric',
                        hour: 'numeric', minute: 'numeric', second: 'numeric',
                        hourCycle: 'h23'
                    });
                    const parts = formatter.formatToParts(now);
                    let y = 0, m = 0, d = 0, h = 0, min = 0, s = 0;
                    for(const p of parts) {
                        if(p.type === 'year') y = parseInt(p.value);
                        if(p.type === 'month') m = parseInt(p.value) - 1;
                        if(p.type === 'day') d = parseInt(p.value);
                        if(p.type === 'hour') h = parseInt(p.value);
                        if(p.type === 'minute') min = parseInt(p.value);
                        if(p.type === 'second') s = parseInt(p.value);
                    }
                    targetNowTime = new Date(y, m, d, h, min, s).getTime();
                } catch(e) {
                    // Fallback
                }
            }

            let endStr = endDate;
            if (endStr.includes(' ') && !endStr.includes('T')) endStr = endStr.replace(' ', 'T');
            if (endStr.endsWith('Z')) endStr = endStr.slice(0, -1);
            
            const endParts = endStr.split(/[-T: ]/);
            let endTime;
            if (endParts.length >= 6) {
                 endTime = new Date(
                     parseInt(endParts[0]), 
                     parseInt(endParts[1]) - 1, 
                     parseInt(endParts[2]),
                     parseInt(endParts[3]),
                     parseInt(endParts[4]),
                     parseInt(endParts[5])
                 ).getTime();
            } else {
                 endTime = new Date(endStr).getTime();
            }

            const diff = endTime - targetNowTime;
            
            if (diff <= 0) { 
                setTime({ days: 0, hours: 0, minutes: 0, seconds: 0, expired: true }); 
                return; 
            }
            setTime({
                days: Math.floor(diff / 86400000),
                hours: Math.floor((diff % 86400000) / 3600000),
                minutes: Math.floor((diff % 3600000) / 60000),
                seconds: Math.floor((diff % 60000) / 1000),
                expired: false,
            });
        };
        calc();
        const t = setInterval(calc, 1000);
        return () => clearInterval(t);
    }, [endDate, timeZone]);

    return time;
}

export default function CountdownTimer({ data, isFluid, sectionBg }: { data: any; isFluid?: boolean; sectionBg?: string }) {
    const { settings } = usePage<any>().props;
    const timeZone = settings?.time_zone || 'UTC';
    const time = useCountdown(data?.end_date || '', timeZone);
    if (!data?.end_date || time.expired) return null;

    const isDark = data?.bg_style === 'dark';
    const bg = sectionBg || (isDark ? '#111111' : '#ffffff');
    const textColor = isDark ? 'text-white' : 'text-gray-900';
    const mutedColor = isDark ? 'text-white/50' : 'text-gray-400';
    const boxBg = isDark ? 'bg-white/10' : 'bg-gray-50 border border-gray-100';

    const pad = (n: number) => String(n).padStart(2, '0');
    const units = [
        { label: 'Days', value: pad(time.days) },
        { label: 'Hours', value: pad(time.hours) },
        { label: 'Mins', value: pad(time.minutes) },
        { label: 'Secs', value: pad(time.seconds) },
    ];

    return (
        <section className="w-full" style={{ backgroundColor: bg }}>
            <div className="max-w-3xl mx-auto px-4 text-center">
                {data.title && (
                    <h2 className={`text-3xl md:text-5xl font-black tracking-tight mb-3 ${textColor}`}>{data.title}</h2>
                )}
                {data.description && (
                    <p className={`text-base md:text-lg mb-10 ${mutedColor}`}>{data.description}</p>
                )}
                <div className="flex items-center justify-center gap-3 md:gap-6 mb-10">
                    {units.map(u => (
                        <div key={u.label} className="flex flex-col items-center">
                            <div className={`${boxBg} rounded-2xl px-5 py-4 md:px-8 md:py-6 min-w-[70px] md:min-w-[110px]`}>
                                <span className={`text-4xl md:text-6xl font-black tabular-nums ${textColor}`}>{u.value}</span>
                            </div>
                            <span className={`text-xs font-semibold uppercase tracking-widest mt-2 ${mutedColor}`}>{u.label}</span>
                        </div>
                    ))}
                </div>
                {data.cta_text && data.cta_link && (
                    <Link
                        href={data.cta_link}
                        className={`inline-block px-10 py-4 rounded-full font-bold transition-opacity hover:opacity-90 ${isDark ? 'bg-white text-black' : 'bg-[var(--primary)] text-[var(--secondary)]'}`}
                    >
                        {data.cta_text}
                    </Link>
                )}
            </div>
        </section>
    );
}
