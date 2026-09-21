<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Screen-size bands for the TV landed-cost calculator.
 *
 * The shared inputs (per-kg rate, airport processing fee) are config_entries
 * rows in the `pricing` group, not columns here — they are not per-size.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_size_bands', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_route_id')->constrained()->cascadeOnDelete();
            $table->string('size_label');
            $table->unsignedInteger('min_inches');
            $table->unsignedInteger('max_inches')->nullable();
            $table->decimal('bd_govt_duty_bdt', 12, 2);
            $table->decimal('volume_fee_aud', 12, 2);
            $table->decimal('local_delivery_bdt', 12, 2);
            $table->decimal('default_pallet_weight_kg', 8, 3)->default(12);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_route_id', 'size_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_size_bands');
    }
};
