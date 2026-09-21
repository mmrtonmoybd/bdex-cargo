<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The packing list. Customs needs it, pricing uses it, and the prohibited-items
 * check reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_box_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('weight_kg', 8, 3)->default(0);
            $table->unsignedInteger('liquid_volume_ml')->nullable();
            $table->decimal('declared_value', 12, 2)->default(0);

            /** Set when the description matches the editable banned-items list. Flags, never blocks. */
            $table->boolean('flagged_prohibited')->default(false);
            $table->string('flagged_term')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
