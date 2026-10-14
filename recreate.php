<?php
$files = [
    '2026_10_05_064318_create_rooms_table.php' => '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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
    }
    public function down(): void {
        Schema::dropIfExists("rooms");
    }
};',
    '2026_10_05_064319_create_products_table.php' => '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->decimal("price", 10, 2);
            $table->string("image")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("products");
    }
};',
    '2026_10_05_064320_create_bookings_table.php' => '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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
    }
    public function down(): void {
        Schema::dropIfExists("bookings");
    }
};',
    '2026_10_05_064321_create_booking_items_table.php' => '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("booking_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("booking_id")->constrained()->onDelete("cascade");
            $table->foreignId("product_id")->constrained()->onDelete("cascade");
            $table->integer("quantity");
            $table->decimal("price", 10, 2);
            $table->decimal("subtotal", 10, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("booking_items");
    }
};',
    '2026_10_05_064322_create_payments_table.php' => '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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
    }
    public function down(): void {
        Schema::dropIfExists("payments");
    }
};'
];

foreach ($files as $name => $content) {
    file_put_contents(__DIR__ . "/database/migrations/" . $name, $content);
}
