<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tax belongs to the corridor, not to the system.
 *
 * A shipment leaving Australia carries 10% GST; one leaving Bangladesh carries
 * 15% VAT. A single global rate cannot express that, and hardcoding either would
 * make a rate change a deploy — so each route carries its own rate and its own
 * name for it, which is what appears on the invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_routes', function (Blueprint $table): void {
            $table->decimal('tax_rate_percent', 6, 3)->default(0)->after('quote_currency');
            $table->string('tax_label')->nullable()->after('tax_rate_percent');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_routes', function (Blueprint $table): void {
            $table->dropColumn(['tax_rate_percent', 'tax_label']);
        });
    }
};
