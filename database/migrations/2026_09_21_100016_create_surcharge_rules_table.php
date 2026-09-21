<?php

declare(strict_types=1);

use App\Enums\CalculationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The generalised fee engine.
 *
 * The airport fee, the >$100 electronics loading, the non-garment handling
 * charge and the AusPost under-15kg fee are all rows in this table evaluated at
 * quote time. None of them is an `if` statement in PHP, and a new conditional
 * fee next year is one row, not a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surcharge_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->text('description')->nullable();

            /** Null means the rule applies to every route. */
            $table->foreignId('shipping_route_id')->nullable()->constrained()->cascadeOnDelete();

            /** Null means the rule applies to every item category. */
            $table->foreignId('item_category_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('calculation_type')->default(CalculationType::Flat->value);
            $table->decimal('amount', 12, 2);

            // Condition triple. All three null = the rule always applies.
            $table->string('condition_field')->nullable();
            $table->string('condition_operator')->nullable();
            $table->string('condition_value')->nullable();

            /**
             * Set when the seeded amount is a stand-in for a figure the business has
             * not confirmed. Surfaces as a dashboard warning until cleared.
             */
            $table->boolean('is_placeholder_amount')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shipping_route_id', 'is_active', 'sort_order'], 'surcharge_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surcharge_rules');
    }
};
