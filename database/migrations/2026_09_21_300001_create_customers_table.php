<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * External customers. Separate from `users`, who are staff — a customer never
 * reaches the admin panel, and a staff login is never a shipper.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->index();
            $table->string('password')->nullable();
            $table->string('preferred_locale', 5)->default('en');
            $table->string('preferred_currency', 3)->default('AUD');

            /** @var array<int, string> Channels this customer opted into: mail, sms, whatsapp, database. */
            $table->json('notification_channels')->nullable();

            $table->boolean('is_business')->default(false);
            $table->string('business_name')->nullable();
            $table->text('default_address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
