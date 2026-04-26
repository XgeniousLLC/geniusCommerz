<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->unique();
            $table->string('label');
            $table->text('credentials')->nullable();    // encrypted JSON
            $table->boolean('is_active')->default(false);
            $table->string('environment', 10)->default('sandbox'); // sandbox|live
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
