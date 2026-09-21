<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corridors the business serves. Admin-addable; nothing assumes exactly two.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->char('origin_country', 2);
            $table->char('destination_country', 2);
            $table->char('base_currency', 3)->default('AUD');
            $table->char('quote_currency', 3)->default('BDT');

            // Free text on purpose: published transit times disagree between source
            // materials, so staff own the wording per route rather than the code.
            $table->string('transit_time_text')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_routes');
    }
};
