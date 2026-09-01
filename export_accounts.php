<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function demoPassword(string $email): string {
    if (strtolower($email) === 'superadmin@example.com') return 'password';
    return 'Menro' . substr(hash('sha256', 'menro-tagoloan-demo|' . strtolower($email)), 0, 8);
}

$users = App\Models\User::with(['role', 'agency'])->get();

$data = [];
foreach ($users as $u) {
    $data[] = [
        'id' => $u->id,
        'role' => $u->role?->name ?? 'User',
        'name' => $u->name,
        'email' => $u->email,
        'password' => demoPassword($u->email),
        'status' => $u->status?->value ?? (string)$u->status,
        'agency' => $u->agency?->name ?? ($u->role?->slug === 'super-admin' ? 'MENRO HQ' : 'None'),
    ];
}

file_put_contents('demo_accounts.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Successfully generated demo_accounts.json with " . count($data) . " accounts.\n";
