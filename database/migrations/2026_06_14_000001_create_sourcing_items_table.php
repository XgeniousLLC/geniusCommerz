<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sourcing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('supplier');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('sell_price', 12, 2)->nullable();
            $table->unsignedInteger('moq')->default(1);
            $table->unsignedInteger('lead_days')->default(0);
            $table->enum('status', ['sourced', 'listed'])->default('sourced');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sourcing_items');
    }
};
