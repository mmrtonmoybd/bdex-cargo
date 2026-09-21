<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every attempt to take money, successful or not.
 *
 * Separate from orders.payment_status because "what the order currently says" and
 * "what actually happened" are different questions, and only the second one can
 * settle a dispute.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            /** FQCN of the driver that handled it — never a bare vendor name. */
            $table->string('driver_class');
            $table->string('driver_name');

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('status')->index();

            /** The gateway's own reference, for reconciliation. */
            $table->string('gateway_reference')->nullable()->index();
            $table->string('redirect_url', 1000)->nullable();

            /** @var array<string, mixed> The gateway's response, for debugging a dispute. */
            $table->json('raw_payload')->nullable();

            $table->text('failure_reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
