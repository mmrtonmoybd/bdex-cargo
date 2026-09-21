<?php

declare(strict_types=1);

use App\Filament\Resources\ContentBlocks\Pages\EditContentBlock;
use App\Models\ContentBlock;
use App\Models\User;
use Database\Seeders\ContentBlockSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

/**
 * Everything the public page says must be reachable from the panel. These tests
 * open each section's editor and prove a change reaches the rendered page.
 */
beforeEach(function (): void {
    $this->seed([SiteSettingSeeder::class, ContentBlockSeeder::class, PricingSeeder::class]);

    $role = Role::query()->create(['name' => 'editor-'.Str::random(6), 'guard_name' => 'web']);

    foreach (['ViewAny:ContentBlock', 'View:ContentBlock', 'Update:ContentBlock'] as $permission) {
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    $this->actingAs(User::factory()->create()->assignRole($role));
});

it('opens an editor for every seeded section', function (): void {
    $sections = ContentBlock::query()->pluck('section_key');

    expect($sections)->not->toBeEmpty();

    foreach (ContentBlock::query()->get() as $block) {
        livewire(EditContentBlock::class, ['record' => $block->getRouteKey()])
            ->assertSuccessful();
    }
});

it('edits a customer quote and shows it on the page', function (): void {
    $block = ContentBlock::query()->where('section_key', 'testimonials')->sole();

    livewire(EditContentBlock::class, ['record' => $block->getRouteKey()])
        ->fillForm([
            'data.quotes' => [
                ['text' => 'They packed it better than I would have.', 'name' => 'Ayesha R.', 'city' => 'Perth, WA'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $quotes = $block->fresh()->data['quotes'];

    expect($quotes)->toHaveCount(1)
        ->and($quotes[0]['name'])->toBe('Ayesha R.');

    // And it reaches the page the visitor actually loads.
    $html = $this->get('/')->getContent();
    preg_match('/id="bdex-data">(.*?)<\/script>/s', $html, $matches);
    $payload = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

    expect($payload['quotes'][0]['text'])->toBe('They packed it better than I would have.');
});

it('edits an FAQ without disturbing the rest of the section', function (): void {
    $block = ContentBlock::query()->where('section_key', 'faq')->sole();
    $eyebrow = $block->data['eyebrow'];

    livewire(EditContentBlock::class, ['record' => $block->getRouteKey()])
        ->fillForm([
            'title' => 'Questions we get asked',
            'data.items' => [
                ['q' => 'Do you collect on a Sunday?', 'a' => 'In Dhaka, yes — pick the slot at booking.'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $block->fresh();

    expect($fresh->title)->toBe('Questions we get asked')
        ->and($fresh->data['items'])->toHaveCount(1)
        // The eyebrow has its own field and must survive an edit to the list.
        ->and($fresh->data['eyebrow'])->toBe($eyebrow);
});

it('edits the ticker and the navigation', function (): void {
    $marquee = ContentBlock::query()->where('section_key', 'marquee')->sole();

    livewire(EditContentBlock::class, ['record' => $marquee->getRouteKey()])
        // A simple repeater carries its rows wrapped; it stores them flat.
        ->fillForm(['data.items' => [['value' => 'DAILY FLIGHTS'], ['value' => '·'], ['value' => 'DOOR TO DOOR']]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($marquee->fresh()->data['items'])->toBe(['DAILY FLIGHTS', '·', 'DOOR TO DOOR']);

    $nav = ContentBlock::query()->where('section_key', 'nav')->sole();

    livewire(EditContentBlock::class, ['record' => $nav->getRouteKey()])
        ->fillForm(['data.links' => [['label' => 'Rates', 'href' => '#rates']]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($nav->fresh()->data['links'])->toBe([['label' => 'Rates', 'href' => '#rates']]);
});
