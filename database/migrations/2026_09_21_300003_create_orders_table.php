<?php

declare(strict_types=1);

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The shipment as the customer understands it.
 *
 * `bdex_tracking_number` is issued here and is the only tracking identity a
 * customer ever sees. `carrier_consignment_id` is nullable and stays null until
 * staff consolidate the order onto a real carrier booking — an order is a
 * complete, trackable thing long before any carrier knows it exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('bdex_tracking_number')->unique();

            $table->foreignId('shipping_route_id')->constrained();
            $table->foreignId('pricing_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // Null until an order is consolidated onto a real carrier booking.
            $table->foreignId('carrier_consignment_id')->nullable()->index();

            $table->string('status')->default(NormalizedStatus::Booked->value)->index();
            $table->string('service_level')->default(ServiceLevel::Standard->value);
            $table->string('fulfilment_method')->default(FulfilmentMethod::CourierPickup->value);

            $table->string('sender_name');
            $table->string('sender_phone');
            $table->text('sender_address');
            $table->string('sender_city')->nullable();
            $table->string('sender_postcode')->nullable();

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->text('receiver_address');
            $table->string('receiver_city')->nullable();
            $table->string('receiver_postcode')->nullable();

            $table->date('pickup_preferred_date')->nullable();
            $table->string('pickup_window')->nullable();
            $table->timestamp('pickup_confirmed_at')->nullable();

            $table->decimal('declared_value', 12, 2)->default(0);
            $table->boolean('has_product_cover_insurance')->default(false);
            $table->decimal('insurance_fee', 12, 2)->default(0);

            $table->string('payment_status')->default(PaymentStatus::Pending->value)->index();
            $table->char('payment_currency', 3)->default('AUD');
            $table->decimal('paid_amount', 12, 2)->default(0);

            /** FQCN of the driver that took the payment, for audit — never a bare vendor name. */
            $table->string('payment_gateway_driver')->nullable();
            $table->string('payment_reference')->nullable();

            $table->decimal('total_cost_aud', 12, 2)->default(0);
            $table->decimal('total_cost_bdt', 14, 2)->nullable();

            /** @var array<string, mixed> The engine's itemisation, traceable to the rules that produced it. */
            $table->json('cost_breakdown')->nullable();

            $table->text('special_instructions')->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->boolean('is_cold_storage')->default(false);

            $table->foreignId('seller_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
