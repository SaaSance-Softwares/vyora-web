<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AuthSettingsController extends Controller
{
    const GROUP = 'auth_settings';

    public function index()
    {
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');

        $defaultAuthFields = [
            'name' => ['visible' => true, 'required' => true, 'auth_type' => 'data_entry'],
            'email' => ['visible' => true, 'required' => true, 'auth_type' => 'data_entry'],
            'phone' => [
                'visible' => true, 
                'required' => false, 
                'auth_type' => 'data_entry',
                'sms_template' => 'Your Dope Style login OTP is {otp}. Valid for 5 minutes.',
                'whatsapp_template' => 'user_verification_otp'
            ]
        ];

        // Merge defaults carefully with DB value
        $dbAuthFields = json_decode($rows->get('auth_fields')?->value ?? '{}', true);
        $mergedAuthFields = array_replace_recursive($defaultAuthFields, $dbAuthFields);

        // Defaults
        $settings = [
            'auth_fields' => $mergedAuthFields,
            'auth_social' => json_decode($rows->get('auth_social')?->value ?? '{"google":{"enabled":false,"client_id":""},"facebook":{"enabled":false,"client_id":""}}', true),
            'auth_appearance' => json_decode($rows->get('auth_appearance')?->value ?? '{"ux_mode":"page","border_radius":"16","border_color":"#e5e7eb"}', true),
            'auth_header' => json_decode($rows->get('auth_header')?->value ?? '{"text":"Welcome Back","image":"","order":["image","text"],"image_width":"120"}', true),
            'auth_footer' => json_decode($rows->get('auth_footer')?->value ?? '{"text":"Secure payment powered by Dope Style"}', true),
        ];

        $brandRows = ThemeSetting::where('group', 'typography')->orWhere('group', 'colors')->get()->keyBy('key');
        $brand = [
            'heading_font' => $brandRows->get('heading_font')?->value ?? 'Inter',
            'primary_color' => $brandRows->get('primary_color')?->value ?? '#000000',
        ];

        // Check if Twilio and WhatsApp are enabled
        $twilioEnabled = ThemeSetting::where('group', 'integration.twilio')->where('key', 'enabled')->value('value') === '1';
        $whatsappEnabled = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'enabled')->value('value') === '1';

        return view('admin.auth-settings.index', compact('settings', 'brand', 'twilioEnabled', 'whatsappEnabled'));
    }

    public function update(Request $request)
    {
        $data = $request->all();

        // Handle JSON fields
        $jsonFields = ['auth_methods', 'auth_fields', 'auth_social', 'auth_appearance', 'auth_header', 'auth_footer'];

        foreach ($jsonFields as $field) {
            if ($request->has($field)) {
                $newValue = $request->input($field);
                if (is_string($newValue)) {
                    $newValue = json_decode($newValue, true);
                }
                
                // Fetch existing to merge (important for preserving image paths)
                $existing = ThemeSetting::where('key', $field)->first();
                $existingVal = json_decode($existing?->value ?? '{}', true);
                
                $finalValue = array_merge($existingVal, is_array($newValue) ? $newValue : []);

                if ($field === 'auth_header' && isset($finalValue['order']) && is_array($finalValue['order'])) {
                    $finalValue['order'] = array_values(array_unique(array_filter($finalValue['order'], fn($v) => in_array($v, ['image', 'text']))));
                    if (empty($finalValue['order'])) {
                        $finalValue['order'] = ['image', 'text'];
                    }
                }

                ThemeSetting::updateOrCreate(
                    ['key' => $field],
                    ['value' => json_encode($finalValue), 'group' => self::GROUP]
                );
            }
        }

        // Handle Image Uploads for Header
        $this->handleImageUpload($request, 'auth_header_image', 'auth_header');

        return redirect()->back()->with('success', 'Auth settings updated successfully.');
    }

    private function handleImageUpload(Request $request, $fileKey, $settingKey)
    {
        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = 'uploads/auth/' . $filename;

            // Save to backend public
            $file->move(public_path('uploads/auth'), $filename);

            // Update JSON setting
            $setting = ThemeSetting::where('key', $settingKey)->first();
            $val = json_decode($setting?->value ?? '{}', true);
            $val['image'] = '/' . $path;
            
            ThemeSetting::updateOrCreate(
                ['key' => $settingKey],
                ['value' => json_encode($val), 'group' => self::GROUP]
            );
        }
    }
}
