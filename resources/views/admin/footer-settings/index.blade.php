@extends('layouts.admin')

@section('header', 'Footer Settings')

@section('content')
    <div class="w-full" x-data="footerBuilder()">

        <form action="{{ route('admin.online-store.footer-settings.update') }}" method="POST" id="footerForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="footer_structure" :value="JSON.stringify(columns)">

            <div class="space-y-6">

                <!-- Global Settings Row -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-wrap justify-between items-end gap-6 mb-8">
                    <div class="w-full md:w-auto">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 leading-tight">Footer Settings</h1>
                        <p class="text-gray-500 text-sm mt-1">Design your global website footer.</p>
                    </div>
                    <div class="flex flex-wrap gap-4 shrink-0 items-center justify-end w-full md:w-auto">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Background Color</label>
                            <input type="color" name="footer_bg_color" value="{{ $settings['footer_bg_color'] }}" class="h-10 w-24 rounded-lg cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Text Color</label>
                            <input type="color" name="footer_text_color" value="{{ $settings['footer_text_color'] }}" class="h-10 w-24 rounded-lg cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Newsletter Block</label>
                            <select name="footer_show_newsletter" class="block w-full rounded-lg border border-gray-200 py-2 px-3 focus:ring-violet-500 focus:border-violet-500 text-sm bg-gray-50 font-bold text-gray-800">
                                <option value="0" {{ $settings['footer_show_newsletter'] == '0' ? 'selected' : '' }}>Hidden</option>
                                <option value="1" {{ $settings['footer_show_newsletter'] == '1' ? 'selected' : '' }}>Show Newsletter</option>
                            </select>
                        </div>
                        <div class="pt-5">
                            <button type="submit" class="w-full bg-black text-white px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-gray-800 transition-colors flex justify-center items-center gap-1.5 text-[11px] uppercase tracking-wide">
                                Save Footer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar & Social -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Bottom Bar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Copyright Text</label>
                            <input type="text" name="footer_bottom_text" value="{{ $settings['footer_bottom_text'] }}" class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Social Links Display</label>
                            <select name="footer_social_links" class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-sm">
                                <option value="1" {{ $settings['footer_social_links'] == '1' ? 'selected' : '' }}>Show Social Links (From Store Settings)</option>
                                <option value="0" {{ $settings['footer_social_links'] == '0' ? 'selected' : '' }}>Hide Social Links</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Columns Builder -->
                <div class="bg-gray-50/50 rounded-[2rem] border border-gray-200 p-8">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight flex items-center gap-2">Footer Columns</h3>
                        <button type="button" @click="addColumn()" class="text-sm font-bold bg-white border border-gray-200 text-gray-900 hover:text-violet-600 hover:border-violet-300 px-4 py-2 rounded-xl flex items-center gap-1 transition-all shadow-sm">
                            + Add Column
                        </button>
                    </div>

                    <!-- Columns List -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <template x-for="(col, colIndex) in columns" :key="col.id">
                            <div class="bg-white border-2 border-gray-100 rounded-2xl shadow-sm relative group p-4 transition-colors"
                                 draggable="true"
                                 @dragstart.stop="dragCol = colIndex"
                                 @dragover.prevent="$el.classList.add('border-violet-400')"
                                 @dragleave.prevent="$el.classList.remove('border-violet-400')"
                                 @drop.prevent.stop="$el.classList.remove('border-violet-400'); moveColumn(dragCol, colIndex); dragCol = null;"
                            >
                                <div class="absolute right-2 top-2 flex items-center space-x-1">
                                    <div class="cursor-move text-gray-400 hover:text-gray-600 p-1" title="Drag to reorder column">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                    </div>
                                    <button type="button" @click="removeColumn(colIndex)" class="text-gray-400 hover:text-red-500 p-1" title="Delete Column">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <div class="mb-4 pr-6">
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Column Title</label>
                                    <input type="text" x-model="col.title" class="block w-full rounded border-gray-300 text-sm p-2" placeholder="e.g. Quick Links">
                                </div>

                                <!-- Items -->
                                <div class="space-y-3 mb-4">
                                    <template x-for="(item, itemIndex) in col.items" :key="item.id">
                                        <div class="border border-gray-100 bg-gray-50 rounded p-3 relative transition-colors"
                                             draggable="true"
                                             @dragstart.stop="dragItem = {col: colIndex, item: itemIndex}"
                                             @dragover.prevent="$el.classList.add('border-violet-400', 'bg-violet-50')"
                                             @dragleave.prevent="$el.classList.remove('border-violet-400', 'bg-violet-50')"
                                             @drop.prevent.stop="$el.classList.remove('border-violet-400', 'bg-violet-50'); moveItemTo(dragItem, colIndex, itemIndex); dragItem = null;"
                                        >
                                            <div class="absolute right-1 top-1 flex items-center">
                                                <div class="cursor-move text-gray-400 hover:text-gray-600 p-1" title="Drag to reorder item">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                </div>
                                                <button type="button" @click="removeItem(colIndex, itemIndex)" class="text-gray-400 hover:text-red-500 p-1" title="Delete Item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <select x-model="item.type" class="block w-full rounded border-gray-300 text-xs p-1 mb-2 bg-white">
                                                <option value="link">Custom Link</option>
                                                <option value="legal">Legal Policy Page</option>
                                                <option value="text">Text block</option>
                                                <option value="image_text">Image & Text</option>
                                                <option value="phone">Phone Number</option>
                                                <option value="email">Email ID</option>
                                                <option value="payment_badges">Payment Badges</option>
                                                <option value="app_links">App Download Buttons</option>
                                                <option value="store_map">Store Location (Map)</option>
                                                <option value="business_hours">Business Hours</option>
                                            </select>

                                            <div x-show="item.type === 'payment_badges'">
                                                <input type="text" x-model="item.label" placeholder="Title (e.g. Secure Payments)" class="block w-full rounded border-gray-300 text-xs p-1 mb-2">
                                                <div class="space-y-1">
                                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                                        <input type="checkbox" x-model="item.show_card" class="rounded border-gray-300 text-black"> Card Payment
                                                    </label>
                                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                                        <input type="checkbox" x-model="item.show_wallet" class="rounded border-gray-300 text-black"> Wallet
                                                    </label>
                                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                                        <input type="checkbox" x-model="item.show_upi" class="rounded border-gray-300 text-black"> UPI
                                                    </label>
                                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                                        <input type="checkbox" x-model="item.show_netbanking" class="rounded border-gray-300 text-black"> Netbanking
                                                    </label>
                                                </div>
                                            </div>

                                            <div x-show="item.type === 'app_links'">
                                                <input type="text" x-model="item.ios_url" placeholder="Apple App Store URL" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <input type="text" x-model="item.android_url" placeholder="Google Play Store URL" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>

                                            <div x-show="item.type === 'store_map'">
                                                <input type="text" x-model="item.label" placeholder="Store Name (e.g. Main Branch)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <textarea x-model="item.content" placeholder="Complete Address" rows="2" class="block w-full rounded border-gray-300 text-xs p-1 mb-1"></textarea>
                                                <input type="text" x-model="item.url" placeholder="Google Maps Link URL" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>

                                            <div x-show="item.type === 'business_hours'">
                                                <input type="text" x-model="item.label" placeholder="Title (e.g. Working Hours)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <textarea x-model="item.content" placeholder="Mon-Fri: 9am - 7pm&#10;Sat: 10am - 5pm" rows="3" class="block w-full rounded border-gray-300 text-xs p-1"></textarea>
                                            </div>

                                            <div x-show="item.type === 'link'">
                                                <input type="text" x-model="item.label" placeholder="Link Text" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <input type="text" x-model="item.url" placeholder="URL (/about)" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>

                                            <div x-show="item.type === 'phone'">
                                                <input type="text" x-model="item.label" placeholder="Display Text (e.g. Call Us: +123456789)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <input type="text" x-model="item.url" placeholder="Phone Number (e.g. +123456789)" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>

                                            <div x-show="item.type === 'email'">
                                                <input type="text" x-model="item.label" placeholder="Display Text (e.g. Email Us)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <input type="text" x-model="item.url" placeholder="Email Address (e.g. hello@store.com)" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>

                                            <div x-show="item.type === 'legal'">
                                                <input type="text" x-model="item.label" placeholder="Override Title (Optional)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <select x-model="item.legal_slug" class="block w-full rounded border-gray-300 text-xs p-1">
                                                    <option value="">Select Policy Page</option>
                                                    @foreach($legalPages as $page)
                                                        <option value="{{ $page->slug }}">{{ $page->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div x-show="item.type === 'text'">
                                                <textarea x-model="item.content" placeholder="E.g. Address: 123 Street Name" rows="2" class="block w-full rounded border-gray-300 text-xs p-1"></textarea>
                                            </div>

                                            <div x-show="item.type === 'image_text'">
                                                <div class="mb-2">
                                                    <template x-if="item.image_url">
                                                        <img :src="item.image_url" class="h-12 w-auto mb-1 rounded bg-white shadow-sm border border-gray-200 object-contain">
                                                    </template>
                                                    <input type="file" @change="uploadImage(colIndex, itemIndex, $event)" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 mb-1">
                                                </div>
                                                <input type="text" x-model="item.image_link" placeholder="Image Link URL (Optional)" class="block w-full rounded border-gray-300 text-xs p-1 mb-1">
                                                <textarea x-model="item.content" placeholder="Text below image..." rows="2" class="block w-full rounded border-gray-300 text-xs p-1 mb-1"></textarea>
                                                <input type="text" x-model="item.text_link" placeholder="Text Link URL (Optional)" class="block w-full rounded border-gray-300 text-xs p-1">
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="addItem(colIndex)" class="w-full py-2 border border-dashed border-gray-300 text-gray-500 rounded text-xs font-bold hover:bg-gray-50 transition-colors">
                                    + Add Item
                                </button>
                            </div>
                        </template>
                    </div>

                </div>

            </div>
        </form>
    </div>

    <!-- Inject legal pages for JS helper -->
    <script>
        window.legalPages = @json($legalPages);
    </script>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('footerBuilder', () => ({
            columns: [],
            dragCol: null,
            dragItem: null,
            init() {
                try {
                    let saved = {!! json_encode($settings['footer_structure']) !!};
                    if (typeof saved === 'string') {
                        saved = JSON.parse(saved);
                    }
                    if (Array.isArray(saved)) {
                        this.columns = saved;
                    }
                } catch (e) {
                    this.columns = [];
                }
            },
            generateId() {
                return 'id_' + Math.random().toString(36).substr(2, 9);
            },
            addColumn() {
                if(this.columns.length >= 4) {
                    alert('Maximum 4 columns allowed.');
                    return;
                }
                this.columns.push({
                    id: this.generateId(),
                    title: 'New Column',
                    items: []
                });
            },
            removeColumn(index) {
                if (confirm('Remove this column?')) {
                    this.columns.splice(index, 1);
                }
            },
            addItem(colIndex) {
                this.columns[colIndex].items.push({
                    id: this.generateId(),
                    type: 'link',
                    label: '',
                    url: '',
                    legal_slug: '',
                    content: '',
                    image_url: ''
                });
            },
            removeItem(colIndex, itemIndex) {
                this.columns[colIndex].items.splice(itemIndex, 1);
            },
            moveColumn(fromIndex, toIndex) {
                if (fromIndex === null || fromIndex === toIndex) return;
                const element = this.columns.splice(fromIndex, 1)[0];
                this.columns.splice(toIndex, 0, element);
            },
            moveItemTo(dragObj, toCol, toItem) {
                if (!dragObj) return;
                const {col: fromCol, item: fromItem} = dragObj;
                if (fromCol === toCol && fromItem === toItem) return;

                const element = this.columns[fromCol].items.splice(fromItem, 1)[0];
                this.columns[toCol].items.splice(toItem, 0, element);
            },
            async uploadImage(colIndex, itemIndex, event) {
                const file = event.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch('{{ route("admin.online-store.mnpages.upload-image") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.columns[colIndex].items[itemIndex].image_url = data.url;
                    } else {
                        alert(data.error || 'Upload failed');
                    }
                } catch (err) {
                    alert('Upload failed: ' + err.message);
                }
            }
        }));
    });
</script>
@endpush
