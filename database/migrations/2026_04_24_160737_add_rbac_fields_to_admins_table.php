<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }
            if (! Schema::hasColumn('admins', 'invite_token')) {
                $table->string('invite_token', 64)->nullable()->after('role');
            }
            if (! Schema::hasColumn('admins', 'must_reset_password')) {
                $table->boolean('must_reset_password')->default(false)->after('invite_token');
            }
            if (! Schema::hasColumn('admins', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('must_reset_password');
            }
        });

        // Backfill UUIDs for any rows missing them
        DB::table('admins')->whereNull('uuid')->orWhere('uuid', '')->get(['id'])
            ->each(fn ($row) => DB::table('admins')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]));

        // Add unique indexes only if they don't already exist (works for MySQL + SQLite)
        $existingIndexes = collect(Schema::getIndexes('admins'))->pluck('name');
        Schema::table('admins', function (Blueprint $table) use ($existingIndexes) {
            if (! $existingIndexes->contains('admins_uuid_unique')) {
                $table->unique('uuid');
            }
            if (! $existingIndexes->contains('admins_invite_token_unique')) {
                $table->unique('invite_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'invite_token', 'must_reset_password', 'last_login_at']);
        });
    }
};
