<?php

declare(strict_types=1);

use App\Enums\NormalizedStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One real booking with a real carrier, shared by however many customer orders
 * were consolidated into it.
 *
 * This is the row that makes the whole abstraction work: five customers' orders
 * can point at one FedEx consignment, and when that consignment moves, all five
 * orders move together. The carrier's own tracking number lives here and nowhere
 * a customer can reach.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_consignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();

            /** The carrier's own number. Never rendered on a customer surface. */
            $table->string('carrier_tracking_number');

            $table->string('internal_status')->default(NormalizedStatus::Booked->value)->index();
            $table->string('last_raw_status')->nullable();
            $table->string('last_location')->nullable();

            /** @var array<string, mixed> The carrier's last response, kept for debugging. */
            $table->json('raw_payload')->nullable();

            $table->timestamp('booked_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['carrier_id', 'carrier_tracking_number']);
            $table->index(['internal_status', 'last_synced_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('carrier_consignment_id')
                ->references('id')
                ->on('carrier_consignments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['carrier_consignment_id']);
        });

        Schema::dropIfExists('carrier_consignments');
    }
};
