<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->nullableMorphs('model');
            $table->string('collection_name', 100)->default('default');
            $table->string('disk', 50)->default('public');
            $table->string('path');                    // relative path on disk
            $table->string('filename');                // stored filename (uuid.webp)
            $table->string('original_filename');       // original upload name
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');        // bytes
            $table->json('conversions')->nullable();   // {thumb: path, webp: path, avif: path}
            $table->json('custom_properties')->nullable();
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'collection_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
