@extends('layouts.admin')

@section('header', 'Admin User Settings')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8 pb-12">
    {{-- ── USER DIRECTORY ─────────────────────────────────── --}}
    <div class="xl:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-150 bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">User Directory</h3>
                <p class="text-sm text-gray-500">A list of all users who have administrative access to this system.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-black text-white">
                {{ $users->count() }} Total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Module Access</th>
                        <th scope="col" class="px-6 py-3 class=text-right text-xs font-semibold text-gray-500 uppercase tracking-wider relative px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 text-black font-semibold rounded-full flex items-center justify-center border border-gray-200">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 flex items-center">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                        @if($u->phone)
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $u->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                                    @if(($u->role ?? 'administrator') === 'administrator') bg-slate-900 text-white 
                                    @elseif(($u->role ?? 'administrator') === 'editor') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif(($u->role ?? 'administrator') === 'manager') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-purple-50 text-purple-700 border border-purple-200 @endif">
                                    {{ $roles[$u->role ?? 'administrator'] ?? ucwords(str_replace('_', ' ', $u->role ?? 'administrator')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-[280px]">
                                    @if(($u->role ?? 'administrator') === 'administrator')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-300">All Modules</span>
                                    @else
                                        @php
                                            $userAccess = $u->module_access ?? [];
                                        @endphp
                                        @if(empty($userAccess))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-600 border border-rose-100">No Access Granted</span>
                                        @else
                                            @foreach($userAccess as $mod)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                    @if($mod === 'Inventory') bg-blue-50 text-blue-700 border border-blue-100
                                                    @elseif($mod === 'Marketing & Sales') bg-emerald-50 text-emerald-700 border border-emerald-100
                                                    @elseif($mod === 'Customize Store') bg-violet-50 text-violet-700 border border-violet-100
                                                    @else bg-indigo-50 text-indigo-700 border border-indigo-100 @endif">
                                                    {{ $mod }}
                                                </span>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button type="button" 
                                        onclick="editUser({{ json_encode($u) }})"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                        Edit
                                    </button>

                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.settings.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke admin access for {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-red-200 text-xs font-medium rounded text-red-600 bg-red-50 hover:bg-red-100 focus:outline-none">
                                                Revoke
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── MANAGEMENT PANEL ──────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 self-start">
        <h3 id="panel-title" class="text-lg font-bold text-gray-900 mb-1">Create Admin User</h3>
        <p id="panel-desc" class="text-sm text-gray-500 mb-6">Create a new administrator account and assign roles and access.</p>

        <form id="user-form" action="{{ route('admin.settings.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="field-name" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="field-email" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="field-phone" placeholder="optional"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div>
                    <label id="password-label" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="field-password" required
                        placeholder="Min 8 characters"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                    <p id="password-hint" class="text-[11px] text-gray-400 mt-1 hidden">Leave empty to keep existing password.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Type</label>
                    <select name="role" id="field-role" onchange="toggleModuleAccess(this.value)"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        @foreach($roles as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="module-access-container" class="border-t border-gray-150 pt-4 mt-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Module Access Permissions</label>
                    <p class="text-xs text-gray-500 mb-3">Granular module permissions for custom access control.</p>
                    
                    <div class="space-y-2">
                        @foreach($modules as $key => $name)
                            <label class="flex items-start p-2 rounded-md hover:bg-gray-50 cursor-pointer border border-gray-100">
                                <input type="checkbox" name="module_access[]" value="{{ $key }}"
                                    class="module-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-black focus:ring-black">
                                <span class="ml-3 text-xs text-gray-700">
                                    <span class="block font-medium text-gray-900">{{ $key }}</span>
                                    {{ $name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex items-center space-x-3">
                    <button type="submit" id="btn-submit"
                        class="flex-1 bg-black text-white px-4 py-2.5 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors shadow-sm">
                        Create Account
                    </button>
                    <button type="button" id="btn-cancel" onclick="resetForm()"
                        class="hidden px-4 py-2.5 border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors text-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleModuleAccess(role) {
        const container = document.getElementById('module-access-container');
        const checkboxes = document.querySelectorAll('.module-checkbox');
        
        if (role === 'administrator') {
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
            checkboxes.forEach(cb => {
                cb.checked = true;
            });
        } else {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            if (role === 'editor') {
                checkboxes.forEach(cb => {
                    cb.checked = (cb.value === 'Inventory' || cb.value === 'Customize Store');
                });
            } else if (role === 'manager') {
                checkboxes.forEach(cb => {
                    cb.checked = (cb.value === 'Inventory' || cb.value === 'Marketing & Sales');
                });
            } else if (role === 'customer_service') {
                checkboxes.forEach(cb => {
                    cb.checked = (cb.value === 'Marketing & Sales');
                });
            }
        }
    }

    function editUser(user) {
        document.getElementById('panel-title').innerText = 'Edit Admin User';
        document.getElementById('panel-desc').innerText = 'Modify account privileges, password, and module permissions.';
        
        document.getElementById('user-form').action = `/admin/settings/users/${user.id}`;
        document.getElementById('form-method').value = 'PUT';
        
        document.getElementById('field-name').value = user.name;
        document.getElementById('field-email').value = user.email;
        document.getElementById('field-phone').value = user.phone || '';
        
        // Password fields
        const pwdInput = document.getElementById('field-password');
        pwdInput.required = false;
        pwdInput.placeholder = '••••••••';
        document.getElementById('password-hint').classList.remove('hidden');
        document.getElementById('password-label').innerText = 'Change Password';

        document.getElementById('field-role').value = user.role || 'administrator';
        
        // Reset checkboxes
        const checkboxes = document.querySelectorAll('.module-checkbox');
        checkboxes.forEach(cb => cb.checked = false);

        // Check assigned access
        const access = user.module_access || [];
        checkboxes.forEach(cb => {
            if (access.includes(cb.value)) {
                cb.checked = true;
            }
        });

        toggleModuleAccess(user.role || 'administrator');
        
        // Show cancel button
        document.getElementById('btn-cancel').classList.remove('hidden');
        document.getElementById('btn-submit').innerText = 'Update Account';
        
        // Scroll to form smoothly on mobile
        document.getElementById('panel-title').scrollIntoView({ behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('panel-title').innerText = 'Create Admin User';
        document.getElementById('panel-desc').innerText = 'Create a new administrator account and assign roles and access.';
        
        document.getElementById('user-form').action = "{{ route('admin.settings.users.store') }}";
        document.getElementById('form-method').value = 'POST';
        
        document.getElementById('field-name').value = '';
        document.getElementById('field-email').value = '';
        document.getElementById('field-phone').value = '';
        
        const pwdInput = document.getElementById('field-password');
        pwdInput.required = true;
        pwdInput.placeholder = 'Min 8 characters';
        pwdInput.value = '';
        document.getElementById('password-hint').classList.add('hidden');
        document.getElementById('password-label').innerText = 'Password';

        document.getElementById('field-role').value = 'administrator';
        
        const checkboxes = document.querySelectorAll('.module-checkbox');
        checkboxes.forEach(cb => cb.checked = true);
        
        toggleModuleAccess('administrator');

        document.getElementById('btn-cancel').classList.add('hidden');
        document.getElementById('btn-submit').innerText = 'Create Account';
    }

    // Initialize module access states on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleModuleAccess(document.getElementById('field-role').value);
    });
</script>
@endpush
