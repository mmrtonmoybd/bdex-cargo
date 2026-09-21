<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The header has its own two colours.
 *
 * It sits against both the dark hero and the light page body, so it cannot simply
 * inherit either. Giving it its own tokens keeps it admin-controllable alongside
 * the rest of the palette.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('color_nav_bg', 9)->default('#eaeeec')->after('color_ink');
            $table->string('color_nav_ink', 9)->default('#0b1f17')->after('color_nav_bg');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn(['color_nav_bg', 'color_nav_ink']);
        });
    }
};
