<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-row display configuration for the public site.
 *
 * Deliberately separate from `config_entries`: nothing here is ever secret, so
 * the public request path never reads a table holding encrypted values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();

            $table->string('business_name')->default('BD-EX Cargo');
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();

            // Brand tokens, seeded from the reference prototype's :root block and
            // rendered into CSS custom properties on every public page load.
            $table->string('color_void', 9)->default('#04150f');
            $table->string('color_signal', 9)->default('#25e39a');
            $table->string('color_red', 9)->default('#c8102e');
            $table->string('color_blue', 9)->default('#12457f');
            $table->string('color_bg', 9)->default('#f5f8f6');
            $table->string('color_ink', 9)->default('#08160f');

            $table->string('font_display')->default("'Bricolage Grotesque', 'Arial Black', ui-sans-serif, system-ui, sans-serif");
            $table->string('font_body')->default("'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif");
            $table->string('font_mono')->default("'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace");

            $table->string('contact_phone_au')->nullable();
            $table->string('contact_phone_bd')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('address_au')->nullable();
            $table->text('address_bd')->nullable();

            /** @var array<int, string> Keyword list flagged against packing-list descriptions. */
            $table->json('banned_items')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
