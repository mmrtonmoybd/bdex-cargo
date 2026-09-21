<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BD-EX's own air waybill number.
 *
 * Deliberately ours, not the carrier's. Customs paperwork needs a waybill
 * reference and customers expect one, but printing the carrier's number on a
 * document the customer holds would give away the arrangement the whole system
 * exists to keep quiet. The carrier's own number stays on the consignment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('air_waybill_number')->nullable()->unique()->after('bdex_tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('air_waybill_number');
        });
    }
};
