<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. PO-2026-001
            $table->string('supplier_name');
            $table->date('order_date');
            $table->enum('status', ['draft', 'ordered', 'partial', 'received'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->unsignedInteger('received_qty')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('shipment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_spends', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['meta', 'google', 'tiktok', 'youtube', 'other']);
            $table->string('campaign_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('spend_date');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spends');
        Schema::dropIfExists('purchase_shipments');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
