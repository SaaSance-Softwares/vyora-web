export default function Spacer({ data }: { data: any }) {
    const size = data?.size || 'md';
    const paddingMap: any = {
        sm: 'h-8 md:h-12',
        md: 'h-16 md:h-24',
        lg: 'h-24 md:h-32',
        xl: 'h-32 md:h-48',
    };
    
    const heightClass = paddingMap[size] || paddingMap.md;
    
    return (
        <div className={`w-full ${heightClass}`} />
    );
}
