<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A pickup request submitted from the public booking wizard.
 *
 * Deliberately not the orders domain — that arrives with its own phase. This is
 * the enquiry as the customer typed it, with the quote they were shown, so the
 * reference number the site hands back is a real record staff can act on rather
 * than a number invented in the browser.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('shipping_route_id')->nullable()->constrained()->nullOnDelete();

            $table->string('service')->default('standard');
            $table->date('pickup_date')->nullable();
            $table->string('pickup_slot')->nullable();

            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->text('sender_address')->nullable();

            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('receiver_address')->nullable();

            $table->unsignedInteger('box_count')->default(1);
            $table->decimal('weight_kg', 8, 3)->default(0);
            $table->foreignId('item_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('packing_notes')->nullable();

            /** @var array<string, mixed> The engine's own breakdown at the moment of booking. */
            $table->json('quote_snapshot')->nullable();
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->char('quoted_currency', 3)->nullable();

            $table->boolean('terms_accepted')->default(false);
            $table->string('status')->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_enquiries');
    }
};
