<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

echo "=== LOGIN TEST FOR SUBSCRIBER ===\n\n";

// Get subscriber
$subscriber = User::find(3);
if (!$subscriber) {
    echo "❌ Subscriber not found!\n";
    exit(1);
}

echo "User: " . $subscriber->email . " (" . $subscriber->username . ")\n";
echo "Roles: " . json_encode($subscriber->getRoleNames()->toArray()) . "\n";

// Check permissions
echo "\n=== PERMISSION CHECK ===\n";
echo "Direct Permissions: " . json_encode($subscriber->getDirectPermissions()->pluck('name')->toArray()) . "\n";
echo "Role Permissions: " . json_encode($subscriber->getPermissionsViaRoles()->pluck('name')->toArray()) . "\n";
echo "All Permissions: " . json_encode($subscriber->getAllPermissions()->pluck('name')->toArray()) . "\n";

// Check can()
echo "\n=== CAN() METHOD CHECK ===\n";
$canView = $subscriber->can('dashboard.view');
echo "can('dashboard.view'): " . ($canView ? 'YES ✅' : 'NO ❌') . "\n";

// Try fresh
echo "\n=== FRESH() CHECK ===\n";
$fresh = $subscriber->fresh();
$canViewFresh = $fresh->can('dashboard.view');
echo "fresh()->can('dashboard.view'): " . ($canViewFresh ? 'YES ✅' : 'NO ❌') . "\n";

echo "\n";
if ($canViewFresh) {
    echo "✅ SUBSCRIBER SHOULD BE ABLE TO LOGIN AND ACCESS ADMIN!\n";
} else {
    echo "❌ SUBSCRIBER DOES NOT HAVE DASHBOARD.VIEW PERMISSION!\n";
    echo "\nRole details:\n";
    foreach ($subscriber->roles as $role) {
        echo "  Role: " . $role->name . "\n";
        echo "  Permissions in role: " . $role->permissions()->count() . "\n";
        foreach ($role->permissions as $perm) {
            echo "    - " . $perm->name . "\n";
        }
    }
}
