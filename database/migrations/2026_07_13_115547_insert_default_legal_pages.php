<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '',
                'is_mandatory' => true,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '',
                'is_mandatory' => true,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookie-policy',
                'content' => '',
                'is_mandatory' => true,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Grievance Redressal',
                'slug' => 'grievance-redressal',
                'content' => '',
                'is_mandatory' => true,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            $exists = DB::table('legal_pages')->where('slug', $page['slug'])->exists();
            if (!$exists) {
                DB::table('legal_pages')->insert($page);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('legal_pages')->whereIn('slug', [
            'privacy-policy',
            'terms-of-service',
            'cookie-policy',
            'grievance-redressal'
        ])->where('is_mandatory', true)->delete();
    }
};
