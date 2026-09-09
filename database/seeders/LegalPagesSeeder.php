<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>This is the privacy policy.</p>',
                'is_mandatory' => true,
                'is_published' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p>These are the terms of service.</p>',
                'is_mandatory' => true,
                'is_published' => true,
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookie-policy',
                'content' => '<h1>Cookie Policy</h1><p>This is the cookie policy.</p>',
                'is_mandatory' => true,
                'is_published' => true,
            ],
            [
                'title' => 'Grievance Redressal',
                'slug' => 'grievance-redressal',
                'content' => '<h1>Grievance Redressal</h1><p>Contact our Data Protection Officer.</p>',
                'is_mandatory' => true,
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::firstOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
