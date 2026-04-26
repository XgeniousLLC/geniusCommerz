<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating'); // 1–5
            $table->string('title', 150)->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_approved')->default(true); // auto-approve; set false to moderate
            $table->timestamps();
            $table->unique(['product_id', 'user_id']); // one review per product per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
