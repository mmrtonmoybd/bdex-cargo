<?php

declare(strict_types=1);

use App\Enums\PricingModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a customer can send, and how it is priced.
 *
 * Adding a category is a row plus its zone rates — never a code change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_route_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('pricing_model')->default(PricingModel::PerKgByZone->value);

            /** Used by flat_fee, flat_fee_per_item and per_kg_flat models. */
            $table->decimal('flat_fee_amount', 12, 2)->nullable();

            /** Ceiling the flat fee covers, e.g. "Laptop (up to 2.5kg)". Display + validation only. */
            $table->decimal('max_weight_kg', 8, 3)->nullable();

            $table->boolean('is_garment')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_route_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};
