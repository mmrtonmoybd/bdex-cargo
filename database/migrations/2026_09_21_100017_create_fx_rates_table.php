<?php

declare(strict_types=1);

use App\Enums\FxRateSourceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Currency conversion rates. Newest row per pair wins; older rows are history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_rates', function (Blueprint $table): void {
            $table->id();
            $table->char('from_currency', 3);
            $table->char('to_currency', 3);
            $table->decimal('rate', 18, 8);
            $table->string('source')->default(FxRateSourceType::Manual->value);
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['from_currency', 'to_currency', 'fetched_at'], 'fx_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_rates');
    }
};
