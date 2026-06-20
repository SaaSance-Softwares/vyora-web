export default function AnnouncementMarquee({ data, isFluid, sectionBg }: { data: any; isFluid?: boolean; sectionBg?: string }) {
    if (!data?.items || data.items.length === 0) return null;

    const bg = sectionBg || data.bg_color || '#000000';
    const textColor = data.text_color || '#ffffff';
    
    // Calculate duration based on number of items to maintain a constant velocity
    const secondsPerItem = data.speed === 'slow' ? 15 : data.speed === 'fast' ? 5 : 10;
    
    // Repeat items a few times so a single block can easily fill the screen width
    const repeatedItems = [...data.items, ...data.items, ...data.items, ...data.items];
    
    const duration = `${repeatedItems.length * secondsPerItem}s`;

    const MarqueeBlock = () => (
        <div className="flex min-w-full shrink-0 items-center justify-around" style={{ animation: `marquee-scroll ${duration} linear infinite` }}>
            {repeatedItems.map((item: any, idx: number) => (
                <span key={idx} className="inline-flex items-center shrink-0">
                    {item.link ? (
                        <a href={item.link} className="text-sm font-semibold tracking-wide hover:opacity-70 transition-opacity px-8">
                            {item.text}
                        </a>
                    ) : (
                        <span className="text-sm font-semibold tracking-wide px-8">{item.text}</span>
                    )}
                    <span className="opacity-30 text-xs">·</span>
                </span>
            ))}
        </div>
    );

    return (
        <div className="w-full flex overflow-hidden py-3" style={{ backgroundColor: bg, color: textColor }}>
            <MarqueeBlock />
            <MarqueeBlock />
            <style>{`
                @keyframes marquee-scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-100%); }
                }
            `}</style>
        </div>
    );
}
