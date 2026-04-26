<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->morphs('translatable');
            $table->json('fields');
            $table->timestamps();

            $table->unique(['language_id', 'translatable_type', 'translatable_id'], 'ct_lang_model_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
