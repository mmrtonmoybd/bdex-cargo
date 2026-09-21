<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Most sections of the public site render a list — stat cells, benefit rows, FAQ
 * entries, footer columns. Those lists live here as a JSON payload per section so
 * the page stays admin-editable without a table per section.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_blocks', function (Blueprint $table): void {
            $table->json('data')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table): void {
            $table->dropColumn('data');
        });
    }
};
