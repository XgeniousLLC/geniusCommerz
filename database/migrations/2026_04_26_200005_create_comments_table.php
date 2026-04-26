<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->text('body');
            $table->tinyInteger('rating')->nullable()->unsigned();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
