<?php

declare(strict_types=1);

use App\Enums\NormalizedStatus;
use App\Models\ContentBlock;
use App\Models\Customer;
use App\Support\Localization;
use App\Support\SiteContent;
use Database\Seeders\BengaliContentSeeder;
use Database\Seeders\ContentBlockSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function (): void {
    $this->seed([SiteSettingSeeder::class, ContentBlockSeeder::class, BengaliContentSeeder::class, PricingSeeder::class]);
});

it('serves the homepage in Bengali when asked', function (): void {
    $this->get('/?lang=bn')
        ->assertOk()
        ->assertSee('বাংলাদেশ থেকে অস্ট্রেলিয়া', escape: false)
        ->assertDontSee('Parcels from Bangladesh');
});

it('remembers the language for the rest of the session', function (): void {
    $this->get('/?lang=bn')->assertOk();

    // No ?lang= this time; the choice should stick.
    $this->get('/')->assertSee('বাংলাদেশ থেকে অস্ট্রেলিয়া', escape: false);
});

it('falls back to English for a section nobody has translated yet', function (): void {
    app()->setLocale('bn');

    $content = app(SiteContent::class);

    // A half-translated site shows the untranslated half, not a blank space.
    expect($content->heading('hero'))->toContain('বাংলাদেশ')
        ->and($content->heading('terms'))->toBe('Australia to Bangladesh');
});

it('translates the status vocabulary', function (): void {
    app()->setLocale('en');
    expect(NormalizedStatus::InTransit->getLabel())->toBe('In transit');

    app()->setLocale('bn');
    expect(NormalizedStatus::InTransit->getLabel())->toBe('পথে আছে');
});

it('honours a customer\'s saved language without them asking again', function (): void {
    $customer = Customer::factory()->create(['preferred_locale' => 'bn']);

    $this->actingAs($customer, 'customer');

    expect(Localization::resolve())->toBe('bn');
});

it('ignores a language nobody supports', function (): void {
    expect(Localization::resolve('fr'))->toBe('en');

    $this->get('/?lang=fr')->assertOk()->assertSee('Parcels from Bangladesh');
});

it('lets an admin translate a section without touching code', function (): void {
    ContentBlock::query()->updateOrCreate(
        ['section_key' => 'terms', 'locale' => 'bn'],
        ['title' => 'অস্ট্রেলিয়া থেকে বাংলাদেশ', 'is_active' => true],
    );

    app()->setLocale('bn');

    expect(app(SiteContent::class)->heading('terms'))->toBe('অস্ট্রেলিয়া থেকে বাংলাদেশ');
});
