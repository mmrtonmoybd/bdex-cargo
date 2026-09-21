<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A service a seller offers under their own name and their own price.
 *
 * The price is still derived from the pricing engine — the listing only carries
 * the seller's markup and their wording, so a rate change upstream still reaches
 * every reseller.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('markup_percent', 6, 3)->nullable();
            $table->decimal('flat_markup_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_listings');
    }
};
