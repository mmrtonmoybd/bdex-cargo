<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origin zones within a route. Today AU→BD has two; the schema has no opinion
 * about how many there are tomorrow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_zones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_route_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_route_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_zones');
    }
};
