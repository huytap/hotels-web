<?php
require 'booking/vendor/autoload.php';

$app = require_once 'booking/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking hotel status for wp_id=2:\n";
$hotel = App\Models\Hotel::where('wp_id', 2)->first();
if ($hotel) {
    echo "Hotel found:\n";
    echo "- ID: {$hotel->id}\n";
    echo "- WP_ID: {$hotel->wp_id}\n";
    echo "- Name: {$hotel->name}\n";
    echo "- is_active: " . ($hotel->is_active ? 'true' : 'false') . "\n";
    echo "- created_at: {$hotel->created_at}\n";
    echo "- updated_at: {$hotel->updated_at}\n";

    if (!$hotel->is_active) {
        echo "\n🔧 Activating hotel...\n";
        $hotel->is_active = true;
        $hotel->save();
        echo "✅ Hotel activated!\n";
    } else {
        echo "\n✅ Hotel is already active\n";
    }
} else {
    echo "❌ Hotel with wp_id=2 not found\n";
}
?>