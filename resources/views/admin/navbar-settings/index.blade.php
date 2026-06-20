@extends('layouts.admin')

@section('header', 'Menu & Navigation Settings')


@section('content')
    <div class="w-full" x-data="menuBuilderBuilder()">

        <form action="{{ route('admin.online-store.navbar-settings.update') }}" method="POST" id="navbarForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="navbar_style" value="custom">
            <input type="hidden" name="menu_structure" :value="JSON.stringify(items)">

            <div class="space-y-6">

                <!-- Navbar Settings Row -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex justify-between items-end gap-6 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 leading-tight">Advanced Menu Config</h1>
                        <p class="text-gray-500 text-sm mt-1">Design complex grid dropdowns with nested categories, grouped
                            columns, and rich images.</p>
                    </div>
                    <div class="flex gap-6 shrink-0 w-[42rem] items-center justify-end">
                        <div class="w-1/4">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Position</label>
                            <select name="nav_position" x-model="position"
                                class="block w-full rounded-lg border border-gray-200 py-2 px-3 focus:ring-violet-500 focus:border-violet-500 text-sm bg-gray-50 font-bold text-gray-800">
                                <option value="inline">Beside Logo</option>
                                <option value="below">Below Bar</option>
                            </select>
                        </div>
                        <div class="w-1/4">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Alignment</label>
                            <select name="nav_alignment" x-model="alignment"
                                class="block w-full rounded-lg border border-gray-200 py-2 px-3 focus:ring-violet-500 focus:border-violet-500 text-sm bg-gray-50 font-bold text-gray-800">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="w-1/4">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Link
                                Hover Effect</label>
                            <select name="nav_hover_style" x-model="hoverStyle"
                                class="block w-full rounded-lg border border-gray-200 py-2 px-3 focus:ring-violet-500 focus:border-violet-500 text-sm bg-gray-50 font-bold text-gray-800">
                                <option value="none">No Animation</option>
                                <option value="underline">Fade Underline</option>
                                <option value="left_to_right">Slide Underline</option>
                            </select>
                        </div>
                        <div class="w-1/4 pt-5">
                            <button type="submit"
                                class="w-full bg-black text-white px-2 py-2 rounded-lg font-bold shadow-sm hover:bg-gray-800 transition-colors flex justify-center items-center gap-1.5 text-[11px] uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Deploy Menu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Builder -->
                <div class="bg-gray-50/50 rounded-[2rem] border border-gray-200 p-8">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight flex items-center gap-2"><svg
                                class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16m-7 6h7"></path>
                            </svg> Root Construction</h3>
                        <button type="button" @click="addMainItem()"
                            class="text-sm font-bold bg-white border border-gray-200 text-gray-900 hover:text-violet-600 hover:border-violet-300 px-4 py-2 rounded-xl flex items-center gap-1 transition-all shadow-sm">
                            + Append Root Item
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-6">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div
                                class="bg-white border-2 border-gray-100 rounded-2xl shadow-sm relative group/card transition-colors hover:border-violet-100 overflow-visible">

                                <!-- Delete Root Button -->
                                <div class="absolute right-4 top-4">
                                    <button type="button" @click="removeItem(index)"
                                        class="text-gray-400 hover:text-white p-1 rounded hover:bg-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Main Item Header Area -->
                                <div class="p-6 pb-0">
                                    <div class="flex gap-6 pr-12 pb-6 border-b border-dashed border-gray-200">
                                        <div class="flex-1">
                                            <label
                                                class="block text-xs font-bold text-violet-600 uppercase mb-1.5 tracking-wider">Root
                                                Item Label</label>
                                            <input type="text" x-model="item.label"
                                                class="block w-full border border-gray-200 rounded-lg focus:border-violet-500 focus:ring-violet-500 py-2.5 px-3 text-sm font-semibold bg-white outline-none transition-colors"
                                                placeholder="e.g. Mens, Summer Collection...">
                                        </div>
                                        <div class="w-64">
                                            <label
                                                class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Item
                                                Format</label>
                                            <select x-model="item.type"
                                                class="block w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm focus:border-violet-500 font-bold text-gray-800">
                                                <option value="mega_menu">Expanded Dropdown / Grid</option>
                                                <option value="category">Flat Category Link</option>
                                                <option value="collection">Flat Collection Link</option>
                                                <option value="page">Flat CMS Page Link</option>
                                                <option value="link">Flat Custom URL Link</option>
                                            </select>
                                        </div>

                                        <!-- Dynamic Bindings -->
                                        <div class="flex-1 border-l border-gray-100 pl-6"
                                            x-show="item.type !== 'mega_menu'">
                                            <label
                                                class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Target
                                                Binding</label>
                                            <div x-show="item.type === 'link'">
                                                <input type="text" x-model="item.link" placeholder="URL"
                                                    class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                            </div>
                                            <div x-show="item.type === 'collection'">
                                                <select x-model="item.ref_id"
                                                    class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                    <option value="">Map to Collection...</option>
                                                    <template x-for="col in collections">
                                                        <option :value="col.slug" x-text="col.name"
                                                            :selected="col.slug == item.ref_id"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div x-show="item.type === 'category'">
                                                <select x-model="item.ref_id"
                                                    class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                    <option value="">Map to Category...</option>
                                                    <template x-for="cat in categories">
                                                        <option :value="cat.slug" x-text="cat.name"
                                                            :selected="cat.slug == item.ref_id"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div x-show="item.type === 'page'">
                                                <select x-model="item.ref_id"
                                                    class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                    <option value="">Map to CMS Page...</option>
                                                    <template x-for="pg in pages">
                                                        <option :value="pg.slug" x-text="pg.title"
                                                            :selected="pg.slug == item.ref_id"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- If it's a mega menu, they can optionally link the root! -->
                                        <div class="flex-1 border-l border-gray-100 pl-6"
                                            x-show="item.type === 'mega_menu'">
                                            <label
                                                class="block text-[10px] font-black text-violet-600 uppercase mb-1.5 tracking-wider">Clickable
                                                Root Link Target (Optional)</label>
                                            <div class="flex gap-2 w-full">
                                                <select x-model="item.root_type"
                                                    class="rounded-lg border border-gray-200 py-2.5 px-3 text-xs font-bold text-gray-500 bg-gray-50 shrink-0 w-32 focus:border-violet-500 outline-none">
                                                    <option value="">No Click Link</option>
                                                    <option value="url">Custom URL</option>
                                                    <option value="category">Category</option>
                                                    <option value="collection">Collection</option>
                                                    <option value="page">CMS Page</option>
                                                </select>

                                                <div x-show="item.root_type === 'url'" class="flex-1">
                                                    <input type="text" x-model="item.root_url" placeholder="https://"
                                                        class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                </div>
                                                <div x-show="item.root_type === 'category'" class="flex-1">
                                                    <select x-model="item.root_ref_id"
                                                        class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                        <option value="">Map Category</option>
                                                        <template x-for="cat in categories">
                                                            <option :value="cat.slug" x-text="cat.name"
                                                                :selected="cat.slug == item.root_ref_id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div x-show="item.root_type === 'collection'" class="flex-1">
                                                    <select x-model="item.root_ref_id"
                                                        class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                        <option value="">Map Collection</option>
                                                        <template x-for="col in collections">
                                                            <option :value="col.slug" x-text="col.name"
                                                                :selected="col.slug == item.root_ref_id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div x-show="item.root_type === 'page'" class="flex-1">
                                                    <select x-model="item.root_ref_id"
                                                        class="block w-full rounded-lg border border-gray-200 py-2.5 px-3 text-sm font-medium focus:border-violet-500 outline-none">
                                                        <option value="">Map CMS Page</option>
                                                        <template x-for="pg in pages">
                                                            <option :value="pg.slug" x-text="pg.title"
                                                                :selected="pg.slug == item.root_ref_id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mega Menu Area -->
                                <div x-show="item.type === 'mega_menu'" class="p-6 bg-gray-50/30 rounded-b-2xl">
                                    <div class="flex items-center justify-between mb-4 mt-2">
                                        <div
                                            class="flex items-center gap-3 bg-white px-3 py-2 border border-gray-200 rounded-lg shadow-sm">
                                            <label class="text-xs font-bold text-gray-700 uppercase tracking-widest">Mega
                                                Row Grid (1-8 Columns):</label>
                                            <input type="number" min="1" max="8" x-model="item.columns"
                                                class="w-16 rounded border-b-2 border-gray-300 py-1 px-1 text-center font-black focus:border-violet-500 outline-none">
                                        </div>
                                        <button type="button" @click="addColumnGrid(item)"
                                            class="text-xs font-bold bg-transparent border border-black text-black hover:bg-black hover:text-white px-4 py-2 rounded-md transition-colors flex items-center gap-1 shadow-sm">
                                            + Deploy a new physical Layout Column
                                        </button>
                                    </div>

                                    <!-- Grid Layout -->
                                    <div class="w-full relative mt-6 grid gap-6 items-start"
                                        :style="`grid-template-columns: repeat(${item.columns || 4}, minmax(0, 1fr))`">
                                        <!-- Explicit Physical Columns loop -->
                                        <template x-for="(col, cIndex) in item.layout_columns" :key="col.id">
                                            <div class="h-full min-h-[100px] border-2 border-dashed border-violet-200 rounded-xl bg-violet-50/20 p-2 relative group/col isolate flex flex-col gap-3 transition-colors duration-200"
                                                @dragover.prevent="$el.classList.add('bg-violet-100/60'); $el.classList.add('border-violet-400')"
                                                @dragleave.prevent="$el.classList.remove('bg-violet-100/60'); $el.classList.remove('border-violet-400')"
                                                @drop.prevent="$el.classList.remove('bg-violet-100/60'); $el.classList.remove('border-violet-400'); dropBlockIntoCol(item, col)">

                                                <!-- Column Actions -->
                                                <div
                                                    class="absolute -top-3 right-2 flex gap-1 z-10 opacity-0 group-hover/col:opacity-100 transition-opacity">
                                                    <button type="button" @click="removeColumnGrid(item, cIndex)"
                                                        class="bg-red-100 text-red-600 hover:bg-red-500 hover:text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm border border-red-200 transition-colors">Del
                                                        Col</button>
                                                    <button type="button" @click="addBlockToCol(col)"
                                                        class="bg-violet-600 text-white hover:bg-violet-800 text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm transition-colors">+
                                                        Block</button>
                                                </div>

                                                <template x-for="(block, bIndex) in col.blocks" :key="block.id">
                                                    <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden relative group/block cursor-move"
                                                        draggable="true"
                                                        @dragstart="startDragBlock(item, col, bIndex); $event.target.style.opacity = '0.5'"
                                                        @dragend="$event.target.style.opacity = '1'">

                                                        <!-- Block Toolbar -->
                                                        <div
                                                            class="bg-gray-100/50 px-2 py-1.5 border-b border-gray-100 flex items-center justify-between">
                                                            <div class="flex items-center gap-1.5">
                                                                <svg class="w-3 h-3 text-gray-400" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                                </svg>
                                                                <select x-model="block.type"
                                                                    class="bg-transparent text-[9px] font-black uppercase text-gray-500 outline-none focus:text-violet-600">
                                                                    <option value="group">Links Group</option>
                                                                    <option value="image">Promo Image</option>
                                                                </select>
                                                            </div>
                                                            <button type="button" @click="removeBlockFromCol(col, bIndex)"
                                                                class="text-gray-400 hover:text-red-500">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        <div class="p-3">
                                                            <!-- Links Group -->
                                                            <div x-show="block.type === 'group'">
                                                                <div class="mb-3 border-b border-gray-100 pb-2">
                                                                    <input type="text" x-model="block.label"
                                                                        placeholder="Header Title"
                                                                        class="w-full border-0 text-sm font-bold text-gray-900 outline-none p-0 focus:ring-0 placeholder:text-gray-300 uppercase tracking-wider">
                                                                    <input type="text" x-model="block.link"
                                                                        placeholder="Header Link Target (Optional)"
                                                                        class="w-full text-[10px] border border-gray-200 rounded px-1.5 py-1 text-gray-500 mt-1 focus:border-violet-500 outline-none">
                                                                </div>

                                                                <!-- Sub Links Inside Block -->
                                                                <div class="space-y-1.5 relative">
                                                                    <template x-for="(link, lIndex) in block.links"
                                                                        :key="link.id">
                                                                        <div
                                                                            class="flex items-center gap-1 group/link relative">
                                                                            <select x-model="link.type"
                                                                                class="w-[60px] text-[9px] font-bold border border-gray-200 rounded bg-gray-50 p-1 text-gray-500">
                                                                                <option value="category">Cat</option>
                                                                                <option value="collection">Col</option>
                                                                                <option value="url">URL</option>
                                                                            </select>

                                                                            <div x-show="link.type === 'url'"
                                                                                class="flex-1 flex gap-1">
                                                                                <input type="text" x-model="link.label"
                                                                                    placeholder="LBL"
                                                                                    class="w-1/2 text-[10px] border border-gray-200 rounded p-1">
                                                                                <input type="text" x-model="link.url"
                                                                                    placeholder="URL"
                                                                                    class="w-1/2 text-[10px] border border-gray-200 rounded p-1">
                                                                            </div>

                                                                            <div x-show="link.type === 'category'"
                                                                                class="flex-1">
                                                                                <select x-model="link.ref_id"
                                                                                    @change="link.label = getCategoryName(link.ref_id) || link.label"
                                                                                    class="w-full text-[10px] border border-gray-200 rounded p-1 font-medium bg-white">
                                                                                    <option value="">Category...</option>
                                                                                    <template x-for="cat in categories">
                                                                                        <option :value="cat.slug"
                                                                                            x-text="cat.name"
                                                                                            :selected="cat.slug == link.ref_id">
                                                                                        </option>
                                                                                    </template>
                                                                                </select>
                                                                            </div>

                                                                            <div x-show="link.type === 'collection'"
                                                                                class="flex-1">
                                                                                <select x-model="link.ref_id"
                                                                                    @change="link.label = getCollectionName(link.ref_id) || link.label"
                                                                                    class="w-full text-[10px] border border-gray-200 rounded p-1 font-medium bg-white">
                                                                                    <option value="">Collection...</option>
                                                                                    <template x-for="col in collections">
                                                                                        <option :value="col.slug"
                                                                                            x-text="col.name"
                                                                                            :selected="col.slug == link.ref_id">
                                                                                        </option>
                                                                                    </template>
                                                                                </select>
                                                                            </div>

                                                                            <button type="button"
                                                                                @click="removeSubLink(block, lIndex)"
                                                                                class="shrink-0 text-red-300 hover:text-red-500">
                                                                                <svg class="w-3.5 h-3.5" fill="none"
                                                                                    viewBox="0 0 24 24"
                                                                                    stroke="currentColor">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                    <button type="button" @click="addSubLink(block)"
                                                                        class="text-[9px] font-bold uppercase text-violet-500 hover:text-violet-700 flex items-center gap-0.5 mt-2 transition-colors w-full bg-violet-50 py-1 justify-center rounded border border-violet-100">
                                                                        + Attach Link
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Image Promo -->
                                                            <div x-show="block.type === 'image'" class="space-y-2 relative">
                                                                <div class="bg-gray-50 border border-gray-200 rounded-lg flex flex-col items-center justify-center overflow-hidden relative group/img"
                                                                    :class="!block.image_url ? 'aspect-[4/5]' : 'min-h-[100px] p-2'">
                                                                    <template x-if="block.image_url">
                                                                        <img :src="block.image_url"
                                                                            class="max-w-full h-auto object-contain block mx-auto">
                                                                    </template>

                                                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity z-10"
                                                                        :class="{'opacity-100 bg-transparent': !block.image_url}">
                                                                        <label
                                                                            class="px-3 py-1.5 bg-white text-[10px] font-bold rounded shadow-sm text-gray-900 overflow-hidden cursor-pointer hover:bg-gray-50 transition-colors">
                                                                            <span x-show="block.isUploading">Up...</span>
                                                                            <span x-show="!block.isUploading">Upload</span>
                                                                            <input type="file" accept="image/*"
                                                                                class="opacity-0 absolute inset-0 w-full h-full cursor-pointer"
                                                                                @change="uploadImage($event, block)">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <input type="text" x-model="block.link"
                                                                    placeholder="Click URL"
                                                                    class="w-full text-[10px] border border-gray-200 rounded p-1.5 focus:border-violet-500 outline-none">
                                                                <input type="text" x-model="block.label"
                                                                    placeholder="Overlay text"
                                                                    class="w-full text-[10px] border border-gray-200 rounded p-1.5 focus:border-violet-500 outline-none mb-2">
                                                                <input type="hidden" x-model="block.image_url">
                                                            </div>

                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="col.blocks?.length === 0" class="text-center py-2 opacity-50">
                                                    <p class="text-[9px] font-bold text-gray-500 uppercase">Empty Col</p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="item.layout_columns?.length === 0"
                                        class="text-center py-6 mt-4 border border-dashed border-gray-200 rounded-xl bg-white bg-opacity-50">
                                        <p class="text-xs font-bold text-gray-300 uppercase tracking-widest">No layout
                                            columns defined.</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="items.length === 0"
                            class="text-center py-16 bg-gray-50 rounded-3xl border border-dashed border-gray-300/50 relative overflow-hidden">
                            <div class="absolute inset-0 bg-white opacity-40"></div>
                            <div class="relative z-10">
                                <p class="text-sm font-bold tracking-tight text-gray-400 max-w-sm mx-auto">Your canvas is
                                    entirely clean. Layout your premium drop downs using the builder to get started.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex mt-8 pb-12">
                <button type="submit"
                    class="w-full bg-black text-white px-6 py-4 rounded-2xl font-bold shadow-lg shadow-black/20 hover:bg-gray-900 transition-all flex justify-center items-center gap-2 uppercase tracking-wider text-sm mt-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Deploy Configuration
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('menuBuilderBuilder', () => ({
                position: '{{ $settings['nav_position'] ?? 'inline' }}',
                alignment: '{{ $settings['nav_alignment'] ?? 'left' }}',
                hoverStyle: '{{ $settings['nav_hover_style'] ?? 'none' }}',
                items: JSON.parse(`{!! addslashes($settings['menu_structure'] ?? '[]') !!}`) || [],
                collections: @json($collections),
                categories: @json($categories),
                pages: @json($pages),
                draggedData: null,

                startDragBlock(item, sourceCol, bIndex) {
                    this.draggedData = { item, sourceCol, bIndex };
                },

                dropBlockIntoCol(item, targetCol) {
                    if (!this.draggedData || this.draggedData.item !== item) return;
                    const { sourceCol, bIndex } = this.draggedData;

                    // Pop block out of its original physical source column
                    const block = sourceCol.blocks.splice(bIndex, 1)[0];

                    // Inject safely into new column target block array
                    if (!targetCol.blocks) targetCol.blocks = [];
                    targetCol.blocks.push(block);

                    this.draggedData = null; // Clear states
                },

                init() {
                    // Ensure migration to physical layout_columns format seamlessly!
                    this.items.forEach(item => {
                        if (!item.id) item.id = this.generateId();

                        if (item.type === 'mega_menu' && item.blocks) {
                            // Data migration mapping from plain blocks masonry to grid struct
                            item.layout_columns = [];
                            item.blocks.forEach(b => {
                                if (!b.id) b.id = this.generateId();
                                item.layout_columns.push({ id: this.generateId(), blocks: [b] });
                            });
                            delete item.blocks; // Clear old property entirely
                        }
                        if (item.type === 'mega_menu' && !item.layout_columns) {
                            item.layout_columns = [];
                        }

                        if (item.layout_columns) {
                            item.layout_columns.forEach(col => {
                                if (!col.id) col.id = this.generateId();
                                if (col.blocks) {
                                    col.blocks.forEach(b => {
                                        if (!b.id) b.id = this.generateId();
                                        if (b.links) {
                                            b.links.forEach(l => {
                                                if (!l.id) l.id = this.generateId();
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                },

                getCategoryName(slug) {
                    const cat = this.categories.find(c => c.slug === slug);
                    if (cat) return cat.name.split(' > ').pop();
                    return '';
                },

                getCollectionName(slug) {
                    const col = this.collections.find(c => c.slug === slug);
                    return col ? col.name : '';
                },

                generateId() {
                    return 'id-' + Math.random().toString(36).substr(2, 9);
                },

                addMainItem() {
                    this.items.push({
                        id: this.generateId(),
                        label: '',
                        type: 'mega_menu',
                        link: '',
                        ref_id: '',
                        columns: 4,
                        layout_columns: []
                    });
                },

                removeItem(index) {
                    if (confirm('Delete this Root navigation item entirely?')) {
                        this.items.splice(index, 1);
                    }
                },

                addColumnGrid(parentItem) {
                    if (!parentItem.layout_columns) parentItem.layout_columns = [];
                    parentItem.layout_columns.push({
                        id: this.generateId(),
                        blocks: []
                    });
                },

                removeColumnGrid(parentItem, cIndex) {
                    if (confirm('Delete this physical layout column?')) {
                        parentItem.layout_columns.splice(cIndex, 1);
                    }
                },

                addBlockToCol(col) {
                    if (!col.blocks) col.blocks = [];
                    col.blocks.push({
                        id: this.generateId(),
                        type: 'group',
                        label: '',
                        link: '',
                        image_url: '',
                        isUploading: false,
                        links: []
                    });
                },

                removeBlockFromCol(col, bIndex) {
                    if (confirm('Delete this block?')) {
                        col.blocks.splice(bIndex, 1);
                    }
                },

                addSubLink(block) {
                    if (!block.links) block.links = [];
                    block.links.push({
                        id: this.generateId(),
                        type: 'category',
                        label: '',
                        url: '',
                        ref_id: ''
                    });
                },

                removeSubLink(block, lIndex) {
                    block.links.splice(lIndex, 1);
                },

                async uploadImage(event, block) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Client-side file size validation layer
                    const maxSizeMB = 2;
                    if (file.size > maxSizeMB * 1024 * 1024) {
                        alert(`Error: The selected image is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum allowed size is ${maxSizeMB}MB. Please compress your image or select a smaller one.`);
                        event.target.value = ''; // Reset input
                        return;
                    }

                    block.isUploading = true;
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const res = await fetch('{{ route("admin.online-store.mnpages.upload-image") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const text = await res.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('Raw response:', text);
                            alert('Server error: Check console for details. Reached endpoint but failed to parse JSON.');
                            block.isUploading = false;
                            return;
                        }

                        if (res.ok && data.success && data.url) {
                            block.image_url = data.url;
                        } else {
                            const errMsg = data.message || data.error || (data.errors && data.errors.image ? data.errors.image[0] : 'Upload failed due to validation exception.');
                            alert('Error: ' + errMsg);
                        }
                    } catch (e) {
                        console.error("Upload error:", e);
                        alert('Network error while uploading: ' + e.message);
                    }
                    block.isUploading = false;
                }
            }));
        });
    </script>
@endsection