export default function TextBlock({ data, isFluid }: { data: any; isFluid?: boolean }) {
    if (!data.content) return null;

    return (
        <section className="bg-white">
            <div
                className={`${isFluid ? 'w-full' : 'container mx-auto'} px-4 prose max-w-4xl [&>*:first-child]:mt-0 [&>*:last-child]:mb-0`}
                dangerouslySetInnerHTML={{ __html: data.content }}
            />
        </section>
    );
}
