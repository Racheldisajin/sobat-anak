<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('user_address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
                $table->string('order_number', 50)->unique();
                $table->unsignedInteger('subtotal')->default(0);
                $table->unsignedInteger('shipping_cost')->default(0);
                $table->unsignedInteger('total_amount')->default(0);
                $table->string('status', 40)->default('pending')->index();
                $table->string('payment_status', 80)->nullable();
                $table->string('payment_type', 80)->nullable();
                $table->string('fraud_status', 80)->nullable();
                $table->string('midtrans_transaction_id', 120)->nullable()->index();
                $table->string('midtrans_order_id', 120)->nullable()->index();
                $table->string('snap_token')->nullable();
                $table->text('snap_redirect_url')->nullable();
                $table->json('midtrans_response')->nullable();
                $table->json('callback_payload')->nullable();
                $table->json('shipping_snapshot')->nullable();
                $table->text('customer_note')->nullable();
                $table->timestamp('paid_at')->nullable()->index();
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('product_name');
                $table->text('product_image')->nullable();
                $table->unsignedInteger('price')->default(0);
                $table->unsignedInteger('quantity')->default(1);
                $table->unsignedInteger('line_total')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
