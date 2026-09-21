<?php

declare(strict_types=1);

use App\Enums\WeightTierScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Price per kg banded by total shipment weight (the BD→AU model).
 *
 * `max_kg` null means "and above".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_route_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_kg', 8, 3);
            $table->decimal('max_kg', 8, 3)->nullable();
            $table->decimal('price_per_kg', 12, 2);
            $table->string('applies_to')->default(WeightTierScope::GarmentOnly->value);
            $table->string('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shipping_route_id', 'applies_to', 'is_active'], 'weight_tier_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_tiers');
    }
};
