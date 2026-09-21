<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ratings customers leave on a seller.
 *
 * Tied to an order so a review cannot be left by someone who never shipped, which
 * is the difference between a reputation and a comment box.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['seller_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_reviews');
    }
};
