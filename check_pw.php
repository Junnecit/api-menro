<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function demoPassword(string $email): string {
    if ($email === 'superadmin@example.com') return 'password';
    return 'Menro' . substr(hash('sha256', 'menro-tagoloan-demo|' . strtolower($email)), 0, 8);
}

$super = App\Models\User::where('email', 'superadmin@example.com')->first();
if ($super) {
    $check = Illuminate\Support\Facades\Hash::check('password', $super->password);
    echo "superadmin@example.com check('password'): " . ($check ? 'TRUE' : 'FALSE') . "\n";
    echo "Raw hash in DB: " . $super->password . "\n";
}

$admin = App\Models\User::where('email', 'admin.menro@tagoloan.demo')->first();
if ($admin) {
    $pwd = demoPassword($admin->email);
    $check = Illuminate\Support\Facades\Hash::check($pwd, $admin->password);
    echo "admin.menro@tagoloan.demo check('{$pwd}'): " . ($check ? 'TRUE' : 'FALSE') . "\n";
}

$planter = App\Models\User::where('email', 'planter1.menro@tagoloan.demo')->first();
if ($planter) {
    $pwd = demoPassword($planter->email);
    $check = Illuminate\Support\Facades\Hash::check($pwd, $planter->password);
    echo "planter1.menro@tagoloan.demo check('{$pwd}'): " . ($check ? 'TRUE' : 'FALSE') . "\n";
}
