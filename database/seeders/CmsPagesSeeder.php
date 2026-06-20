<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>About Us</h2><p>Welcome to our store. We are dedicated to providing the best quality products for our customers.</p><p>Our mission is to bring style and comfort to your everyday life.</p>'
                        ]
                    ]
                ],
                'meta_title' => 'About Us - Our Story',
                'meta_description' => 'Learn more about our brand and mission.',
                'is_active' => true,
                'is_home' => false,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>Contact Us</h2><p>Have questions? We are here to help.</p><ul><li>Email: support@example.com</li><li>Phone: +1 (555) 123-4567</li><li>Address: 123 Fashion Ave, New York, NY</li></ul>'
                        ]
                    ]
                ],
                'meta_title' => 'Contact Us - Get in Touch',
                'meta_description' => 'Contact our customer support team.',
                'is_active' => true,
                'is_home' => false,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information.</p><h3>Information We Collect</h3><p>We collect information you provide directly to us when you make a purchase, create an account, or contact us.</p>'
                        ]
                    ]
                ],
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Read our privacy policy.',
                'is_active' => true,
                'is_home' => false,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>Terms of Service</h2><p>By accessing our website, you agree to be bound by these terms of service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws.</p>'
                        ]
                    ]
                ],
                'meta_title' => 'Terms of Service',
                'meta_description' => 'Read our terms of service.',
                'is_active' => true,
                'is_home' => false,
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>Refund Policy</h2><p>We have a 30-day return policy, which means you have 30 days after receiving your item to request a return.</p><p>To be eligible for a return, your item must be in the same condition that you received it, unworn or unused, with tags, and in its original packaging.</p>'
                        ]
                    ]
                ],
                'meta_title' => 'Refund Policy',
                'meta_description' => 'Read our refund policy.',
                'is_active' => true,
                'is_home' => false,
            ],
            [
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'content' => '<h2>Shipping Policy</h2><p>All orders are processed within 1 to 3 business days (excluding weekends and holidays) after receiving your order confirmation email. You will receive another notification when your order has shipped.</p>'
                        ]
                    ]
                ],
                'meta_title' => 'Shipping Policy',
                'meta_description' => 'Read our shipping policy.',
                'is_active' => true,
                'is_home' => false,
            ],
        ];

        foreach ($pages as $page) {
            \App\Models\CmsPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
