<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Volume tiers and negotiated contract rates, in one shape.
 *
 * A row with no customer is a published volume tier anyone qualifies for by
 * shipping enough. A row with a customer is that customer's contract rate. Both
 * are a discount against the engine's own price, so an upstream rate change still
 * reaches every agreement rather than leaving contracts frozen at last year's
 * numbers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_agreements', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();

            /** Null means anyone who qualifies — a published volume tier. */
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();

            /** Null means every corridor. */
            $table->foreignId('shipping_route_id')->nullable()->constrained()->cascadeOnDelete();

            /** Thresholds. Null means no threshold on that axis. */
            $table->decimal('min_weight_kg', 8, 3)->nullable();
            $table->unsignedInteger('min_shipments_per_month')->nullable();

            $table->decimal('discount_percent', 6, 3)->default(0);

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shipping_route_id', 'is_active', 'priority'], 'agreement_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_agreements');
    }
};
