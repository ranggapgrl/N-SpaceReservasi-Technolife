<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$room = App\Models\Room::first();
echo "Type: " . gettype($room->facilities) . "\n";
var_dump($room->facilities);
