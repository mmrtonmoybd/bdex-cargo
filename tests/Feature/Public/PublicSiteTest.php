<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\SiteSetting;
use Database\Seeders\ContentBlockSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function (): void {
    $this->seed([SiteSettingSeeder::class, ContentBlockSeeder::class, PricingSeeder::class]);
});

it('renders the homepage', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('BD-EX Cargo');
});

it('renders copy from the content blocks table', function (): void {
    ContentBlock::query()->where('section_key', 'hero')->update([
        'title' => 'A headline only an admin could have written',
    ]);

    $this->get('/')->assertSee('A headline only an admin could have written');
});

it('publishes the stored brand colours as CSS custom properties', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('--sig: #25e39a', escape: false);
});

it('reflects a colour change on the very next request', function (): void {
    // Warm the cache the way a real visitor would.
    $this->get('/')->assertSee('--sig: #25e39a', escape: false);

    SiteSetting::query()->first()->update(['color_signal' => '#ff00ff']);

    $this->get('/')
        ->assertSee('--sig: #ff00ff', escape: false)
        ->assertDontSee('--sig: #25e39a', escape: false);
});

it('publishes the rate card', function (): void {
    $this->get('/')
        ->assertSee('General goods')
        ->assertSee('$17.00/kg');
});

it('serves the standalone quote calculators', function (): void {
    $this->get('/quote')->assertOk();
    $this->get('/quote/tv')->assertOk();
});

it('emits font stacks unescaped but strips anything that could break out of the style block', function (): void {
    SiteSetting::query()->first()->update([
        'font_display' => "'Bricolage Grotesque', sans-serif",
        'font_body' => 'Injected</style><script>alert(1)</script>',
    ]);

    $response = $this->get('/');

    $response->assertSee("--disp: 'Bricolage Grotesque', sans-serif", escape: false)
        ->assertDontSee('<script>alert(1)</script>', escape: false)
        ->assertDontSee('</style><script', escape: false);
});
