<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Design Page: {{ $mnpage->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        .builder-section {
            transition: all 0.2s;
        }

        .builder-section:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased h-screen overflow-hidden">
    <div class="h-screen flex flex-col">
        <!-- Top Bar -->
        <header
            class="h-24 bg-white border-b border-gray-100/50 flex items-center justify-between px-12 fixed w-full top-0 z-50 shadow-sm">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.online-store.mnpages.index') }}"
                    class="w-12 h-9 flex items-center justify-center bg-gray-50 text-gray-700 hover:text-black hover:bg-gray-100 rounded-md transition-all group">
                    <svg class="w-5 h-5 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-10 bg-black rounded-md"></div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Design</h1>
                        <p class="text-xs font-bold text-gray-700 mt-0.5">
                            {{ $mnpage->title }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <button type="button" onclick="openAddSectionModal()"
                    class="h-10 flex items-center gap-2 bg-gray-900 text-white px-5 rounded text-xs font-semibold hover:bg-black transition-all shadow focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Section</span>
                </button>

                <div class="flex items-center gap-6">
                    <div id="save-status"
                        class="text-sm font-semibold text-emerald-500 opacity-0 transition-opacity">
                        Saved</div>
                    <button type="button" onclick="publishChanges()"
                        class="h-10 bg-violet-600 text-white px-6 rounded text-xs font-semibold hover:bg-violet-700 transition-all shadow">
                        Publish
                    </button>
                </div>
            </div>
        </header>

        <!-- 2-Column Builder Workspace -->
        <main class="flex-1 mt-24 flex bg-gray-50 overflow-hidden min-h-0" style="height: calc(100vh - 6rem);">
            <!-- Left Sidebar (Builder List) -->
            <div class="w-[500px] h-full bg-white border-r border-gray-200 overflow-y-auto custom-scrollbar flex-shrink-0 p-5 shadow-[10px_0_15px_-3px_rgba(0,0,0,0.03)] z-10 relative">
                <div id="builder-container" class="space-y-12 min-h-full pb-32">
                    <div id="empty-builder-msg" onclick="openAddSectionModal()" class="group py-24 bg-gray-50/50 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center transition-all hover:border-violet-300 hover:bg-violet-50/50 cursor-pointer" >
                        <div
                            class="w-16 h-16 rounded-lg bg-white flex items-center justify-center text-gray-300 group-hover:bg-violet-100 group-hover:text-violet-500 transition-all duration-300 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <h3 class="mt-6 text-sm font-semibold text-gray-700 group-hover:text-violet-600 transition-colors">Start Designing</h3>
                        <p class="mt-1 text-xs text-gray-500">Add sections from the top menu</p>
                    </div>
                </div>
            </div>

            <!-- Right Area (Live Preview) -->
            <div class="flex-1 flex flex-col relative bg-[#f1f2f4] min-w-0">
                <!-- Device Toolbar -->
                <div class="h-9 bg-white border-b border-gray-200 flex items-center justify-center gap-1 shadow-sm relative z-10">
                    <button onclick="setPreviewWidth('100%')" class="px-4 py-1.5 rounded hover:bg-gray-100 text-gray-600 text-xs font-semibold flex items-center gap-2 transition-colors focus:bg-gray-100 focus:text-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Desktop
                    </button>
                    <div class="w-px h-4 bg-gray-300 mx-2"></div>
                    <button onclick="setPreviewWidth('768px')" class="px-4 py-1.5 rounded hover:bg-gray-100 text-gray-600 text-xs font-semibold flex items-center gap-2 transition-colors focus:bg-gray-100 focus:text-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Tablet
                    </button>
                    <div class="w-px h-4 bg-gray-300 mx-2"></div>
                    <button onclick="setPreviewWidth('375px')" class="px-4 py-1.5 rounded hover:bg-gray-100 text-gray-600 text-xs font-semibold flex items-center gap-2 transition-colors focus:bg-gray-100 focus:text-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Mobile
                    </button>
                </div>
                <!-- Iframe Container -->
                <div class="flex-1 overflow-auto flex justify-center p-6 lg:p-5 h-full min-h-0">
                    @php
                        $frontendUrl = url('/');
                        $previewUrl = $mnpage->is_home ? $frontendUrl . '/?preview=true' : $frontendUrl . '/p/' . $mnpage->slug . '?preview=true';
                    @endphp
                    <div id="iframe-wrapper" class="w-full h-full transition-all duration-300 mx-auto" style="max-width: 100%;">
                        <div class="w-full h-full rounded-xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.1)] border border-gray-300 bg-white">
                            <iframe id="live-preview-iframe" src="{{ $previewUrl }}" class="w-full h-full border-0"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Templates -->
    <template id="tpl-hero_slider">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="hero_slider">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Hero Slider</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Full width rotating
                        banner</p>
                </div>
            </div>
            
            

            <div class="mb-5 space-y-2 border-b border-gray-50 pb-6">
                <label class="text-sm font-semibold text-gray-700 ml-1">Image Fit</label>
                <div class="relative group/select">
                    <select class="section-image-fit w-full h-10 bg-white border border-gray-100 rounded-xl px-4 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all appearance-none cursor-pointer">
                        <option value="cover">Fill Container (Cover)</option>
                        <option value="contain">Show Entire Image (Contain)</option>
                        <option value="none">Original Size (None)</option>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="slides-container space-y-5"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-sm font-semibold text-gray-700 hover:border-violet-500 hover:text-violet-600 hover:bg-violet-50 transition-all shadow-sm"
                    onclick="addSlide(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Slide</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-slide-item">
        <div
            class="slide-item bg-white p-5 rounded-md border border-gray-100 relative group/slide transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/slide:opacity-100 transition-all border border-gray-50 z-20"
                onclick="this.closest('.slide-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="space-y-4">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Image
                            URL</label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                class="slide-image flex-1 bg-white border border-gray-100 rounded px-4 py-3 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all outline-none"
                                placeholder="https://...">
                            <label
                                class="cursor-pointer h-9 px-6 flex items-center justify-center bg-violet-600 text-white rounded text-xs font-semibold hover:bg-gray-900 transition-all shadow">
                                Upload
                                <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                            </label>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Link
                            URL</label>
                        <input type="text"
                            class="slide-link w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="/collections/new-arrivals">
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Main
                            Heading</label>
                        <input type="text"
                            class="slide-title w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="New Season Collection">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Sub
                            Heading</label>
                        <input type="text"
                            class="slide-subtitle w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="Explore the latest trends">
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="tpl-product_carousel">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="product_carousel">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Product Carousel</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Automated scrolling
                        product grid</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Section
                        Title</label>
                    <input type="text"
                        class="section-title w-full bg-gray-50 border-none rounded px-5 py-4 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all"
                        placeholder="e.g. Best Sellers">
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm font-semibold text-gray-700 ml-1">Collection</label>
                    <div class="relative">
                        <select
                            class="section-collection w-full bg-gray-50 border-none rounded px-5 py-4 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all appearance-none">
                            <option value="new_arrivals">New Arrivals</option>
                            <option value="best_sellers">Best Sellers</option>
                            <option value="featured">Featured Products</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Display
                        Limit</label>
                    <input type="number"
                        class="section-limit w-full bg-gray-50 border-none rounded px-5 py-4 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all"
                        value="8">
                </div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-text_block">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="text_block">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-violet-50 flex items-center justify-center text-violet-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Text Block</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Rich editor for
                        storytelling</p>
                </div>
            </div>
            <div class="rounded-md border border-gray-100 overflow-hidden shadow-sm">
                <div class="quill-editor" style="height: 400px; border: none;"></div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-image_grid">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="image_grid">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Image Grid</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Multi-column visual
                        gallery</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Section
                        Title</label>
                    <input type="text"
                        class="grid-title w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                        placeholder="Optional Title">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Grid
                        Columns</label>
                    <select
                        class="grid-columns w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                        <option value="2">2 Columns</option>
                        <option value="3">3 Columns</option>
                        <option value="4">4 Columns</option>
                        <option value="5">5 Columns</option>
                        <option value="6">6 Columns</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                        Text</label>
                    <input type="text"
                        class="grid-cta-text w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                        placeholder="e.g. Shop Now">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                        Link</label>
                    <input type="text"
                        class="grid-cta-link w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                        placeholder="/collections/all">
                </div>
            </div>

            <div class="space-y-5">
                <div class="grid-items-container space-y-5"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm"
                    onclick="addGridItem(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Grid Image</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-image_grid_item">
        <div
            class="grid-item bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.grid-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="space-y-4">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Image
                            URL</label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                class="item-image flex-1 bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                placeholder="https://...">
                            <label
                                class="cursor-pointer h-9 px-6 flex items-center justify-center bg-gray-800 text-white rounded text-sm font-semibold hover:bg-gray-900 transition-all shadow shadow-black/5">
                                Upload
                                <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                            </label>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Link
                            URL</label>
                        <input type="text"
                            class="item-link w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="Target Link">
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Alt
                            Text</label>
                        <input type="text"
                            class="item-alt w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="SEO Description">
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                                Text</label>
                            <input type="text"
                                class="item-cta-text w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                placeholder="Shop Now">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-sm font-semibold text-gray-700 ml-1">Target</label>
                            <select
                                class="item-target w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="tpl-image_banner">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="image_banner">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Image Banner</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Standalone
                        high-impact visual</p>
                </div>
            </div>


            <div class="space-y-4">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Image
                            URL</label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                class="banner-image flex-1 bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                placeholder="https://...">
                            <label
                                class="cursor-pointer h-9 px-6 flex items-center justify-center bg-violet-600 text-white rounded text-xs font-semibold hover:bg-gray-900 transition-all shadow">
                                Upload
                                <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                            </label>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Object
                            Fit</label>
                        <div class="relative group/select">
                            <select class="banner-fit w-full h-10 bg-white border border-gray-100 rounded-xl px-4 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all appearance-none cursor-pointer">
                                <option value="cover">Fill Container (Cover)</option>
                                <option value="contain">Show Entire Image (Contain)</option>
                                <option value="auto">Original Size (None)</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Banner Text
                            Overlay</label>
                        <textarea
                            class="banner-text w-full bg-gray-50 border-none rounded px-4 py-3 text-sm font-bold text-gray-900 outline-none h-[120px]"
                            placeholder="Optional text associated with banner"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Text
                            Position</label>
                        <select
                            class="banner-text-position w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                            <option value="center">Center</option>
                            <option value="bottom">Bottom</option>
                            <option value="top">Top</option>
                            <option value="below">Below Image</option>
                        </select>
                    </div>
                </div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-horizontal_scroll_cards">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="horizontal_scroll_cards">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-teal-50 flex items-center justify-center text-teal-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Horizontal Scroll Cards</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Swipeable custom
                        card gallery</p>
                </div>
            </div>
            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Section
                        Title</label>
                    <input type="text"
                        class="vscroll-title w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                        placeholder="e.g. Featured Brands">
                </div>
            </div>
            <div class="space-y-5">
                <div class="vscroll-cards-container space-y-5"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-teal-500 hover:text-teal-600 hover:bg-teal-50 transition-all shadow-sm"
                    onclick="addScrollCard(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Card</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-horizontal_scroll_card_item">
        <div
            class="vscroll-card bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.vscroll-card').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="space-y-4">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Image
                            URL</label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                class="card-image flex-1 bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                placeholder="https://...">
                            <label
                                class="cursor-pointer h-9 px-6 flex items-center justify-center bg-gray-800 text-white rounded text-sm font-semibold hover:bg-gray-900 transition-all shadow shadow-black/5">
                                Upload
                                <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Headline</label>
                        <input type="text"
                            class="card-headline w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none">
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                                Text</label>
                            <input type="text"
                                class="card-cta-text w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                                Link</label>
                            <input type="text"
                                class="card-cta-link w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none">
                        </div>
                    </div>
                </div>
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <label
                        class="text-sm font-semibold text-gray-700 ml-1">Paragraph</label>
                    <textarea
                        class="card-paragraph w-full bg-white border border-gray-100 rounded px-4 py-3 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all outline-none h-20"></textarea>
                </div>
            </div>
        </div>
    </template>

    <template id="tpl-product_horizontal_scroll">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="product_horizontal_scroll">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Product Scroll</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Selected product
                        showcase</p>
                </div>
            </div>
            <div class="mb-6">
                <label class="text-sm font-semibold text-gray-700 ml-1">Section
                    Title</label>
                <input type="text"
                    class="section-title w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                    placeholder="e.g. Hand-picked products">
            </div>
            <div class="space-y-5">
                <div class="product-scroll-container space-y-4"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 transition-all shadow-sm"
                    onclick="addScrollProduct(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Product</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-image_product_carousel">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="image_product_carousel">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Image & Product Carousel</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Split layout visual
                        showcase</p>
                </div>
            </div>
            <div class="space-y-4 gap-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Left Block
                            Image</label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                class="carousel-image flex-1 bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                placeholder="https://...">
                            <label
                                class="cursor-pointer h-9 px-6 flex items-center justify-center bg-violet-600 text-white rounded text-xs font-semibold hover:bg-gray-900 transition-all shadow">
                                Upload
                                <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="space-y-4 border-l border-gray-50 pl-12">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Right Block
                        Products</label>
                    <div class="product-scroll-container space-y-4 mb-6"></div>
                    <button type="button"
                        class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-black hover:text-black hover:bg-gray-50 transition-all shadow-sm"
                        onclick="addScrollProduct(this)">
                        <svg class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span>Add Product</span>
                    </button>
                </div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-product_scroll_item">
        <div
            class="product-item bg-gray-50 p-6 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5 flex items-center gap-6">
            <button type="button"
                class="absolute -top-2 -right-2 w-8 h-8 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20"
                onclick="this.closest('.product-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="flex-1 space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Select
                    Product</label>
                <div class="relative">
                    <select
                        class="item-product-slug w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                        <option value="">-- Choose a Product --</option>
                        @if(isset($products) && $products->count() > 0)
                            @foreach($products as $product)
                                <option value="{{ $product->slug }}">{{ $product->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ===================== NEW SECTION TEMPLATES ===================== --}}

    <template id="tpl-announcement_marquee">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="announcement_marquee">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Announcement Marquee</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Scrolling text
                        ticker</p>
                </div>
            </div>
            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Scroll
                        Speed</label>
                    <div class="relative">
                        <select
                            class="marquee-speed w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                            <option value="slow">Slow</option>
                            <option value="medium" selected>Medium</option>
                            <option value="fast">Fast</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Background
                        Color</label>
                    <input type="color"
                        class="marquee-bg-color h-[46px] w-full rounded border-none bg-gray-50 p-1 cursor-pointer"
                        value="#000000">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Text
                        Color</label>
                    <input type="color"
                        class="marquee-text-color h-[46px] w-full rounded border-none bg-gray-50 p-1 cursor-pointer"
                        value="#ffffff">
                </div>
            </div>
            <div class="space-y-5">
                <div class="marquee-items-container space-y-4"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-amber-500 hover:text-amber-600 hover:bg-amber-50 transition-all shadow-sm"
                    onclick="addMarqueeItem(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Announcement</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-marquee-item">
        <div
            class="marquee-item bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5 flex items-center gap-5">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.marquee-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="flex-1 space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Scrolling
                    Text</label>
                <input type="text"
                    class="item-marquee-text w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="e.g. Free Shipping on orders over $50">
            </div>
            <div class="flex-1 space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Link
                    (Optional)</label>
                <input type="text"
                    class="item-marquee-link w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="/collections/all">
            </div>
        </div>
    </template>

    <template id="tpl-feature_highlights">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="feature_highlights">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Feature Highlights</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Trust signals & key
                        benefits</p>
                </div>
            </div>

            <div class="mb-6">
                <label class="text-sm font-semibold text-gray-700 ml-1">Text Color</label>
                <div class="flex items-center gap-4 mt-2">
                    <input type="color"
                        class="hl-text-color h-9 w-24 rounded border-none bg-gray-50 p-1 cursor-pointer"
                        value="#000000">
                    <span class="text-sm font-bold text-gray-700">Pick a theme
                        color</span>
                </div>
            </div>

            <div class="space-y-5">
                <div class="highlights-items-container space-y-4"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 transition-all shadow-sm"
                    onclick="addHighlightItem(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Highlight</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-highlight-item">
        <div
            class="highlight-item bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5 space-y-4">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.highlight-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Icon
                    (Emoji/SVG)</label>
                <input type="text"
                    class="item-hl-icon w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="🚚">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Label</label>
                <input type="text"
                    class="item-hl-label w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="Free Shipping">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Description</label>
                <input type="text"
                    class="item-hl-desc w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="On orders over $50">
            </div>
        </div>
    </template>

    <template id="tpl-category_grid">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="category_grid">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Category Grid</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Visual category
                        navigation</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Section
                        Title</label>
                    <input type="text"
                        class="catgrid-title w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="Shop by Category">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Columns</label>
                    <div class="relative">
                        <select
                            class="catgrid-columns w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                            <option value="2">2 Columns</option>
                            <option value="3">3 Columns</option>
                            <option value="4" selected>4 Columns</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="catgrid-items-container space-y-4"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-sm font-semibold text-gray-700 hover:border-violet-500 hover:text-violet-600 hover:bg-violet-50 transition-all shadow-sm"
                    onclick="addCategoryItem(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Category</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-category-item">
        <div
            class="category-item bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5 space-y-4 items-end">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.category-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Image URL</label>
                <div class="flex items-center gap-2">
                    <input type="text"
                        class="item-cat-image flex-1 bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                        placeholder="https://...">
                    <label
                        class="cursor-pointer h-9 px-6 flex items-center justify-center bg-gray-800 text-white rounded text-sm font-semibold hover:bg-gray-900 transition-all shadow shadow-black/5">
                        Upload
                        <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                    </label>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Category
                    Name</label>
                <input type="text"
                    class="item-cat-name w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="Tops">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Link</label>
                <input type="text"
                    class="item-cat-link w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                    placeholder="/collections/tops">
            </div>
        </div>
    </template>

    <template id="tpl-split_banner">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="split_banner">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-pink-50 flex items-center justify-center text-pink-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-900">Split Banner</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Dual column content
                        block</p>
                </div>
            </div>

            <div class="space-y-4 gap-6">
                <div class="space-y-5">
                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Visual
                            Content</label>
                        <div class="grid grid-cols-1 gap-6 bg-gray-50 p-5 rounded-md">
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-semibold text-gray-700 ml-1">Image
                                    URL</label>
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                        class="split-image flex-1 bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                        placeholder="https://...">
                                    <label
                                        class="cursor-pointer h-9 px-6 flex items-center justify-center bg-gray-800 text-white rounded text-sm font-semibold hover:bg-gray-900 transition-all shadow shadow-black/5">
                                        Upload
                                        <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">Image
                                        Side</label>
                                    <select
                                        class="split-image-side w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">Object
                                        Fit</label>
                                    <select
                                        class="split-object-fit w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none cursor-pointer">
                                        <option value="cover">Fill Container (Cover)</option>
                                        <option value="contain">Show Entire Image (Contain)</option>
                                        <option value="none">Original Size (None)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Editorial
                            Content</label>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">Badge</label>
                                    <input type="text"
                                        class="split-badge w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                        placeholder="New Arrival">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">BG
                                        Color</label>
                                    <input type="color"
                                        class="split-text-bg h-[46px] w-full rounded border-none bg-gray-50 p-1 cursor-pointer"
                                        value="#ffffff">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-semibold text-gray-700 ml-1">Headline</label>
                                <input type="text"
                                    class="split-title w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                    placeholder="Bold Headline">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-semibold text-gray-700 ml-1">Subtitle</label>
                                <textarea
                                    class="split-subtitle w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none h-20"
                                    placeholder="Subheading text..."></textarea>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">CTA
                                        Text</label>
                                    <input type="text"
                                        class="split-cta-text w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                        placeholder="Shop Now">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-semibold text-gray-700 ml-1">CTA
                                        Link</label>
                                    <input type="text"
                                        class="split-cta-link w-full bg-gray-50 border-none rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                        placeholder="/collections/new">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-newsletter_signup">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="newsletter_signup">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-violet-50 flex items-center justify-center text-violet-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900">Newsletter Signup</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Email collection &
                        growth</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Headline</label>
                        <input type="text"
                            class="nl-title w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                            placeholder="Join the List">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Background
                            Style</label>
                        <div class="relative">
                            <select
                                class="nl-bg-style w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                                <option value="light">Light Minimal</option>
                                <option value="dark">Dark Premium</option>
                                <option value="image">Custom Image</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Description</label>
                        <textarea
                            class="nl-description w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none h-[115px]"
                            placeholder="Get early access to drops and exclusive discounts."></textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Input
                        Placeholder</label>
                    <input type="text"
                        class="nl-placeholder w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="Enter your email">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Button
                        Text</label>
                    <input type="text"
                        class="nl-button-text w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="Subscribe">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Background Image
                    (Optional)</label>
                <div class="flex items-center gap-2">
                    <input type="text"
                        class="nl-bg-image flex-1 bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="https://...">
                    <label
                        class="cursor-pointer h-10 px-8 flex items-center justify-center bg-gray-800 text-white rounded text-sm font-semibold hover:bg-gray-800 transition-all shadow shadow-black/5">
                        Upload Image
                        <input type="file" class="hidden" accept="image/*" onchange="uploadImage(this)">
                    </label>
                </div>
            </div>

            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-video_banner">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="video_banner">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900">Video Banner</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Cinematic
                        background experience</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="md:col-span-2 space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Video Source
                        (MP4 URL)</label>
                    <input type="text"
                        class="vid-url w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="https://...video.mp4">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Section
                        Height</label>
                    <div class="relative">
                        <select
                            class="vid-height w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                            <option value="small">Small (40vh)</option>
                            <option value="medium" selected>Medium (60vh)</option>
                            <option value="large">Large (80vh)</option>
                            <option value="fullscreen">Fullscreen</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Overlay
                            Branding</label>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-2">
                                <label
                                    class="text-[8px] font-bold text-gray-700">Title</label>
                                <input type="text"
                                    class="vid-title w-full bg-gray-50 border-none rounded px-5 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                    placeholder="Bold Headline">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[8px] font-bold text-gray-700">Subtitle</label>
                                <input type="text"
                                    class="vid-subtitle w-full bg-gray-50 border-none rounded px-5 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                    placeholder="Supporting text">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Call to
                            Action</label>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-[8px] font-bold text-gray-700">Button
                                    Text</label>
                                <input type="text"
                                    class="vid-cta-text w-full bg-gray-50 border-none rounded px-5 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                    placeholder="Shop Now">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[8px] font-bold text-gray-700">URL
                                    Link</label>
                                <input type="text"
                                    class="vid-cta-link w-full bg-gray-50 border-none rounded px-5 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                                    placeholder="/collections/all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 ml-1">Visual
                    Settings</label>
                <div class="bg-gray-50 p-6 rounded-md flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700">Dark Overlay
                        Opacity</span>
                    <div class="flex items-center gap-4 w-1/3">
                        <input type="number"
                            class="vid-overlay w-full bg-white border border-gray-100 rounded px-4 py-2 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            value="40" min="0" max="100">
                        <span class="text-sm font-bold text-gray-700">%</span>
                    </div>
                </div>
            </div>

            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-testimonials_slider">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="testimonials_slider">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-orange-50 flex items-center justify-center text-orange-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900">Testimonials Slider</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Community trust &
                        reviews</p>
                </div>
            </div>

            <div class="mb-6">
                <label class="text-sm font-semibold text-gray-700 ml-1">Section
                    Title</label>
                <input type="text"
                    class="testimonials-title w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                    placeholder="What Our Customers Say">
            </div>

            <div class="space-y-5">
                <div class="testimonials-items-container space-y-4"></div>
                <button type="button"
                    class="group h-10 w-full flex items-center justify-center gap-3 border-2 border-dashed border-gray-100 rounded-md text-xs font-semibold text-gray-700 hover:border-orange-500 hover:text-orange-600 hover:bg-orange-50 transition-all shadow-sm"
                    onclick="addTestimonialItem(this)">
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Review</span>
                </button>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-testimonial-item">
        <div
            class="testimonial-item bg-white p-5 rounded-md border border-gray-100 relative group/item transition-all duration-300 hover:bg-white hover:shadow hover:shadow-black/5">
            <button type="button"
                class="absolute -top-3 -right-3 w-10 h-10 bg-white shadow shadow-black/5 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition-all border border-gray-50 z-20 shadow-sm"
                onclick="this.closest('.testimonial-item').remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="grid grid-cols-1 gap-5">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Quote</label>
                    <textarea
                        class="item-t-quote w-full bg-white border border-gray-100 rounded px-4 py-3 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all outline-none h-24"
                        placeholder="Amazing product, love it!"></textarea>
                </div>
                <div class="space-y-4 gap-6">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Name</label>
                        <input type="text"
                            class="item-t-name w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="Jane Doe">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Role /
                            Location</label>
                        <input type="text"
                            class="item-t-role w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="Verified Buyer">
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Rating</label>
                        <div class="relative">
                            <select
                                class="item-t-rating w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none appearance-none">
                                <option value="5" selected>5 ★ Rating</option>
                                <option value="4">4 ★ Rating</option>
                                <option value="3">3 ★ Rating</option>
                                <option value="2">2 ★ Rating</option>
                                <option value="1">1 ★ Rating</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Avatar URL
                            (optional)</label>
                        <input type="text"
                            class="item-t-avatar w-full bg-white border border-gray-100 rounded px-4 py-3 text-xs font-bold text-gray-900 focus:ring-2 focus:ring-violet-500/20 transition-all outline-none"
                            placeholder="Avatar URL">
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="tpl-countdown_timer">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="countdown_timer">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-cyan-50 flex items-center justify-center text-cyan-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900">Countdown Timer</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Urgency & sale
                        highlights</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-semibold text-gray-700 ml-1">Headline</label>
                        <input type="text"
                            class="cd-title w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                            placeholder="Sale Ends In">
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">End Date &
                            Time</label>
                        <input type="datetime-local"
                            class="cd-end-date w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none">
                    </div>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label
                        class="text-sm font-semibold text-gray-700 ml-1">Description</label>
                    <textarea
                        class="cd-description w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none h-20"
                        placeholder="Don't miss out on these limited prices"></textarea>
                </div>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                        Text</label>
                    <input type="text"
                        class="cd-cta-text w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="Shop the Sale">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">CTA
                        Link</label>
                    <input type="text"
                        class="cd-cta-link w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none"
                        placeholder="/collections/sale">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Style</label>
                    <div class="relative">
                        <select
                            class="cd-bg-style w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                            <option value="light">Light Minimal</option>
                            <option value="dark">Dark High Contrast</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <template id="tpl-spacer">
        <div class="builder-section bg-white rounded-lg border border-gray-100 p-6 relative group transition-all duration-500 hover:shadow-sm"
            data-type="spacer">
            @include('admin.pages.partials.builder_controls')
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 pb-8">
                <div class="w-10 h-10 rounded bg-gray-50 flex items-center justify-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8h16M4 16h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900">Spacing</h3>
                    <p class="text-sm font-bold text-gray-700 mt-0.5">Vertical empty space</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 ml-1">Size</label>
                    <div class="relative">
                        <select class="spacer-size w-full bg-gray-50 border-none rounded px-5 py-4 text-sm font-bold text-gray-900 outline-none appearance-none">
                            <option value="sm">Small</option>
                            <option value="md" selected>Medium</option>
                            <option value="lg">Large</option>
                            <option value="xl">Extra Large</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.pages.partials.builder_section_settings')
        </div>
    </template>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        // Use existing Create/Edit page JS logic here, adapted for standalone
        const container = document.getElementById('builder-container');
        const emptyMsg = document.getElementById('empty-builder-msg');

        // Setup Builder logic helper functions first

        function addSection(type, data = null, settings = null) {
            emptyMsg.style.display = 'none';
            const tpl = document.getElementById('tpl-' + type);
            if (!tpl) return;

            const clone = tpl.content.cloneNode(true);
            container.appendChild(clone);
            const lastSection = container.lastElementChild;

            if (type === 'text_block') {
                const editorEl = lastSection.querySelector('.quill-editor');
                const quill = new Quill(editorEl, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'font': [] }, { 'size': [] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', '', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'script': 'sub' }, { 'script': 'super' }],
                            ['blockquote', 'code-block'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }, { 'align': [] }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });
                if (data && data.content) {
                    quill.root.innerHTML = data.content;
                }
                editorEl._quill = quill;
                quill.on('text-change', triggerAutoSave);
            }

            if (type === 'hero_slider') {
                if (data && data.image_fit) {
                    const fitSelect = lastSection.querySelector('.section-image-fit');
                    if (fitSelect) fitSelect.value = data.image_fit;
                }
                if (data && data.section_width) {
                    const el = lastSection.querySelector('.section-width');
                    if (el) el.value = data.section_width;
                }
                if (data && data.section_height) {
                    const el = lastSection.querySelector('.section-height');
                    if (el) el.value = data.section_height;
                }
                if (data && data.slides) {
                    data.slides.forEach(slide => addSlide(lastSection.querySelector('button[onclick="addSlide(this)"]'), slide));
                } else {
                    addSlide(lastSection.querySelector('button[onclick="addSlide(this)"]'));
                }
            }

            if (type === 'product_carousel') {
                if (data) {
                    lastSection.querySelector('.section-title').value = data.title || '';
                    lastSection.querySelector('.section-collection').value = data.collection || 'new_arrivals';
                    lastSection.querySelector('.section-limit').value = data.limit || 8;
                }
            }

            if (type === 'product_horizontal_scroll') {
                if (data) {
                    lastSection.querySelector('.section-title').value = data.title || '';
                    if (data.product_slugs) {
                        data.product_slugs.forEach(slug => addScrollProduct(lastSection.querySelector('button[onclick="addScrollProduct(this)"]'), slug));
                    }
                } else {
                    addScrollProduct(lastSection.querySelector('button[onclick="addScrollProduct(this)"]'));
                }
            }

            if (type === 'image_product_carousel') {
                if (data) {
                    lastSection.querySelector('.carousel-image').value = data.image || '';
                    if (lastSection.querySelector('.carousel-image-fit')) {
                        lastSection.querySelector('.carousel-image-fit').value = data.object_fit || 'cover';
                    }
                    if (data.product_slugs) {
                        data.product_slugs.forEach(slug => addScrollProduct(lastSection.querySelector('button[onclick="addScrollProduct(this)"]'), slug));
                    }
                } else {
                    addScrollProduct(lastSection.querySelector('button[onclick="addScrollProduct(this)"]'));
                }
            }

            if (type === 'image_grid') {
                if (data) {
                    lastSection.querySelector('.grid-title').value = data.title || '';
                    lastSection.querySelector('.grid-columns').value = data.columns || '4';
                    lastSection.querySelector('.grid-text').value = data.text || '';
                    lastSection.querySelector('.grid-cta-text').value = data.cta_text || '';
                    lastSection.querySelector('.grid-cta-link').value = data.cta_link || '';
                    if (lastSection.querySelector('.grid-hover-animation')) lastSection.querySelector('.grid-hover-animation').value = data.hover_animation || 'zoom';
                    if (data.images) {
                        data.images.forEach(img => addGridItem(lastSection.querySelector('button[onclick="addGridItem(this)"]'), img));
                    }
                } else {
                    addGridItem(lastSection.querySelector('button[onclick="addGridItem(this)"]'));
                }
            }

            if (type === 'image_banner') {
                if (data) {
                    if (lastSection.querySelector('.section-width') && data.section_width) {
                        lastSection.querySelector('.section-width').value = data.section_width;
                    }
                    if (lastSection.querySelector('.section-height') && data.section_height) {
                        lastSection.querySelector('.section-height').value = data.section_height;
                    }
                    lastSection.querySelector('.banner-image').value = data.image || '';
                    lastSection.querySelector('.banner-fit').value = data.object_fit || 'cover';
                    lastSection.querySelector('.banner-text').value = data.text || '';
                    lastSection.querySelector('.banner-text-position').value = data.text_position || 'center';
                }
            }

            if (type === 'horizontal_scroll_cards') {
                if (data) {
                    lastSection.querySelector('.vscroll-title').value = data.title || '';
                    if (lastSection.querySelector('.hscroll-hover-animation')) {
                        lastSection.querySelector('.hscroll-hover-animation').value = data.hover_animation || 'zoom';
                    }
                    if (data.cards) {
                        data.cards.forEach(card => addScrollCard(lastSection.querySelector('button[onclick="addScrollCard(this)"]'), card));
                    }
                } else {
                    addScrollCard(lastSection.querySelector('button[onclick="addScrollCard(this)"]'));
                }
            }

            if (type === 'announcement_marquee') {
                if (data) {
                    lastSection.querySelector('.marquee-speed').value = data.speed || 'medium';
                    lastSection.querySelector('.marquee-bg-color').value = data.bg_color || '#000000';
                    lastSection.querySelector('.marquee-text-color').value = data.text_color || '#ffffff';
                    if (data.items) {
                        data.items.forEach(item => addMarqueeItem(lastSection.querySelector('button[onclick="addMarqueeItem(this)"]'), item));
                    }
                } else {
                    addMarqueeItem(lastSection.querySelector('button[onclick="addMarqueeItem(this)"]'));
                }
            }

            if (type === 'feature_highlights') {
                if (data) {
                    lastSection.querySelector('.hl-text-color').value = data.text_color || '#000000';
                }
                if (data && data.items) {
                    data.items.forEach(item => addHighlightItem(lastSection.querySelector('button[onclick="addHighlightItem(this)"]'), item));
                } else {
                    addHighlightItem(lastSection.querySelector('button[onclick="addHighlightItem(this)"]'));
                }
            }

            if (type === 'category_grid') {
                if (data) {
                    lastSection.querySelector('.catgrid-title').value = data.title || '';
                    lastSection.querySelector('.catgrid-columns').value = data.columns || '4';
                    if (data.categories) {
                        data.categories.forEach(item => addCategoryItem(lastSection.querySelector('button[onclick="addCategoryItem(this)"]'), item));
                    }
                } else {
                    addCategoryItem(lastSection.querySelector('button[onclick="addCategoryItem(this)"]'));
                }
            }

            if (type === 'split_banner' && data) {
                lastSection.querySelector('.split-image').value = data.image || '';
                lastSection.querySelector('.split-image-side').value = data.image_side || 'left';
                lastSection.querySelector('.split-object-fit').value = data.object_fit || 'cover';
                lastSection.querySelector('.split-badge').value = data.badge || '';
                lastSection.querySelector('.split-title').value = data.title || '';
                lastSection.querySelector('.split-text-bg').value = data.text_bg || '#ffffff';
                lastSection.querySelector('.split-subtitle').value = data.subtitle || '';
                lastSection.querySelector('.split-cta-text').value = data.cta_text || '';
                lastSection.querySelector('.split-cta-link').value = data.cta_link || '';
            }

            if (type === 'newsletter_signup' && data) {
                lastSection.querySelector('.nl-title').value = data.title || '';
                lastSection.querySelector('.nl-bg-style').value = data.bg_style || 'light';
                lastSection.querySelector('.nl-description').value = data.description || '';
                lastSection.querySelector('.nl-placeholder').value = data.placeholder || '';
                lastSection.querySelector('.nl-button-text').value = data.button_text || '';
                lastSection.querySelector('.nl-bg-image').value = data.bg_image || '';
            }

            if (type === 'video_banner' && data) {
                lastSection.querySelector('.vid-url').value = data.video_url || '';
                lastSection.querySelector('.vid-height').value = data.height || 'medium';
                lastSection.querySelector('.vid-overlay').value = data.overlay_opacity || '40';
                lastSection.querySelector('.vid-title').value = data.title || '';
                lastSection.querySelector('.vid-subtitle').value = data.subtitle || '';
                lastSection.querySelector('.vid-cta-text').value = data.cta_text || '';
                lastSection.querySelector('.vid-cta-link').value = data.cta_link || '';
            }

            if (type === 'testimonials_slider') {
                if (data) {
                    lastSection.querySelector('.testimonials-title').value = data.title || '';
                    if (data.testimonials) {
                        data.testimonials.forEach(item => addTestimonialItem(lastSection.querySelector('button[onclick="addTestimonialItem(this)"]'), item));
                    }
                } else {
                    addTestimonialItem(lastSection.querySelector('button[onclick="addTestimonialItem(this)"]'));
                }
            }

            if (type === 'countdown_timer' && data) {
                lastSection.querySelector('.cd-title').value = data.title || '';
                lastSection.querySelector('.cd-end-date').value = data.end_date || '';
                lastSection.querySelector('.cd-description').value = data.description || '';
                lastSection.querySelector('.cd-cta-text').value = data.cta_text || '';
                lastSection.querySelector('.cd-cta-link').value = data.cta_link || '';
                lastSection.querySelector('.cd-bg-style').value = data.bg_style || 'light';
            }

            if (type === 'spacer' && data) {
                lastSection.querySelector('.spacer-size').value = data.size || 'md';
            }

            if (settings) {
                if (settings.bg_color) {
                    const bgCheck = lastSection.querySelector('.section-has-bg');
                    const bgPick = lastSection.querySelector('.section-bg-color');
                    if (bgCheck && bgPick) {
                        bgCheck.checked = true;
                        bgPick.value = settings.bg_color;
                        const wrapper = bgPick.closest('.section-bg-picker-wrapper');
                        if(wrapper) wrapper.classList.remove('hidden');
                        else bgPick.classList.remove('hidden');
                    }
                }
                if (settings.text_color) {
                    const txtCheck = lastSection.querySelector('.section-has-text-color');
                    const txtPick = lastSection.querySelector('.section-text-color');
                    if (txtCheck && txtPick) {
                        txtCheck.checked = true;
                        txtPick.value = settings.text_color;
                        const wrapper = txtPick.closest('.section-text-color-picker-wrapper');
                        if(wrapper) wrapper.classList.remove('hidden');
                    }
                }
                let py = settings.inner_padding_y !== undefined ? settings.inner_padding_y : settings.inner_padding;
                if (py !== undefined && lastSection.querySelector('.section-inner-padding-y')) {
                    lastSection.querySelector('.section-inner-padding-y').value = py;
                }
                let px = settings.inner_padding_x !== undefined ? settings.inner_padding_x : settings.inner_padding;
                if (px !== undefined && lastSection.querySelector('.section-inner-padding-x')) {
                    lastSection.querySelector('.section-inner-padding-x').value = px;
                }
                let my = settings.outer_margin_y !== undefined ? settings.outer_margin_y : settings.outer_padding;
                if (my !== undefined && lastSection.querySelector('.section-outer-margin-y')) {
                    lastSection.querySelector('.section-outer-margin-y').value = my;
                }
                let mx = settings.outer_margin_x !== undefined ? settings.outer_margin_x : settings.outer_padding;
                if (mx !== undefined && lastSection.querySelector('.section-outer-margin-x')) {
                    lastSection.querySelector('.section-outer-margin-x').value = mx;
                }
                if (settings.section_width !== undefined && lastSection.querySelector('.global-section-width')) {
                    lastSection.querySelector('.global-section-width').value = settings.section_width;
                }
                if (settings.section_height !== undefined && lastSection.querySelector('.global-section-height')) {
                    lastSection.querySelector('.global-section-height').value = settings.section_height;
                }
                if (settings.show_mobile !== undefined && lastSection.querySelector('.section-show-mobile')) {
                    lastSection.querySelector('.section-show-mobile').checked = settings.show_mobile;
                }
                if (settings.show_desktop !== undefined && lastSection.querySelector('.section-show-desktop')) {
                    lastSection.querySelector('.section-show-desktop').checked = settings.show_desktop;
                }
            }
            
            setupSectionCollapsible(lastSection);
        }

        function setupSectionCollapsible(section) {
            const header = section.querySelector('.flex.items-center.gap-4.mb-6.border-b');
            if (header) {
                header.classList.add('cursor-pointer', 'hover:bg-gray-50', 'transition-colors', '-mx-6', 'px-6', '-mt-6', 'pt-6', 'rounded-t-lg');
                
                const chevron = document.createElement('div');
                chevron.className = 'ml-auto text-gray-400 transition-transform duration-300 transform rotate-180';
                chevron.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
                header.appendChild(chevron);

                const body = document.createElement('div');
                body.className = 'section-body transition-all duration-300 pt-2';
                
                let sibling = header.nextElementSibling;
                while (sibling) {
                    const next = sibling.nextElementSibling;
                    body.appendChild(sibling);
                    sibling = next;
                }
                section.appendChild(body);

                header.addEventListener('click', (e) => {
                    if(e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;
                    body.classList.toggle('hidden');
                    if (body.classList.contains('hidden')) {
                        chevron.classList.remove('rotate-180');
                        header.classList.remove('border-b', 'border-gray-50', 'pb-8', 'mb-6');
                        header.classList.add('pb-6', 'mb-2');
                    } else {
                        chevron.classList.add('rotate-180');
                        header.classList.add('border-b', 'border-gray-50', 'pb-8', 'mb-6');
                        header.classList.remove('pb-6', 'mb-2');
                    }
                });
            }

            section.addEventListener('click', (e) => {
                document.querySelectorAll('.builder-section').forEach(el => {
                    el.classList.remove('ring-2', 'ring-violet-500', 'border-violet-500', 'shadow-md');
                });
                section.classList.add('ring-2', 'ring-violet-500', 'border-violet-500', 'shadow-md');
            });
        }

        function addScrollProduct(btn, slug = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-product_scroll_item');
            const clone = tpl.content.cloneNode(true);
            if (slug) {
                clone.querySelector('.item-product-slug').value = slug;
            }
            container.appendChild(clone);
        }

        function addSlide(btn, slideData = null) {
            const slidesContainer = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-slide-item');
            const clone = tpl.content.cloneNode(true);

            if (slideData) {
                const el = clone.querySelector('.slide-item');
                if (el.querySelector('.slide-image')) el.querySelector('.slide-image').value = slideData.image || '';
                if (el.querySelector('.slide-link')) el.querySelector('.slide-link').value = slideData.link || '';
                if (el.querySelector('.slide-title')) el.querySelector('.slide-title').value = slideData.title || '';
                if (el.querySelector('.slide-subtitle')) el.querySelector('.slide-subtitle').value = slideData.subtitle || '';
            }
            slidesContainer.appendChild(clone);
        }

        function addGridItem(btn, itemData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-image_grid_item');
            const clone = tpl.content.cloneNode(true);
            if (itemData) {
                if (clone.querySelector('.item-image')) clone.querySelector('.item-image').value = itemData.image || '';
                if (clone.querySelector('.item-link')) clone.querySelector('.item-link').value = itemData.link || '';
                if (clone.querySelector('.item-alt')) clone.querySelector('.item-alt').value = itemData.alt || '';
                if (clone.querySelector('.item-cta-text')) clone.querySelector('.item-cta-text').value = itemData.cta_text || '';
                if (clone.querySelector('.item-cta-style')) clone.querySelector('.item-cta-style').value = itemData.cta_style || 'pill_overlay';
                if (clone.querySelector('.item-target')) clone.querySelector('.item-target').value = itemData.target || '_self';
            }
            container.appendChild(clone);
        }

        function addScrollCard(btn, cardData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-horizontal_scroll_card_item');
            const clone = tpl.content.cloneNode(true);
            if (cardData) {
                clone.querySelector('.card-image').value = cardData.image || '';
                clone.querySelector('.card-headline').value = cardData.headline || '';
                clone.querySelector('.card-paragraph').value = cardData.paragraph || '';
                clone.querySelector('.card-cta-text').value = cardData.cta_text || '';
                clone.querySelector('.card-cta-link').value = cardData.cta_link || '';
            }
            container.appendChild(clone);
        }

        function addMarqueeItem(btn, itemData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-marquee-item');
            if (!tpl) return;
            const clone = tpl.content.cloneNode(true);
            if (itemData) {
                clone.querySelector('.item-marquee-text').value = itemData.text || '';
                clone.querySelector('.item-marquee-link').value = itemData.link || '';
            }
            container.appendChild(clone);
        }

        function addHighlightItem(btn, itemData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-highlight-item');
            if (!tpl) return;
            const clone = tpl.content.cloneNode(true);
            if (itemData) {
                clone.querySelector('.item-hl-icon').value = itemData.icon || '';
                clone.querySelector('.item-hl-label').value = itemData.label || '';
                clone.querySelector('.item-hl-desc').value = itemData.desc || '';
            }
            container.appendChild(clone);
        }

        function addCategoryItem(btn, itemData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-category-item');
            if (!tpl) return;
            const clone = tpl.content.cloneNode(true);
            if (itemData) {
                clone.querySelector('.item-cat-image').value = itemData.image || '';
                clone.querySelector('.item-cat-name').value = itemData.name || '';
                clone.querySelector('.item-cat-link').value = itemData.link || '';
            }
            container.appendChild(clone);
        }

        function addTestimonialItem(btn, itemData = null) {
            const container = btn.previousElementSibling;
            const tpl = document.getElementById('tpl-testimonial-item');
            if (!tpl) return;
            const clone = tpl.content.cloneNode(true);
            if (itemData) {
                clone.querySelector('.item-t-quote').value = itemData.quote || '';
                clone.querySelector('.item-t-name').value = itemData.name || '';
                clone.querySelector('.item-t-role').value = itemData.role || '';
                clone.querySelector('.item-t-rating').value = itemData.rating || '5';
                clone.querySelector('.item-t-avatar').value = itemData.avatar || '';
            }
            container.appendChild(clone);
        }

        function toggleSectionSettings(btn) {
            const body = btn.nextElementSibling;
            const chevron = btn.querySelector('.section-settings-chevron');
            if (body.classList.contains('hidden')) {
                body.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                body.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function toggleBgPicker(checkbox) {
            const wrapper = checkbox.closest('.space-y-3').querySelector('.section-bg-picker-wrapper');
            if (checkbox.checked) {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
            triggerAutoSave();
        }

        function toggleTextColorPicker(checkbox) {
            const wrapper = checkbox.closest('.space-y-3').querySelector('.section-text-color-picker-wrapper');
            if (checkbox.checked) {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
            triggerAutoSave();
        }

        function moveUp(btn) {
            const section = btn.closest('.builder-section');
            const prev = section.previousElementSibling;
            if (prev && prev.id !== 'empty-builder-msg') {
                section.parentNode.insertBefore(section, prev);
            }
        }

        function moveDown(btn) {
            const section = btn.closest('.builder-section');
            const next = section.nextElementSibling;
            if (next) {
                section.parentNode.insertBefore(next, section);
            }
        }

        function removeSection(btn) {
            if (confirm('Delete this section?')) {
                btn.closest('.builder-section').remove();
                if (container.children.length <= 1) {
                    emptyMsg.style.display = 'block';
                }
            }
        }

        // Auto-save logic
        let autoSaveTimeout;
        const autoSaveStatus = document.getElementById('save-status');

        function triggerAutoSave() {
            clearTimeout(autoSaveTimeout);

            autoSaveTimeout = setTimeout(() => {
                try {
                    // Show indicator only when actual saving starts
                    if (autoSaveStatus) {
                        autoSaveStatus.innerText = 'Saving...';
                        autoSaveStatus.classList.remove('opacity-0');
                        autoSaveStatus.classList.add('opacity-100');
                        autoSaveStatus.classList.remove('text-red-500');
                    }

                    const sections = collectSectionsData();
                    const content = JSON.stringify(sections);

                    fetch('{{ route("admin.online-store.mnpages.auto-save", $mnpage) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ content: content })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && autoSaveStatus) {
                                autoSaveStatus.innerText = 'Saved';

                                // Reload iframe for live preview
                                const iframe = document.getElementById('live-preview-iframe');
                                if (iframe) {
                                    iframe.contentWindow.location.reload();
                                }

                                setTimeout(() => {
                                    autoSaveStatus.classList.remove('opacity-100');
                                    autoSaveStatus.classList.add('opacity-0');
                                }, 2000);
                            }
                        })
                        .catch(err => {
                            console.error('Auto-save fetch failed', err);
                            if (autoSaveStatus) {
                                autoSaveStatus.innerText = 'Save Failed';
                                autoSaveStatus.classList.add('text-red-500');
                            }
                        });
                } catch (e) {
                    console.error('Auto-save error during collection:', e);
                    if (autoSaveStatus) {
                        autoSaveStatus.innerText = 'Error in Layout';
                        autoSaveStatus.classList.remove('opacity-0');
                        autoSaveStatus.classList.add('text-red-500');
                    }
                }
            }, 1000); // Debounce 1s
        }

        // Live Preview Functions
        function setPreviewWidth(width) {
            const wrapper = document.getElementById('iframe-wrapper');
            if (wrapper) {
                wrapper.style.maxWidth = width;
            }
        }

        // Attach listeners for auto-save
        container.addEventListener('input', triggerAutoSave);
        container.addEventListener('change', triggerAutoSave);
        // Mutation observer to detect DOM changes (adding/removing sections)
        const observer = new MutationObserver(triggerAutoSave);
        observer.observe(container, { childList: true, subtree: true });


        // Collect sections data helper
        function collectSectionsData() {
            const sections = [];

            document.querySelectorAll('.builder-section').forEach(section => {
                const type = section.dataset.type;
                let data = {};

                if (type === 'hero_slider') {
                    data.image_fit = section.querySelector('.section-image-fit')?.value || 'cover';
                    data.section_width = section.querySelector('.section-width')?.value || 'default';
                    data.section_height = section.querySelector('.section-height')?.value || 'large';
                    data.slides = [];
                    section.querySelectorAll('.slide-item').forEach(slide => {
                        data.slides.push({
                            image: slide.querySelector('.slide-image')?.value || '',
                            link: slide.querySelector('.slide-link')?.value || '',
                            title: slide.querySelector('.slide-title')?.value || '',
                            subtitle: slide.querySelector('.slide-subtitle')?.value || '',
                        });
                    });
                } else if (type === 'product_carousel') {
                    data.title = section.querySelector('.section-title')?.value || '';
                    data.collection = section.querySelector('.section-collection')?.value || '';
                    data.limit = section.querySelector('.section-limit')?.value || '';
                } else if (type === 'product_horizontal_scroll') {
                    data.title = section.querySelector('.section-title')?.value || '';
                    data.product_slugs = [];
                    section.querySelectorAll('.product-item').forEach(item => {
                        const val = item.querySelector('.item-product-slug')?.value || '';
                        if (val) data.product_slugs.push(val);
                    });
                } else if (type === 'image_product_carousel') {
                    data.image = section.querySelector('.carousel-image')?.value || '';
                    data.object_fit = section.querySelector('.carousel-image-fit') ? section.querySelector('.carousel-image-fit')?.value || '' : 'cover';
                    data.product_slugs = [];
                    section.querySelectorAll('.product-item').forEach(item => {
                        const val = item.querySelector('.item-product-slug')?.value || '';
                        if (val) data.product_slugs.push(val);
                    });
                } else if (type === 'text_block') {
                    const editorEl = section.querySelector('.quill-editor');
                    if (editorEl && editorEl._quill) {
                        data.content = editorEl._quill.root.innerHTML;
                    } else {
                        data.content = '';
                    }
                } else if (type === 'image_grid') {
                    data.title = section.querySelector('.grid-title')?.value || '';
                    data.columns = section.querySelector('.grid-columns')?.value || '';
                    data.text = section.querySelector('.grid-text')?.value || '';
                    data.cta_text = section.querySelector('.grid-cta-text')?.value || '';
                    data.cta_link = section.querySelector('.grid-cta-link')?.value || '';
                    data.hover_animation = section.querySelector('.grid-hover-animation') ? section.querySelector('.grid-hover-animation')?.value || '' : 'zoom';
                    data.images = [];
                    section.querySelectorAll('.grid-item').forEach(item => {
                        data.images.push({
                            image: item.querySelector('.item-image') ? item.querySelector('.item-image')?.value || '' : '',
                            link: item.querySelector('.item-link') ? item.querySelector('.item-link')?.value || '' : '',
                            alt: item.querySelector('.item-alt') ? item.querySelector('.item-alt')?.value || '' : '',
                            cta_text: item.querySelector('.item-cta-text') ? item.querySelector('.item-cta-text')?.value || '' : '',
                            cta_style: item.querySelector('.item-cta-style') ? item.querySelector('.item-cta-style')?.value || '' : 'pill_overlay',
                            target: item.querySelector('.item-target') ? item.querySelector('.item-target')?.value || '' : '_self',
                        });
                    });
                } else if (type === 'image_banner') {
                    data.section_width = section.querySelector('.section-width')?.value || 'default';
                    data.section_height = section.querySelector('.section-height')?.value || 'large';
                    data.image = section.querySelector('.banner-image')?.value || '';
                    data.object_fit = section.querySelector('.banner-fit')?.value || '';
                    data.text = section.querySelector('.banner-text')?.value || '';
                    data.text_position = section.querySelector('.banner-text-position')?.value || '';
                } else if (type === 'horizontal_scroll_cards') {
                    data.title = section.querySelector('.vscroll-title')?.value || '';
                    data.hover_animation = section.querySelector('.hscroll-hover-animation') ? section.querySelector('.hscroll-hover-animation')?.value || '' : 'zoom';
                    data.cards = [];
                    section.querySelectorAll('.vscroll-card').forEach(card => {
                        data.cards.push({
                            image: card.querySelector('.card-image')?.value || '',
                            headline: card.querySelector('.card-headline')?.value || '',
                            paragraph: card.querySelector('.card-paragraph')?.value || '',
                            cta_text: card.querySelector('.card-cta-text')?.value || '',
                            cta_link: card.querySelector('.card-cta-link')?.value || '',
                        });
                    });
                } else if (type === 'announcement_marquee') {
                    data.speed = section.querySelector('.marquee-speed')?.value || '';
                    data.bg_color = section.querySelector('.marquee-bg-color')?.value || '';
                    data.text_color = section.querySelector('.marquee-text-color')?.value || '';
                    data.items = [];
                    section.querySelectorAll('.marquee-item').forEach(item => {
                        data.items.push({
                            text: item.querySelector('.item-marquee-text')?.value || '',
                            link: item.querySelector('.item-marquee-link')?.value || ''
                        });
                    });
                } else if (type === 'feature_highlights') {
                    data.text_color = section.querySelector('.hl-text-color') ? section.querySelector('.hl-text-color')?.value || '' : '#000000';
                    data.items = [];
                    section.querySelectorAll('.highlight-item').forEach(item => {
                        data.items.push({
                            icon: item.querySelector('.item-hl-icon')?.value || '',
                            label: item.querySelector('.item-hl-label')?.value || '',
                            desc: item.querySelector('.item-hl-desc')?.value || ''
                        });
                    });
                } else if (type === 'category_grid') {
                    data.title = section.querySelector('.catgrid-title')?.value || '';
                    data.columns = section.querySelector('.catgrid-columns')?.value || '';
                    data.categories = [];
                    section.querySelectorAll('.category-item').forEach(item => {
                        data.categories.push({
                            image: item.querySelector('.item-cat-image')?.value || '',
                            name: item.querySelector('.item-cat-name')?.value || '',
                            link: item.querySelector('.item-cat-link')?.value || ''
                        });
                    });
                } else if (type === 'split_banner') {
                    data.image = section.querySelector('.split-image')?.value || '';
                    data.image_side = section.querySelector('.split-image-side')?.value || '';
                    data.object_fit = section.querySelector('.split-object-fit')?.value || '';
                    data.badge = section.querySelector('.split-badge')?.value || '';
                    data.title = section.querySelector('.split-title')?.value || '';
                    data.text_bg = section.querySelector('.split-text-bg')?.value || '';
                    data.subtitle = section.querySelector('.split-subtitle')?.value || '';
                    data.cta_text = section.querySelector('.split-cta-text')?.value || '';
                    data.cta_link = section.querySelector('.split-cta-link')?.value || '';
                } else if (type === 'newsletter_signup') {
                    data.title = section.querySelector('.nl-title')?.value || '';
                    data.bg_style = section.querySelector('.nl-bg-style')?.value || '';
                    data.description = section.querySelector('.nl-description')?.value || '';
                    data.placeholder = section.querySelector('.nl-placeholder')?.value || '';
                    data.button_text = section.querySelector('.nl-button-text')?.value || '';
                    data.bg_image = section.querySelector('.nl-bg-image')?.value || '';
                } else if (type === 'video_banner') {
                    data.video_url = section.querySelector('.vid-url')?.value || '';
                    data.height = section.querySelector('.vid-height')?.value || '';
                    data.overlay_opacity = section.querySelector('.vid-overlay')?.value || '';
                    data.title = section.querySelector('.vid-title')?.value || '';
                    data.subtitle = section.querySelector('.vid-subtitle')?.value || '';
                    data.cta_text = section.querySelector('.vid-cta-text')?.value || '';
                    data.cta_link = section.querySelector('.vid-cta-link')?.value || '';
                } else if (type === 'testimonials_slider') {
                    data.title = section.querySelector('.testimonials-title')?.value || '';
                    data.testimonials = [];
                    section.querySelectorAll('.testimonial-item').forEach(item => {
                        data.testimonials.push({
                            quote: item.querySelector('.item-t-quote')?.value || '',
                            name: item.querySelector('.item-t-name')?.value || '',
                            role: item.querySelector('.item-t-role')?.value || '',
                            rating: item.querySelector('.item-t-rating')?.value || '',
                            avatar: item.querySelector('.item-t-avatar')?.value || ''
                        });
                    });
                } else if (type === 'countdown_timer') {
                    data.title = section.querySelector('.cd-title')?.value || '';
                    data.end_date = section.querySelector('.cd-end-date')?.value || '';
                    data.description = section.querySelector('.cd-description')?.value || '';
                    data.cta_text = section.querySelector('.cd-cta-text')?.value || '';
                    data.cta_link = section.querySelector('.cd-cta-link')?.value || '';
                    data.bg_style = section.querySelector('.cd-bg-style')?.value || '';
                }

                let settings = {};
                const hasBg = section.querySelector('.section-has-bg');
                if (hasBg && hasBg.checked) {
                    settings.bg_color = section.querySelector('.section-bg-color')?.value || '';
                }
                const hasTextColor = section.querySelector('.section-has-text-color');
                if (hasTextColor && hasTextColor.checked) {
                    settings.text_color = section.querySelector('.section-text-color')?.value || '';
                }
                const innerPaddingYSel = section.querySelector('.section-inner-padding-y');
                if (innerPaddingYSel) {
                    settings.inner_padding_y = innerPaddingYSel.value;
                }
                const innerPaddingXSel = section.querySelector('.section-inner-padding-x');
                if (innerPaddingXSel) {
                    settings.inner_padding_x = innerPaddingXSel.value;
                }
                const outerMarginYSel = section.querySelector('.section-outer-margin-y');
                if (outerMarginYSel) {
                    settings.outer_margin_y = outerMarginYSel.value;
                }
                const outerMarginXSel = section.querySelector('.section-outer-margin-x');
                if (outerMarginXSel) {
                    settings.outer_margin_x = outerMarginXSel.value;
                }
                const globalWidthSel = section.querySelector('.global-section-width');
                if (globalWidthSel) {
                    settings.section_width = globalWidthSel.value;
                }
                const globalHeightSel = section.querySelector('.global-section-height');
                if (globalHeightSel) {
                    settings.section_height = globalHeightSel.value;
                }
                const mobileCheck = section.querySelector('.section-show-mobile');
                if (mobileCheck) {
                    settings.show_mobile = mobileCheck.checked;
                }
                const desktopCheck = section.querySelector('.section-show-desktop');
                if (desktopCheck) {
                    settings.show_desktop = desktopCheck.checked;
                }

                sections.push({ type: type, data: data, settings: settings });
            });
            return sections;
        }

        // Publish button handles saving — no form submit needed here.

        function publishChanges() {
            if (!confirm('Are you sure you want to publish these changes to the live site?')) return;

            const sections = collectSectionsData();

            fetch('{{ route("admin.online-store.mnpages.publish", $mnpage) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ content: JSON.stringify(sections) })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Published successfully!');
                        window.location.reload();
                    } else {
                        alert('Publish failed.');
                    }
                })
                .catch(error => {
                    console.error('Publish error:', error);
                    alert('Publish failed due to an error.');
                });
        }

        function uploadImage(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('image', input.files[0]);

                // Show loading state safely
                const label = input.closest('label');
                const span = label.querySelector('span');
                let originalText = 'Upload';
                if (span) {
                    originalText = span.innerText;
                    span.innerText = '...';
                } else {
                    // Fallback if no span wrapper exists
                    originalText = label.childNodes[0].nodeValue || 'Upload';
                    if (label.childNodes[0].nodeType === 3) {
                        label.childNodes[0].nodeValue = '... ';
                    }
                }

                fetch('{{ route("admin.online-store.mnpages.upload-image") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Find the text input for image URL and set value.
                            // We use label.parentElement because the upload <label> itself has class="flex",
                            // so input.closest('.flex') would wrongly match the label, not the outer wrapper div.
                            const urlInput = label.parentElement.querySelector('input[type="text"]');
                            if (urlInput) {
                                urlInput.value = data.url;
                                triggerAutoSave(); // Save after upload
                            }
                        } else {
                            alert('Upload failed: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Upload failed');
                    })
                    .finally(() => {
                        if (span) {
                            span.innerText = originalText;
                        } else if (label.childNodes[0].nodeType === 3) {
                            label.childNodes[0].nodeValue = originalText;
                        }
                        input.value = ''; // Reset file input
                    });
            }
        }

        // Initialize Sortable
        new Sortable(container, {
            animation: 150,
            ghostClass: 'bg-indigo-50',
            handle: '.cursor-move',
            onEnd: function () {
                triggerAutoSave();
            }
        });

        // Initial Hydration
        document.addEventListener('DOMContentLoaded', function () {
            let initialContent = @json($mnpage->draft_content ?? $mnpage->content ?? []);

            // If the content is just the metadata block, fallback to published content
            if (initialContent.length === 1 && initialContent[0].type === 'page_meta') {
                initialContent = @json($mnpage->content ?? []);
            }

            console.log('Hydrating builder with:', initialContent);
            if (initialContent && Array.isArray(initialContent) && initialContent.length > 0) {
                initialContent.forEach(section => {
                    if (section.type === 'page_meta') return; // Skip layout metadata in builder
                    try {
                        addSection(section.type, section.data, section.settings);
                    } catch (e) {
                        console.error('Error hydrating section:', section.type, e);
                    }
                });
            }
        });
    </script>

<!-- Add Section Modal -->
<div id="add-section-modal" class="fixed inset-0 z-[100] bg-gray-900/40 backdrop-blur-sm hidden items-center justify-center p-4 sm:p-6 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col scale-95 transition-transform duration-300 relative overflow-hidden" id="add-section-modal-content">
        <!-- Header -->
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white relative z-10">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Add a New Section</h3>
                <p class="text-sm text-gray-500 mt-1">Select a pre-built component to add to your page</p>
            </div>
            <button type="button" onclick="closeAddSectionModal()" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-8 overflow-y-auto custom-scrollbar flex-1 bg-gray-50">
            <div class="space-y-12">
                <!-- Base Components -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5 flex items-center gap-3">
                        <span class="w-8 h-px bg-gray-200"></span> Base Components <span class="flex-1 h-px bg-gray-200"></span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <button onclick="addSectionAndClose('hero_slider')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Hero Slider</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Full width rotating banner with text and links</p>
                        </button>
                        
                        <button onclick="addSectionAndClose('product_carousel')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Product Carousel</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Automated scrolling grid of your products</p>
                        </button>

                        <button onclick="addSectionAndClose('text_block')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Text Block</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Rich text editor for storytelling and content</p>
                        </button>

                        <button onclick="addSectionAndClose('image_grid')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Image Grid</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Multi-column visual gallery for photos</p>
                        </button>

                        <button onclick="addSectionAndClose('image_banner')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Image Banner</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Standalone high-impact visual with text</p>
                        </button>
                    </div>
                </div>

                <!-- Interactive -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5 flex items-center gap-3">
                        <span class="w-8 h-px bg-gray-200"></span> Interactive <span class="flex-1 h-px bg-gray-200"></span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <button onclick="addSectionAndClose('horizontal_scroll_cards')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Horizontal Cards</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Swipeable custom card gallery</p>
                        </button>
                        
                        <button onclick="addSectionAndClose('product_horizontal_scroll')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Product Scroll</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Swipeable product listing area</p>
                        </button>

                        <button onclick="addSectionAndClose('image_product_carousel')" class="text-left p-5 rounded-xl border border-gray-200 hover:border-violet-500 hover:shadow-md transition-all group bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <div class="w-10 h-10 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center mb-4 group-hover:bg-violet-50 group-hover:text-violet-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Image + Products</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Mixed media and product carousel</p>
                        </button>
                    </div>
                </div>

                <!-- Essentials -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5 flex items-center gap-3">
                        <span class="w-8 h-px bg-gray-200"></span> Essentials <span class="flex-1 h-px bg-gray-200"></span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <button onclick="addSectionAndClose('spacer')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Spacing</h5>
                            <p class="text-[11px] text-gray-500">Vertical gap</p>
                        </button>
                        <button onclick="addSectionAndClose('announcement_marquee')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Marquee</h5>
                            <p class="text-[11px] text-gray-500">Scrolling text</p>
                        </button>
                        <button onclick="addSectionAndClose('feature_highlights')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Features</h5>
                            <p class="text-[11px] text-gray-500">Icons and text</p>
                        </button>
                        <button onclick="addSectionAndClose('category_grid')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Categories</h5>
                            <p class="text-[11px] text-gray-500">Visual navigation</p>
                        </button>
                        <button onclick="addSectionAndClose('split_banner')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Split Banner</h5>
                            <p class="text-[11px] text-gray-500">50/50 layout</p>
                        </button>
                        <button onclick="addSectionAndClose('newsletter_signup')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Newsletter</h5>
                            <p class="text-[11px] text-gray-500">Email capture</p>
                        </button>
                        <button onclick="addSectionAndClose('video_banner')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Video Banner</h5>
                            <p class="text-[11px] text-gray-500">Autoplay video</p>
                        </button>
                        <button onclick="addSectionAndClose('testimonials_slider')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Testimonials</h5>
                            <p class="text-[11px] text-gray-500">Customer reviews</p>
                        </button>
                        <button onclick="addSectionAndClose('countdown_timer')" class="text-left p-4 rounded-xl border border-gray-200 hover:border-violet-500 transition-all group bg-white">
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Countdown</h5>
                            <p class="text-[11px] text-gray-500">Urgency banner</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openAddSectionModal() {
        const modal = document.getElementById('add-section-modal');
        const content = document.getElementById('add-section-modal-content');
        modal.classList.remove('hidden');
        // trigger reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }

    function closeAddSectionModal() {
        const modal = document.getElementById('add-section-modal');
        const content = document.getElementById('add-section-modal-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function addSectionAndClose(type) {
        addSection(type);
        closeAddSectionModal();
    }
</script>
</body>

</html>