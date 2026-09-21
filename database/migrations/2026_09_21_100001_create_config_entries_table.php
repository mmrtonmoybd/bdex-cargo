<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operational and secret configuration, editable from Filament at runtime.
 *
 * Everything that would otherwise be an `env()` call outside the bootstrap set
 * (APP_KEY, APP_ENV, APP_URL, APP_DEBUG, DB_*, CACHE_STORE, QUEUE_CONNECTION,
 * SESSION_DRIVER) lives here instead. Secrets are encrypted with APP_KEY, so
 * protecting .env protects every credential in this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->string('group')->index();
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_entries');
    }
};
