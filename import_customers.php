<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

echo "Starting Zoho Customer Import...\n\n";

$csvFile = __DIR__ . '/customers.csv';

if (!file_exists($csvFile)) {
    die("❌ Error: customers.csv not found in the root directory.\n");
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle); // skip header

$successCount = 0;
$skipCount = 0;

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 10) continue;

    $firstName = trim($row[0]);
    $lastName = trim($row[1]);
    $fullName = trim($firstName . ' ' . $lastName);
    $email = trim($row[2]);
    $phone = trim($row[3]);
    
    // Format phone
    if (!empty($phone)) {
        // Strip any non-numeric characters just in case
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '91') && strlen($phone) == 12) {
            $phone = '+' . $phone;
        } elseif (strlen($phone) == 10) {
            $phone = '+91' . $phone;
        } else {
            $phone = '+' . $phone;
        }
    }

    $address1 = trim($row[4]);
    $address2 = trim($row[5]);
    $city = trim($row[6]);
    $state = trim($row[7]);
    $zip = trim($row[8]);
    $country = trim($row[9]);

    // Check if user exists
    $user = User::where('email', $email)->orWhere('phone', $phone)->first();

    if ($user) {
        echo "⏭️  Skipping {$fullName} - User already exists (Email/Phone matched).\n";
        $skipCount++;
        continue;
    }

    // Create User
    $user = User::create([
        'name' => $fullName,
        'email' => strtolower($email),
        'phone' => $phone,
        'password' => Hash::make(Str::random(16)), // Random secure password, they will login via OTP anyway
        'email_verified_at' => now(), // Auto verify since they are real Zoho customers
    ]);

    // Create Address
    Address::create([
        'user_id' => $user->id,
        'name' => $fullName,
        'email' => strtolower($email),
        'phone' => $phone,
        'address_line1' => $address1,
        'address_line2' => $address2,
        'city' => $city,
        'state' => $state,
        'zip_code' => $zip,
        'country' => 'India', // Hardcode India just to be clean
        'is_default' => true,
        'type' => 'home'
    ]);

    echo "✅ Imported: {$fullName}\n";
    $successCount++;
}

fclose($handle);

echo "\n🎉 Import Complete! Successfully imported {$successCount} customers. Skipped {$skipCount} existing users.\n";
