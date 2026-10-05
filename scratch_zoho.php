<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$settings = \App\Models\ThemeSetting::where('group', 'integration.zoho-books')->get();
echo "Settings count: " . $settings->count() . "\n";
foreach($settings as $s) {
    echo $s->key . "\n";
}
