<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const WITH_CONFIRMED = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    private const WITHOUT_CONFIRMED = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    public function up(): void
    {
        $this->setStatusEnum(self::WITH_CONFIRMED);
    }

    public function down(): void
    {
        $this->setStatusEnum(self::WITHOUT_CONFIRMED);
    }

    /**
     * MySQL keeps the raw MODIFY it has always used. Everything else (SQLite under test)
     * goes through the schema builder — the raw statement is MySQL-only syntax and made
     * the whole test suite unmigratable.
     */
    private function setStatusEnum(array $values): void
    {
        if (DB::getDriverName() === 'mysql') {
            $list = implode(',', array_map(fn ($v) => "'{$v}'", $values));
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM({$list}) NOT NULL DEFAULT 'pending'");

            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($values) {
            $table->enum('status', $values)->default('pending')->change();
        });
    }
};
