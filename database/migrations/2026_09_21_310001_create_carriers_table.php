<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The real carriers BD-EX ships with — Aramex, FedEx, DHL, UPS today, whoever
 * else tomorrow. Internal only: no customer-facing surface may read this table.
 *
 * Credentials are deliberately NOT columns here. They live as namespaced
 * config_entries rows (carriers.{code}.client_id, .client_secret, …) so rotating
 * a key is a Filament edit and a leaked key never sits in a table that gets
 * exported, joined or dumped alongside operational data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();

            /** FQCN of the adapter implementing CarrierTrackingAdapter. */
            $table->string('driver_class');

            $table->string('api_base_url')->nullable();
            $table->unsignedInteger('poll_interval_hours')->default(12);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
