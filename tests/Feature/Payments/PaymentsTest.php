<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Config\DynamicConfig;
use App\Services\Payments\Drivers\BkashPaymentDriver;
use App\Services\Payments\Drivers\ManualPaymentDriver;
use App\Services\Payments\Drivers\SslCommerzPaymentDriver;
use App\Services\Payments\Drivers\StripePaymentDriver;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Payments\PaymentProcessor;
use Illuminate\Support\Facades\Http;

it('defaults both sides to manual confirmation', function (): void {
    $gateways = app(PaymentGatewayFactory::class);

    expect($gateways->australia())->toBeInstanceOf(ManualPaymentDriver::class)
        ->and($gateways->bangladesh())->toBeInstanceOf(ManualPaymentDriver::class);
});

it('switches gateway from configuration, with no deploy', function (): void {
    DynamicConfig::set('payment.au.driver', 'stripe');
    DynamicConfig::set('payment.bd.driver', 'bkash');

    $gateways = app(PaymentGatewayFactory::class);

    expect($gateways->australia())->toBeInstanceOf(StripePaymentDriver::class)
        ->and($gateways->bangladesh())->toBeInstanceOf(BkashPaymentDriver::class)
        ->and($gateways->forCurrency('BDT'))->toBeInstanceOf(BkashPaymentDriver::class)
        ->and($gateways->forCurrency('AUD'))->toBeInstanceOf(StripePaymentDriver::class);
});

it('falls back to manual rather than throwing on a bad selection', function (): void {
    DynamicConfig::set('payment.au.driver', 'a-gateway-nobody-wrote');

    expect(app(PaymentGatewayFactory::class)->australia())->toBeInstanceOf(ManualPaymentDriver::class);
});

it('records a staff-confirmed payment and settles the order', function (): void {
    $order = Order::factory()->create(['total_cost_aud' => 220, 'payment_status' => PaymentStatus::Pending]);

    app(PaymentProcessor::class)->recordManualPayment($order, 220);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and((float) $order->fresh()->paid_amount)->toBe(220.0);
});

it('marks an order part paid when the money only partly arrives', function (): void {
    $order = Order::factory()->create(['total_cost_aud' => 220]);

    app(PaymentProcessor::class)->recordManualPayment($order, 100);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PartiallyPaid);

    app(PaymentProcessor::class)->recordManualPayment($order->fresh(), 120);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('hands back a redirect when the gateway wants one', function (): void {
    DynamicConfig::set('payment.au.driver', 'stripe');
    DynamicConfig::set('payment.stripe.secret_key', 'sk_test_x', isSecret: true);

    Http::fake([
        'api.stripe.com/*' => Http::response(['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.test/cs_test_123']),
    ]);

    $order = Order::factory()->create(['total_cost_aud' => 220]);
    $payment = app(PaymentProcessor::class)->charge($order);

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->redirect_url)->toBe('https://checkout.stripe.test/cs_test_123')
        ->and($payment->gateway_reference)->toBe('cs_test_123')
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Pending);
});

it('settles the order once the gateway confirms the money cleared', function (): void {
    DynamicConfig::set('payment.au.driver', 'stripe');
    DynamicConfig::set('payment.stripe.secret_key', 'sk_test_x', isSecret: true);

    Http::fake([
        'api.stripe.com/v1/checkout/sessions/cs_test_123' => Http::response(['payment_status' => 'paid']),
        'api.stripe.com/*' => Http::response(['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.test/x']),
    ]);

    $order = Order::factory()->create(['total_cost_aud' => 220]);
    $payment = app(PaymentProcessor::class)->charge($order);

    app(PaymentProcessor::class)->verify($payment);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('records a refusal instead of losing it', function (): void {
    DynamicConfig::set('payment.bd.driver', 'sslcommerz');

    $order = Order::factory()->create(['payment_currency' => 'BDT', 'total_cost_aud' => 220]);

    // No store credentials configured, so the driver refuses before any HTTP call.
    $payment = app(PaymentProcessor::class)->charge($order);

    expect($payment->status)->toBe(PaymentStatus::Failed)
        ->and($payment->failure_reason)->toContain('store credentials are not set')
        ->and($payment->driver_class)->toBe(SslCommerzPaymentDriver::class)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Pending);

    expect(Payment::query()->count())->toBe(1);
});
