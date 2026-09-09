<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminSettingController extends Controller
{
    /**
     * Display page 1: Admin Setting (Name, Email, Password of logged-in user)
     */
    public function index()
    {
        $user = auth()->user();

        return view('admin.settings.index', compact('user'));
    }

    /**
     * Update page 1: Admin Setting (logged-in user profile)
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $request->strictValidate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Display page 2: Admin User Setting (Create admin user, assign role, assign module access)
     */
    public function users()
    {
        $adminRoles = ['administrator', 'editor', 'manager', 'customer_service'];
        $users = User::whereIn('role', $adminRoles)->get();

        $roles = [
            'administrator' => 'Administrator (Full Access)',
            'editor' => 'Editor (Content & Pages)',
            'manager' => 'Manager (Orders & Inventory)',
            'customer_service' => 'Customer Service (Support & Orders)',
        ];

        $modules = [
            'Inventory' => 'Inventory (Products, Categories, Size Charts, Bulk Upload)',
            'Marketing & Sales' => 'Marketing & Sales (Orders, Coupons, Gift Cards)',
            'Customize Store' => 'Customize Store (Pages, Navbar, Theme, Brand, Policies)',
            'Settings' => 'Settings (Store, Taxes, Delivery PINs, Integrations)',
        ];

        return view('admin.settings.users', compact('users', 'roles', 'modules'));
    }

    /**
     * Store page 2: Admin User Setting (Create admin user)
     */
    public function storeUser(Request $request)
    {
        $request->strictValidate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'module_access' => ['nullable', 'array'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'module_access' => $request->module_access ?? [],
        ]);

        return redirect()->back()->with('success', 'Admin user created successfully.');
    }

    /**
     * Update page 2: Admin User Setting (Update admin user details, role, module access)
     */
    public function updateUser(Request $request, User $user)
    {
        $request->strictValidate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'module_access' => ['nullable', 'array'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->module_access = $request->module_access ?? [];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Admin user updated successfully.');
    }

    /**
     * Delete page 2: Admin User Setting (Delete admin user)
     */
    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own admin account.']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'Admin user deleted successfully.');
    }

    /**
     * Display page 3: Project Vyora (Opensource project info page)
     */
    public function vyora()
    {
        $currentVersion = config('app.version', '1.0.0');
        $latestVersion = $currentVersion;

        try {
            $response = Http::withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                ->timeout(5)
                ->get('https://api.github.com/repos/SaaSance-Softwares/vyora-web/releases/latest');

            if ($response->successful()) {
                $release = $response->json();
                $latestVersion = str_replace('v', '', $release['tag_name'] ?? $currentVersion);
            }
        } catch (\Exception $e) {
            Log::error('GitHub Version Check Failed in vyora: '.$e->getMessage());
        }

        return view('admin.settings.vyora', compact('currentVersion', 'latestVersion'));
    }
    public function manifest()
    {
        $adminPath = config('app.admin_path', 'admin');
        $appName = \App\Models\ThemeSetting::where('group', 'general')->where('key', 'store_name')->value('value') ?? 'Store Admin';
        
        return response()->json([
            "name" => $appName . " Admin",
            "short_name" => $appName . " Admin",
            "description" => $appName . " Administration Panel",
            "start_url" => "/" . $adminPath,
            "display" => "standalone",
            "background_color" => "#ffffff",
            "theme_color" => "#0f172a",
            "icons" => [
                [
                    "src" => asset("favicon.ico"),
                    "sizes" => "64x64 32x32 24x24 16x16",
                    "type" => "image/x-icon"
                ],
                [
                    "src" => asset("favicon.png"),
                    "type" => "image/png",
                    "sizes" => "192x192",
                    "purpose" => "any maskable"
                ],
                [
                    "src" => asset("favicon.png"),
                    "type" => "image/png",
                    "sizes" => "512x512",
                    "purpose" => "any maskable"
                ]
            ]
        ]);
    }
}