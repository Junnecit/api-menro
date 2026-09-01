<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function getVerifiedPassword(App\Models\User $user): string {
    $candidates = [
        'password',
        'Menro' . substr(hash('sha256', 'menro-tagoloan-demo|' . strtolower($user->email)), 0, 8),
        'secret',
        'admin123',
    ];

    foreach ($candidates as $cand) {
        if (Illuminate\Support\Facades\Hash::check($cand, $user->password)) {
            return $cand;
        }
    }

    return '[UNKNOWN HASH]';
}

$users = App\Models\User::with(['role', 'agency'])->get();

$results = [];
foreach ($users as $u) {
    $results[] = [
        'role' => $u->role?->slug ?? 'user',
        'name' => $u->name,
        'email' => $u->email,
        'verified_password' => getVerifiedPassword($u),
        'agency' => $u->agency?->name ?? ($u->role?->slug === 'super-admin' ? 'MENRO HQ' : 'None'),
    ];
}

file_put_contents('verified_accounts.json', json_encode($results, JSON_PRETTY_PRINT));
echo "Dumped " . count($results) . " accounts.\n";
