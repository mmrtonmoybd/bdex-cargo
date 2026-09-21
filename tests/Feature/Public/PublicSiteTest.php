<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\SiteSetting;
use Database\Seeders\ConfigEntrySeeder;
use Database\Seeders\ContentBlockSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function (): void {
    $this->seed([
        SiteSettingSeeder::class,
        ConfigEntrySeeder::class,
        ContentBlockSeeder::class,
        PricingSeeder::class,
    ]);
});

/** The JSON payload the page's scripts render from. */
function payload(string $html): array
{
    expect($html)->toContain('id="bdex-data"');

    preg_match('/id="bdex-data">(.*?)<\/script>/s', $html, $matches);

    return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
}

it('renders the homepage', function (): void {
    $this->get('/')->assertOk()->assertSee('BD-EX Cargo');
});

it('keeps the design intact', function (): void {
    $html = $this->get('/')->getContent();

    // The structural landmarks of the approved prototype.
    foreach (['class="hero"', 'class="marquee"', 'id="calculator"', 'id="ratePanel"', 'id="bookBody"', 'class="callfab"', 'id="faqs"'] as $landmark) {
        expect($html)->toContain($landmark);
    }
});

it('takes its copy from the database', function (): void {
    ContentBlock::query()->where('section_key', 'hero')->update([
        'title' => 'A headline only an admin could have written',
    ]);

    $this->get('/')->assertSee('A headline only an admin could have written');
});

it('publishes the stored brand colours as CSS custom properties', function (): void {
    $this->get('/')->assertOk()->assertSee('--sig: #25e39a', escape: false);
});

it('reflects a colour change on the very next request', function (): void {
    $this->get('/')->assertSee('--sig: #25e39a', escape: false);

    SiteSetting::query()->first()->update(['color_signal' => '#ff00ff']);

    $this->get('/')
        ->assertSee('--sig: #ff00ff', escape: false)
        ->assertDontSee('--sig: #25e39a', escape: false);
});

it('emits font stacks unescaped but strips anything that could break out of the style block', function (): void {
    SiteSetting::query()->first()->update([
        'font_display' => "'Bricolage Grotesque', sans-serif",
        'font_body' => 'Injected</style><script>alert(1)</script>',
    ]);

    $this->get('/')
        ->assertSee("--disp: 'Bricolage Grotesque', sans-serif", escape: false)
        ->assertDontSee('<script>alert(1)</script>', escape: false);
});

it('ships the rate card and tier sheet to the page', function (): void {
    $data = payload($this->get('/')->getContent());

    expect($data['ausbdRows'])->not->toBeEmpty()
        ->and(collect($data['ausbdRows'])->pluck('name'))->toContain('General goods')
        ->and($data['bdTiers'])->toHaveCount(16)
        ->and($data['cats'])->not->toBeEmpty()
        ->and($data['tvCards'])->not->toBeEmpty();
});

it('carries no price literal of its own in the page script', function (): void {
    $data = payload($this->get('/')->getContent());

    // Rates reach the browser as data read from the pricing tables; the bundled
    // script has no rate card of its own to fall back on.
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)->not->toContain('nsw:17')
        ->and($script)->not->toContain('rate:44')
        ->and((float) $data['bdTiers'][0]['rate'])->toBe(44.0);
});

it('redirects the old standalone pages into the single-page design', function (): void {
    $this->get('/quote')->assertRedirect('/#calculator');
    $this->get('/quote/tv')->assertRedirect('/#rates');
    $this->get('/book')->assertRedirect('/#booking');
});
