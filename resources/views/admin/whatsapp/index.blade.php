@extends('admin.whatsapp.layout')

@section('whatsapp_content')
<div class="flex h-[calc(100vh-14rem)] bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden" x-data="whatsappChat()">
    
    {{-- Sidebar: Conversations List --}}
    <div class="w-1/3 border-r border-gray-200 flex flex-col bg-gray-50/50 relative">
        
        {{-- New Chat Search & Add Button --}}
        <div class="p-4 border-b border-gray-200 bg-white z-20">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="searchCustomers"
                        class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400"
                        placeholder="Search customer to start chat...">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button @click="openCustomerModal" class="bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl px-3 py-2.5 transition-colors border border-gray-200 flex items-center justify-center" title="Browse all customers">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            
            {{-- Search Results Dropdown --}}
            <div x-show="searchResults.length > 0" @click.away="searchResults = []" style="display: none;"
                 class="absolute left-4 right-4 mt-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden max-h-60 overflow-y-auto">
                 <template x-for="cust in searchResults" :key="cust.id">
                     <button @click="startNewConversation(cust)" class="w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-gray-50 flex flex-col transition-colors">
                         <span class="font-bold text-sm text-gray-900" x-text="cust.name"></span>
                         <span class="text-[10px] text-gray-500" x-text="cust.phone"></span>
                     </button>
                 </template>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
                <button @click="openConversation({{ $conv->id }}, '{{ $conv->customer_name ?? $conv->phone_number }}', '{{ $conv->phone_number }}')"
                        class="w-full text-left p-4 border-b border-gray-100 hover:bg-white transition-colors focus:outline-none"
                        :class="activeConvId === {{ $conv->id }} ? 'bg-white border-l-4 border-l-[#25D366]' : 'border-l-4 border-l-transparent'">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-900 text-sm truncate">{{ $conv->customer_name ?? $conv->phone_number }}</span>
                        <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '' }}</span>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $conv->phone_number }}</p>
                </button>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">
                    No conversations yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Main Chat Area --}}
    <div class="w-2/3 flex flex-col bg-white relative">
        
        {{-- Empty State --}}
        <div x-show="!activeConvId" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 z-10">
            <div class="w-20 h-20 bg-[#25D366]/10 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Select a Conversation</h3>
            <p class="text-sm text-gray-500">Pick a customer from the left to start messaging</p>
        </div>

        {{-- Chat Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-white flex items-center justify-between" x-show="activeConvId">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                    <span x-text="activeName ? activeName.charAt(0).toUpperCase() : '?'"></span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900" x-text="activeName"></h3>
                    <p class="text-xs text-gray-500" x-text="activePhone"></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                
                {{-- Template Sending Dropdown (Bypass 24h window) --}}
                <div class="relative" x-data="{ openTemplate: false }">
                    <button @click="openTemplate = !openTemplate" type="button" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-bold text-gray-700 flex items-center gap-1 transition-colors border border-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Send Template
                    </button>
                    
                    <div x-show="openTemplate" @click.away="openTemplate = false" style="display: none;" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden z-20">
                        <div class="p-3 bg-gray-50 border-b border-gray-100">
                            <p class="text-xs font-black uppercase tracking-widest text-gray-500">Pick a Template</p>
                        </div>
                        <div class="max-h-60 overflow-y-auto p-1">
                            @if(isset($templates) && count($templates) > 0)
                                @foreach($templates as $template)
                                    <button @click="sendTemplate('{{ $template->name }}'); openTemplate = false" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg focus:outline-none transition-colors">
                                        <div class="font-bold">{{ $template->name }}</div>
                                        <div class="text-[10px] text-gray-400 truncate">{{ $template->category }}</div>
                                    </button>
                                @endforeach
                            @else
                                <div class="p-4 text-xs text-center text-gray-500">No templates found. Go to Templates Library to sync.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <span class="flex h-2 w-2 relative" title="Live Connection">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#25D366] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#25D366]"></span>
                </span>
            </div>
        </div>

        {{-- Messages Window --}}
        <div class="flex-1 overflow-y-auto p-6 bg-[#EFEAE2] flex flex-col space-y-4" id="chat-window" x-show="activeConvId">
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex flex-col max-w-[75%]" :class="msg.direction === 'outbound' ? 'self-end items-end' : 'self-start items-start'">
                    <div class="px-4 py-2 rounded-2xl shadow-sm text-sm relative"
                         :class="msg.direction === 'outbound' ? 'bg-[#D9FDD3] text-gray-900 rounded-tr-sm' : 'bg-white text-gray-900 rounded-tl-sm'">
                        <div x-show="msg.type === 'template'" class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1 border-b border-gray-200/50 pb-1">Automated Template</div>
                        <div class="whitespace-pre-wrap" x-text="msg.body"></div>
                        <div class="flex items-center gap-1 mt-1 justify-end">
                            <span class="text-[10px] text-gray-500/80" x-text="msg.created_at"></span>
                            <template x-if="msg.direction === 'outbound'">
                                <svg class="w-3 h-3" :class="msg.status === 'read' ? 'text-blue-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="text-xs text-gray-500 text-center italic">Loading...</div>
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-gray-50 border-t border-gray-200" x-show="activeConvId">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input type="text" x-model="newMessage" placeholder="Type a free-form message..." 
                       class="flex-1 bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm text-gray-900"
                       :disabled="sending">
                <button type="submit" 
                        class="bg-[#25D366] hover:bg-[#1DA851] text-white rounded-xl px-5 py-3 font-bold transition-colors disabled:opacity-50 flex items-center justify-center shadow-sm"
                        :disabled="!newMessage.trim() || sending">
                    <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>
            <p class="text-[10px] text-gray-400 mt-2 text-center">Note: Meta's 24-hour service window applies to free-form texts. Use "Send Template" to bypass.</p>
        </div>
    </div>

    {{-- Customer Selection Modal --}}
    <div x-show="showCustomerModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md mx-4 overflow-hidden flex flex-col max-h-[80vh]" @click.away="showCustomerModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Select Customer</h3>
                <button @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-2 relative">
                <div x-show="loadingCustomers" class="flex justify-center p-8">
                    <svg class="w-6 h-6 animate-spin text-[#25D366]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                
                <div x-show="!loadingCustomers && allCustomers.length === 0" class="text-center p-8 text-sm text-gray-500">
                    No customers found with phone numbers.
                </div>

                <template x-for="cust in allCustomers" :key="cust.id">
                     <button @click="startNewConversation(cust)" class="w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-gray-50 flex items-center justify-between transition-colors rounded-xl">
                         <div>
                             <span class="font-bold text-sm text-gray-900 block" x-text="cust.name"></span>
                             <span class="text-[10px] text-gray-500 block" x-text="cust.phone"></span>
                         </div>
                         <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                     </button>
                </template>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <p class="text-[10px] text-gray-500 text-center">Showing recently active customers with a valid phone number.</p>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    const BASE_URL = '{{ route('admin.whatsapp.index') }}';
    
    Alpine.data('whatsappChat', () => ({
        activeConvId: null,
        activeName: '',
        activePhone: '',
        messages: [],
        newMessage: '',
        loading: false,
        sending: false,
        pollInterval: null,
        searchQuery: '',
        searchResults: [],
        showCustomerModal: false,
        allCustomers: [],
        loadingCustomers: false,

        async openCustomerModal() {
            this.showCustomerModal = true;
            this.loadingCustomers = true;
            try {
                const res = await fetch(`${BASE_URL}/customers/search?all=1`);
                this.allCustomers = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingCustomers = false;
            }
        },

        async searchCustomers() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch(`${BASE_URL}/customers/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                this.searchResults = data;
            } catch (e) {
                console.error(e);
            }
        },

        async startNewConversation(customer) {
            this.searchResults = [];
            this.searchQuery = '';
            this.showCustomerModal = false;
            
            try {
                const res = await fetch(`${BASE_URL}/conversations/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name: customer.name, phone: customer.phone })
                });
                const data = await res.json();
                
                if(data.success) {
                    // Open the newly created or existing conversation
                    this.openConversation(data.conversation.id, data.conversation.name, data.conversation.phone);
                    
                    // Reload the page to refresh the sidebar list after 1 second, keeping the chat open isn't strictly necessary if we just reload,
                    // but since this is a simple implementation, let's just force a reload so the sidebar updates, and they can click it.
                    // Or better yet, just leave it open and when they refresh next time it will be in the sidebar.
                    alert('Conversation started! The window is now open.');
                }
            } catch(e) {
                console.error(e);
            }
        },

        openConversation(id, name, phone) {
            this.activeConvId = id;
            this.activeName = name;
            this.activePhone = phone;
            this.messages = [];
            this.loading = true;
            
            this.fetchMessages();

            // Start polling every 3 seconds
            if(this.pollInterval) clearInterval(this.pollInterval);
            this.pollInterval = setInterval(() => {
                this.fetchMessages(false); // background fetch
            }, 3000);
        },

        async fetchMessages(scrollToBottom = true) {
            if(!this.activeConvId) return;
            try {
                const res = await fetch(`${BASE_URL}/conversations/${this.activeConvId}/messages`);
                const data = await res.json();
                
                // Only scroll to bottom if new messages arrived
                const previousCount = this.messages.length;
                this.messages = data.messages;
                
                if (scrollToBottom || this.messages.length > previousCount) {
                    this.$nextTick(() => {
                        const win = document.getElementById('chat-window');
                        if(win) win.scrollTop = win.scrollHeight;
                    });
                }
            } catch (err) {
                console.error('Failed to fetch messages', err);
            } finally {
                this.loading = false;
            }
        },

        async sendMessage() {
            if(!this.newMessage.trim() || !this.activeConvId) return;
            
            const msgText = this.newMessage;
            this.newMessage = '';
            this.sending = true;

            try {
                const res = await fetch(`${BASE_URL}/conversations/${this.activeConvId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: msgText })
                });

                if(res.ok) {
                    await this.fetchMessages(true);
                } else {
                    alert('Failed to send message. Please ensure the 24-hour window is active.');
                }
            } catch(err) {
                console.error(err);
                alert('Network error.');
            } finally {
                this.sending = false;
            }
        },

        async sendTemplate(templateName) {
            if(!this.activeConvId || !templateName) return;
            
            this.sending = true;

            try {
                const res = await fetch(`${BASE_URL}/conversations/${this.activeConvId}/template`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ template_name: templateName })
                });

                if(res.ok) {
                    await this.fetchMessages(true);
                } else {
                    alert('Failed to send template. Verify template status in Meta.');
                }
            } catch(err) {
                console.error(err);
                alert('Network error.');
            } finally {
                this.sending = false;
            }
        }
    }));
});
</script>
@endsection
