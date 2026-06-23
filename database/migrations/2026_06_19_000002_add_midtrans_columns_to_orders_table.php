<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('user_address_id')->nullable();
                $table->string('order_number', 50)->unique();
                $table->unsignedInteger('subtotal')->default(0);
                $table->unsignedInteger('shipping_cost')->default(0);
                $table->unsignedInteger('total_amount')->default(0);
                $table->string('status', 40)->default('pending');
                $table->string('payment_status', 80)->nullable();
                $table->string('payment_type', 80)->nullable();
                $table->string('selected_payment_method', 80)->default('all');
                $table->string('selected_payment_label', 120)->default('Semua Metode Aktif');
                $table->longText('enabled_payments')->nullable();
                $table->string('payment_bank', 80)->nullable();
                $table->string('payment_store', 80)->nullable();
                $table->string('payment_code', 120)->nullable();
                $table->string('va_number', 120)->nullable();
                $table->string('biller_code', 80)->nullable();
                $table->string('bill_key', 120)->nullable();
                $table->string('acquirer', 80)->nullable();
                $table->text('pdf_url')->nullable();
                $table->longText('payment_detail')->nullable();
                $table->string('fraud_status', 80)->nullable();
                $table->string('midtrans_transaction_id', 120)->nullable();
                $table->string('midtrans_order_id', 120)->nullable();
                $table->string('snap_token')->nullable();
                $table->text('snap_redirect_url')->nullable();
                $table->longText('midtrans_response')->nullable();
                $table->longText('callback_payload')->nullable();
                $table->longText('shipping_snapshot')->nullable();
                $table->text('customer_note')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();
            });
        } else {
            $this->addOrdersColumns();
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->text('product_image')->nullable();
                $table->unsignedInteger('price')->default(0);
                $table->unsignedInteger('quantity')->default(1);
                $table->unsignedInteger('line_total')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('order_items', 'product_image')) {
                    $table->text('product_image')->nullable()->after('product_name');
                }
                if (! Schema::hasColumn('order_items', 'line_total')) {
                    $table->unsignedInteger('line_total')->default(0)->after('quantity');
                }
            });
        }
    }

    private function addOrdersColumns(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'user_address_id')) $table->unsignedBigInteger('user_address_id')->nullable()->after('user_id');
            if (! Schema::hasColumn('orders', 'shipping_cost')) $table->unsignedInteger('shipping_cost')->default(0)->after('subtotal');
            if (! Schema::hasColumn('orders', 'payment_status')) $table->string('payment_status', 80)->nullable()->after('status');
            if (! Schema::hasColumn('orders', 'payment_type')) $table->string('payment_type', 80)->nullable()->after('payment_status');
            if (! Schema::hasColumn('orders', 'selected_payment_method')) $table->string('selected_payment_method', 80)->default('all')->after('payment_type');
            if (! Schema::hasColumn('orders', 'selected_payment_label')) $table->string('selected_payment_label', 120)->default('Semua Metode Aktif')->after('selected_payment_method');
            if (! Schema::hasColumn('orders', 'enabled_payments')) $table->longText('enabled_payments')->nullable()->after('selected_payment_label');
            if (! Schema::hasColumn('orders', 'payment_bank')) $table->string('payment_bank', 80)->nullable()->after('enabled_payments');
            if (! Schema::hasColumn('orders', 'payment_store')) $table->string('payment_store', 80)->nullable()->after('payment_bank');
            if (! Schema::hasColumn('orders', 'payment_code')) $table->string('payment_code', 120)->nullable()->after('payment_store');
            if (! Schema::hasColumn('orders', 'va_number')) $table->string('va_number', 120)->nullable()->after('payment_code');
            if (! Schema::hasColumn('orders', 'biller_code')) $table->string('biller_code', 80)->nullable()->after('va_number');
            if (! Schema::hasColumn('orders', 'bill_key')) $table->string('bill_key', 120)->nullable()->after('biller_code');
            if (! Schema::hasColumn('orders', 'acquirer')) $table->string('acquirer', 80)->nullable()->after('bill_key');
            if (! Schema::hasColumn('orders', 'pdf_url')) $table->text('pdf_url')->nullable()->after('acquirer');
            if (! Schema::hasColumn('orders', 'payment_detail')) $table->longText('payment_detail')->nullable()->after('pdf_url');
            if (! Schema::hasColumn('orders', 'fraud_status')) $table->string('fraud_status', 80)->nullable()->after('payment_detail');
            if (! Schema::hasColumn('orders', 'midtrans_transaction_id')) $table->string('midtrans_transaction_id', 120)->nullable()->after('fraud_status');
            if (! Schema::hasColumn('orders', 'midtrans_order_id')) $table->string('midtrans_order_id', 120)->nullable()->after('midtrans_transaction_id');
            if (! Schema::hasColumn('orders', 'snap_token')) $table->string('snap_token')->nullable()->after('midtrans_order_id');
            if (! Schema::hasColumn('orders', 'snap_redirect_url')) $table->text('snap_redirect_url')->nullable()->after('snap_token');
            if (! Schema::hasColumn('orders', 'midtrans_response')) $table->longText('midtrans_response')->nullable()->after('snap_redirect_url');
            if (! Schema::hasColumn('orders', 'callback_payload')) $table->longText('callback_payload')->nullable()->after('midtrans_response');
            if (! Schema::hasColumn('orders', 'shipping_snapshot')) $table->longText('shipping_snapshot')->nullable()->after('callback_payload');
            if (! Schema::hasColumn('orders', 'customer_note')) $table->text('customer_note')->nullable()->after('shipping_snapshot');
            if (! Schema::hasColumn('orders', 'paid_at')) $table->timestamp('paid_at')->nullable()->after('customer_note');
            if (! Schema::hasColumn('orders', 'expired_at')) $table->timestamp('expired_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        // Hotfix migration: kolom tidak di-drop agar data transaksi tidak hilang.
    }
};
