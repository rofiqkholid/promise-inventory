<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\InventoryModel\Menu;

$user = User::with('roles')->find(1);
echo "User: " . ($user->name ?? 'Not found') . "\n";
echo "Roles: " . $user->roles->pluck('name', 'id') . "\n";

$targetMenu = Menu::where('route', 'inventory.tool.category.index')->with('roles')->first();
if ($targetMenu) {
    echo "Menu: " . $targetMenu->title . " (ID: " . $targetMenu->id . ")\n";
    echo "Allowed Roles: " . $targetMenu->roles->pluck('name', 'id') . "\n";
} else {
    echo "Menu not found!\n";
}

$dashboardMenu = Menu::where('route', 'inventory.tool.dashboard')->with('roles')->first();
if ($dashboardMenu) {
    echo "Dashboard Menu ID: " . $dashboardMenu->id . "\n";
    echo "Dashboard Allowed Roles: " . $dashboardMenu->roles->pluck('name', 'id') . "\n";
}
