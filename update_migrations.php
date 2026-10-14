<?php
$dir = __DIR__ . '/database/migrations/';
$files = scandir($dir);

$migrations = [
    'create_rooms_table' => '
    public function up(): void
    {
        Schema::create("rooms", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("type");
            $table->text("description")->nullable();
            $table->integer("capacity");
            $table->decimal("price_per_hour", 10, 2);
            $table->string("image")->nullable();
            $table->string("status")->default("active");
            $table->timestamps();
        });
    }',
    'create_products_table' => '
    public function up(): void
    {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->decimal("price", 10, 2);
            $table->string("image")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }',
    'create_bookings_table' => '
    public function up(): void
    {
        Schema::create("bookings", function (Blueprint $table) {
            $table->id();
            $table->string("booking_code")->unique();
            $table->foreignId("room_id")->constrained()->onDelete("cascade");
            $table->string("customer_name");
            $table->string("customer_phone");
            $table->date("booking_date");
            $table->time("start_time");
            $table->time("end_time");
            $table->integer("duration");
            $table->decimal("room_price", 10, 2);
            $table->decimal("product_total", 10, 2)->default(0);
            $table->decimal("total_price", 10, 2);
            $table->string("status")->default("pending");
            $table->timestamps();
        });
    }',
    'create_booking_items_table' => '
    public function up(): void
    {
        Schema::create("booking_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("booking_id")->constrained()->onDelete("cascade");
            $table->foreignId("product_id")->constrained()->onDelete("cascade");
            $table->integer("quantity");
            $table->decimal("price", 10, 2);
            $table->decimal("subtotal", 10, 2);
            $table->timestamps();
        });
    }',
    'create_payments_table' => '
    public function up(): void
    {
        Schema::create("payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("booking_id")->constrained()->onDelete("cascade");
            $table->string("payment_method")->nullable();
            $table->string("transaction_id")->nullable();
            $table->decimal("amount", 10, 2);
            $table->string("status")->default("pending");
            $table->timestamp("paid_at")->nullable();
            $table->timestamps();
        });
    }'
];

foreach ($files as $file) {
    if (strpos($file, ".php") !== false) {
        $content = file_get_contents($dir . $file);
        foreach ($migrations as $key => $upContent) {
            if (strpos($file, $key) !== false) {
                // Replace up() method
                $content = preg_replace("/public function up\(\): void\s*\{(.*?)\}/s", $upContent, $content, 1);
                file_put_contents($dir . $file, $content);
                echo "Updated $file\n";
            }
        }
    }
}
echo "Done!\n";
