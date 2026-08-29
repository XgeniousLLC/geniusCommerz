<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'partially_refunded', 'refunded'];

    private const PREVIOUS_STATUSES = ['unpaid', 'paid', 'partially_refunded', 'refunded'];

    public function up(): void
    {
        // A hosted-redirect flow has a real state between "unpaid" and "paid": the customer
        // has been sent to the gateway and has not come back. Without it there is nowhere
        // to record an order that is mid-payment, and no way to distinguish that from a
        // cash-on-delivery order that simply has not been collected yet.
        $this->setPaymentStatusEnum(self::PAYMENT_STATUSES);

        // One row per payment ATTEMPT. An order can legitimately have several — a failed
        // card, a retry, then a success — and reconciliation needs all of them.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50)->index();
            $table->string('environment', 10)->default('sandbox');
            $table->string('status', 20)->default('pending')->index();

            // Charged amount in the currency the customer actually saw, stored as integer
            // minor units because that is what every gateway API expects.
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);

            // Base-currency mirror so payments reconcile against order totals and reports.
            $table->decimal('base_amount', 12, 4)->default(0);
            $table->char('base_currency', 3);
            $table->decimal('exchange_rate', 18, 8)->default(1);

            $table->string('gateway_transaction_id')->nullable()->index();
            // Guards against a double-submit creating two charges for one attempt.
            $table->string('idempotency_key', 100)->unique();

            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Replay protection. Gateways retry aggressively and will deliver the same event
        // more than once; the unique index is what makes settlement idempotent.
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('event_id');
            $table->string('event_type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id'], 'webhook_provider_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');

        DB::table('orders')->whereIn('payment_status', ['pending', 'failed'])->update(['payment_status' => 'unpaid']);
        $this->setPaymentStatusEnum(self::PREVIOUS_STATUSES);
    }

    /** MySQL keeps its native enum; SQLite (tests) goes through the schema builder. */
    private function setPaymentStatusEnum(array $values): void
    {
        if (DB::getDriverName() === 'mysql') {
            $list = implode(',', array_map(fn ($v) => "'{$v}'", $values));
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM({$list}) NOT NULL DEFAULT 'unpaid'");

            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($values) {
            $table->enum('payment_status', $values)->default('unpaid')->change();
        });
    }
};
