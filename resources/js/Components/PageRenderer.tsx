import HeroSlider from "./sections/HeroSlider";
import ProductCarousel from "./sections/ProductCarousel";
import TextBlock from "./sections/TextBlock";
import ImageGrid from "./sections/ImageGrid";
import ImageBanner from "./sections/ImageBanner";
import HorizontalScrollCards from "./sections/HorizontalScrollCards";
import ProductHorizontalScroll from "./sections/ProductHorizontalScroll";
import ImageProductCarousel from "./sections/ImageProductCarousel";
import AnnouncementMarquee from "./sections/AnnouncementMarquee";
import FeatureHighlights from "./sections/FeatureHighlights";
import CategoryGrid from "./sections/CategoryGrid";
import SplitBanner from "./sections/SplitBanner";
import NewsletterSignup from "./sections/NewsletterSignup";
import VideoBanner from "./sections/VideoBanner";
import TestimonialsSlider from "./sections/TestimonialsSlider";
import CountdownTimer from "./sections/CountdownTimer";
import Spacer from "./sections/Spacer";

const sectionComponents: any = {
    hero_slider: HeroSlider,
    product_carousel: ProductCarousel,
    text_block: TextBlock,
    image_grid: ImageGrid,
    image_banner: ImageBanner,
    vertical_scroll_cards: HorizontalScrollCards,
    horizontal_scroll_cards: HorizontalScrollCards,
    product_vertical_scroll: ProductHorizontalScroll,
    product_horizontal_scroll: ProductHorizontalScroll,
    image_product_carousel: ImageProductCarousel,
    announcement_marquee: AnnouncementMarquee,
    feature_highlights: FeatureHighlights,
    category_grid: CategoryGrid,
    split_banner: SplitBanner,
    newsletter_signup: NewsletterSignup,
    video_banner: VideoBanner,
    testimonials_slider: TestimonialsSlider,
    countdown_timer: CountdownTimer,
    spacer: Spacer,
};



const paddingYMap: Record<string, string> = {
    '0': 'py-0',
    '4': 'py-4 md:py-6',
    '8': 'py-8 md:py-12',
    '12': 'py-12 md:py-16',
    '16': 'py-16 md:py-24',
    '24': 'py-24 md:py-32',
    '32': 'py-32 md:py-40',
};

const paddingXMap: Record<string, string> = {
    '0': 'px-0',
    '4': 'px-4 md:px-6',
    '8': 'px-8 md:px-12',
    '12': 'px-12 md:px-16',
    '16': 'px-16 md:px-24',
    '24': 'px-24 md:px-32',
    '32': 'px-32 md:px-40',
};

const marginYMap: Record<string, string> = {
    '0': 'my-0',
    '4': 'my-4 md:my-6',
    '8': 'my-8 md:my-12',
    '12': 'my-12 md:my-16',
    '16': 'my-16 md:my-24',
    '24': 'my-24 md:my-32',
    '32': 'my-32 md:my-40',
};

const marginXMap: Record<string, string> = {
    '0': 'mx-0',
    '4': 'mx-4 md:mx-6',
    '8': 'mx-8 md:mx-12',
    '12': 'mx-12 md:mx-16',
    '16': 'mx-16 md:mx-24',
    '24': 'mx-24 md:mx-32',
    '32': 'mx-32 md:mx-40',
};

export default function PageRenderer({ content, layout = 'default', settings = {} }: { content: any[]; layout?: string; settings?: any }) {
    if (!content || !Array.isArray(content)) return null;

    const pageOverride = layout || 'default';
    const globalDefault = settings.default_page_layout || 'contained';
    const isFluid = pageOverride === 'fluid' || (pageOverride === 'default' && globalDefault === 'fluid');

    return (
        <div className="w-full">
            {content.map((section, index) => {
                if (section.type === 'page_meta') return null;

                const Component = sectionComponents[section.type];
                if (!Component) {
                    console.warn(`Unknown component type: ${section.type}`);
                    return null;
                }

                const s = section.settings || {};
                const bgColor = s.bg_color || '';
                const textColor = s.text_color || '';
                const showMobile = s.show_mobile !== false;
                const showDesktop = s.show_desktop !== false;

                let visibilityClass = '';
                if (!showMobile && !showDesktop) visibilityClass = 'hidden';
                else if (!showMobile) visibilityClass = 'hidden md:block';
                else if (!showDesktop) visibilityClass = 'block md:hidden';

                const innerPaddingYClass = paddingYMap[s.inner_padding_y ?? s.inner_padding] || '';
                const innerPaddingXClass = paddingXMap[s.inner_padding_x ?? s.inner_padding] || '';
                const outerMarginYClass = marginYMap[s.outer_margin_y ?? s.outer_padding] || '';
                const outerMarginXClass = marginXMap[s.outer_margin_x ?? s.outer_padding] || '';

                const sectionWidth = s.section_width && s.section_width !== 'default' ? s.section_width : (section.data?.section_width || 'default');
                const isSectionFluid = section.type === 'announcement_marquee' || sectionWidth === 'full' || (sectionWidth !== 'contained' && isFluid);
                
                const sectionHeight = s.section_height || 'auto';
                let heightClass = '';
                if (sectionHeight === 'small') heightClass = 'min-h-[400px] flex flex-col justify-center';
                else if (sectionHeight === 'medium') heightClass = 'min-h-[600px] flex flex-col justify-center';
                else if (sectionHeight === 'large') heightClass = 'min-h-[700px] flex flex-col justify-center';
                else if (sectionHeight === 'fullscreen') heightClass = 'min-h-[100vh] flex flex-col justify-center';

                const wrapperClass = [
                    visibilityClass, 
                    isSectionFluid ? 'w-full' : 'max-w-7xl mx-auto',
                    innerPaddingYClass,
                    innerPaddingXClass,
                    outerMarginYClass,
                    outerMarginXClass,
                    heightClass
                ].filter(Boolean).join(' ');

                const sectionStyle: React.CSSProperties = {};
                if (bgColor) sectionStyle.backgroundColor = bgColor;
                if (textColor) sectionStyle.color = textColor;

                return (
                    <div
                        key={index}
                        className={`${wrapperClass} empty:hidden`}
                        style={Object.keys(sectionStyle).length > 0 ? sectionStyle : undefined}
                    >
                        <Component 
                            data={section.data} 
                            isFluid={isSectionFluid} 
                            sectionBg={bgColor} 
                            sectionTextColor={textColor} 
                            sectionSettings={s} 
                            settings={settings} 
                        />
                    </div>
                );
            })}
        </div>
    );
}
