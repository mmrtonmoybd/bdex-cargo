<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-kg rate for one category in one zone.
 *
 * This pivot is what lets "NSW / other states" become N zones without a schema
 * change: adding a zone means adding rows here, not a column anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_category_zone_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate_per_kg', 12, 2);
            $table->timestamps();

            $table->unique(['item_category_id', 'pricing_zone_id'], 'category_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_category_zone_rates');
    }
};
