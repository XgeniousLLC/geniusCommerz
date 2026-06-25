<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_check_cache', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('provider', 20);
            $table->string('risk_level', 20);   // safe | low_risk | mid_risk | high_risk | unknown
            $table->unsignedTinyInteger('risk_score');  // 0–100
            $table->unsignedSmallInteger('fraud_report_count')->default(0);
            $table->json('summary')->nullable();
            $table->json('couriers')->nullable();
            $table->json('reports')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_check_cache');
    }
};
