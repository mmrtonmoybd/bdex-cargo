<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every poll attempt, successful or not.
 *
 * When a customer says their status is stale, this is the record that says
 * whether the carrier was asked, what it said, and how long it took.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carrier_consignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->boolean('succeeded');
            $table->string('normalized_status')->nullable();
            $table->string('raw_status')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('status_changed')->default(false);
            $table->unsignedInteger('orders_affected')->default(0);
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index(['carrier_consignment_id', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_sync_logs');
    }
};
