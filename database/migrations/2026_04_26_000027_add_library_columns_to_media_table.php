<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('folder_id')->nullable()->after('id');
            $table->string('type', 20)->default('image')->after('folder_id');
            $table->string('alt', 500)->nullable()->after('original_filename');
            $table->string('title', 500)->nullable()->after('alt');
            $table->text('caption')->nullable()->after('title');
            $table->unsignedSmallInteger('width')->nullable()->after('caption');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->unsignedBigInteger('created_by')->nullable()->after('order_column');

            $table->foreign('folder_id')->references('id')->on('media_folders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['folder_id', 'type', 'alt', 'title', 'caption', 'width', 'height', 'created_by']);
        });
    }
};
