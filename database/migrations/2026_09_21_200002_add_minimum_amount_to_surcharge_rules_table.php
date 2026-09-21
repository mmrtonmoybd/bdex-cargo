<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Floor for a calculated surcharge.
 *
 * The non-garment handling charge is "$2 per kg, but never less than $20" — a
 * shape the calculation type alone cannot express. Without this the rule would
 * have to become an `if` in PHP, which is exactly what surcharge_rules exists to
 * avoid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surcharge_rules', function (Blueprint $table): void {
            $table->decimal('minimum_amount', 12, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('surcharge_rules', function (Blueprint $table): void {
            $table->dropColumn('minimum_amount');
        });
    }
};
