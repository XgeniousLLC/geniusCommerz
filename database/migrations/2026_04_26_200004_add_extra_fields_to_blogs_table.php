<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->unsignedBigInteger('author_admin_id')->nullable()->after('author_name');
            $table->foreign('author_admin_id')->references('id')->on('admins')->nullOnDelete();
            $table->boolean('enable_toc')->default(false)->after('content');
            $table->json('faqs')->nullable()->after('enable_toc');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['author_admin_id']);
            $table->dropColumn(['author_admin_id', 'enable_toc', 'faqs']);
        });
    }
};
