import { Link } from '@inertiajs/react';

export default function ImageBanner({ data, sectionSettings }: { data: any, sectionSettings?: any }) {
    if (!data?.image) return null;

    const fitClass: any = {
        cover: 'object-cover',
        contain: 'object-contain',
        fill: 'object-fill',
        auto: 'object-none'
    };
    
    const selectedFit = fitClass[data.object_fit || 'cover'] || 'object-cover';

    const positionClass: any = {
        center: 'absolute inset-0 flex items-center justify-center text-center px-4',
        bottom: 'absolute inset-x-0 bottom-0 p-8 pt-24 text-center bg-gradient-to-t from-black/80 to-transparent',
        top: 'absolute inset-x-0 top-0 p-8 pb-24 text-center bg-gradient-to-b from-black/80 to-transparent',
        left: 'absolute inset-y-0 left-0 p-8 pr-24 flex items-center justify-start text-left bg-gradient-to-r from-black/80 to-transparent w-full md:w-2/3 lg:w-1/2',
        right: 'absolute inset-y-0 right-0 p-8 pl-24 flex items-center justify-end text-right bg-gradient-to-l from-black/80 to-transparent w-full md:w-2/3 lg:w-1/2',
        below: 'relative p-8 text-center bg-white'
    };
    
    const selectedPosition = positionClass[data.text_position || 'center'] || positionClass.center;

    
    const heightClass: any = {
        auto: '',
        small: 'h-[300px] md:h-[400px]',
        medium: 'h-[400px] md:h-[600px]',
        large: 'h-[500px] md:h-[700px] lg:h-[800px]',
        fullscreen: 'h-screen'
    };
    const defaultHeight = data.text_position === 'below' ? 'small' : 'large';
    const heightSetting = sectionSettings?.section_height || data.section_height || defaultHeight;
    const isAutoHeight = heightSetting === 'auto';
    const selectedHeight = heightClass[heightSetting] ?? heightClass[defaultHeight];

    const customTextColor = sectionSettings?.text_color ? sectionSettings.text_color : undefined;

    const isExternal = data.link_url && (data.link_url.startsWith('http://') || data.link_url.startsWith('https://'));

    const LinkWrapper = ({ children }: { children: any }) => {
        if (!data.link_url) return <>{children}</>;
        
        if (isExternal) {
            return (
                <a href={data.link_url} target="_blank" rel="noopener noreferrer" className="block w-full h-full cursor-pointer group">
                    {children}
                </a>
            );
        }
        
        return (
            <Link href={data.link_url} className="block w-full h-full cursor-pointer group">
                {children}
            </Link>
        );
    };

    return (
        <section className="w-full relative">
            <LinkWrapper>
                <div className={`w-full relative ${selectedHeight}`}>
                    <img 
                        src={data.image} 
                        alt="Banner" 
                        className={`w-full block ${isAutoHeight ? 'h-auto object-cover' : 'h-full ' + selectedFit}`}
                    />
                    
                    {data.text && data.text_position !== 'below' && (
                        <div className={selectedPosition}>
                            <div className="max-w-3xl mx-auto w-full">
                                <p 
                                    className="text-3xl md:text-5xl font-bold leading-tight drop-shadow-lg"
                                    style={{ color: customTextColor || 'white' }}
                                >
                                    {data.text}
                                </p>
                            </div>
                        </div>
                    )}
                </div>
                {data.text && data.text_position === 'below' && (
                    <div className={selectedPosition}>
                        <div className="max-w-4xl mx-auto">
                            <p 
                                className="text-2xl md:text-3xl font-medium leading-relaxed"
                                style={{ color: customTextColor || '#111827' }}
                            >
                                {data.text}
                            </p>
                        </div>
                    </div>
                )}
            </LinkWrapper>
        </section>
    );
}
