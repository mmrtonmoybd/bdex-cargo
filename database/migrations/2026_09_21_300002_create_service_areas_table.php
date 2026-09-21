<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where we actually collect and deliver.
 *
 * Checked at booking time so an address outside coverage is caught before a
 * customer pays, rather than after a driver is dispatched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table): void {
            $table->id();
            $table->char('country', 2)->index();
            $table->string('city');
            $table->string('region')->nullable();

            /** @var array<int, string> Postcodes or prefixes; empty means the whole city. */
            $table->json('postcodes')->nullable();

            $table->boolean('supports_pickup')->default(true);
            $table->boolean('supports_delivery')->default(true);
            $table->unsignedInteger('standard_days')->nullable();
            $table->unsignedInteger('express_days')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['country', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
