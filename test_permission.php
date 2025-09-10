<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a member user
$member = App\Models\User::factory()->create();
$member->assignRole('member');

echo "Member permissions: ";
print_r($member->getAllPermissions()->pluck('name')->toArray());
echo "Has view courses permission: " . ($member->hasPermissionTo('view courses') ? 'Yes' : 'No') . "\n";

// Create an admin user
$admin = App\Models\User::factory()->create();
$admin->assignRole('admin');

echo "Admin permissions: ";
print_r($admin->getAllPermissions()->pluck('name')->toArray());
echo "Has view courses permission: " . ($admin->hasPermissionTo('view courses') ? 'Yes' : 'No') . "\n";
