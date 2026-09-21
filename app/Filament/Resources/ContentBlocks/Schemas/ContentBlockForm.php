<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContentBlocks\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Edits one section of the public page.
 *
 * Most sections are a heading plus a list — testimonials, FAQs, the journey
 * steps, the footer columns. Those lists live in `data`, and each one gets its own
 * repeater below so they are editable as what they are rather than as raw JSON.
 * Anything without a purpose-built editor still gets the JSON escape hatch, so a
 * new section is never unreachable from the panel.
 */
class ContentBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section')
                    ->description('The public page renders whatever is here. A section with no active block simply does not appear.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('section_key')
                                ->label('Section key')
                                ->helperText('Matches the section on the page, e.g. testimonials.')
                                ->required()
                                ->maxLength(255),
                            Select::make('locale')
                                ->options(['en' => 'English', 'bn' => 'বাংলা'])
                                ->default('en')
                                ->required(),
                            TextInput::make('sort_order')->numeric()->default(0),
                        ]),
                    ]),

                Section::make('Copy')
                    ->schema([
                        TextInput::make('data.eyebrow')
                            ->label('Eyebrow')
                            ->helperText('The small label above the heading, e.g. "01 — ABOUT BD-EX CARGO".')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->helperText('Use | where the design breaks the line.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('body')->rows(4)->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('icon')
                                ->helperText('Heroicon name, where the section uses one.')
                                ->maxLength(255),
                            Toggle::make('is_active')->label('Visible')->default(true),
                        ]),
                    ]),

                ...self::extraFields(),

                ...self::listEditors(),

                ...self::pairEditors(),
            ]);
    }

    /**
     * The single values a particular section carries beyond title and body.
     *
     * Every string the page renders has a field here, so nothing on the public
     * site is reachable only by editing the database.
     *
     * @return array<int, Section>
     */
    private static function extraFields(): array
    {
        return [
            self::extrasFor('hero', 'Hero', [
                TextInput::make('data.kicker')
                    ->label('Kicker')
                    ->helperText('The line above the headline, e.g. "FAST · AFFORDABLE · HASSLE-FREE".')
                    ->columnSpanFull(),
            ]),

            self::extrasFor('about', 'About', [
                Textarea::make('data.body2')
                    ->label('Second paragraph')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

            self::extrasFor('services', 'Services', [
                Textarea::make('data.note')
                    ->label('Footnote')
                    ->helperText('The highlighted note under the benefits grid.')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),

            self::extrasFor('booking', 'Booking panel', [
                TextInput::make('data.promoTitle')->label('Promo title'),
                TextInput::make('data.promoMeta')->label('Promo sub-line'),
            ]),

            self::extrasFor('dropoff', 'Label request panel', [
                TextInput::make('data.panelTitle')->label('Panel title'),
                Textarea::make('data.panelBody')->label('Panel intro')->rows(2)->columnSpanFull(),
                Textarea::make('data.panelNote')->label('Panel note')->rows(2)->columnSpanFull(),
            ]),

            self::extrasFor('cta', 'Call to action', [
                TextInput::make('data.primaryLabel')->label('Button label'),
            ]),

            self::extrasFor('footer', 'Footer', [
                Textarea::make('data.blurb')->label('About line')->rows(3)->columnSpanFull(),
                TextInput::make('data.legal')->label('Copyright line')->columnSpanFull(),
                TextInput::make('data.site')->label('Website line'),
            ]),
        ];
    }

    /**
     * @param  array<int, mixed>  $fields
     */
    private static function extrasFor(string $sectionKey, string $heading, array $fields): Section
    {
        return Section::make($heading)
            ->schema($fields)
            ->columns(2)
            ->visible(fn (Get $get): bool => $get('section_key') === $sectionKey);
    }

    /**
     * One purpose-built editor per section that carries a list.
     *
     * @return array<int, Section>
     */
    private static function listEditors(): array
    {
        return [
            self::listFor('testimonials', 'Customer quotes', 'data.quotes', [
                Textarea::make('text')->label('Quote')->rows(3)->required()->columnSpanFull(),
                TextInput::make('name')->label('Customer')->required(),
                TextInput::make('city')->label('City')->required(),
            ], 'Shown in the rotating card beside "Why BD-EX".'),

            self::listFor('faq', 'Questions', 'data.items', [
                TextInput::make('q')->label('Question')->required()->columnSpanFull(),
                Textarea::make('a')->label('Answer')->rows(3)->required()->columnSpanFull(),
            ]),

            self::listFor('journey', 'Milestones', 'data.steps', [
                TextInput::make('n')->label('Number')->required(),
                TextInput::make('t')->label('Title')->required(),
                Textarea::make('d')->label('Description')->rows(2)->columnSpanFull(),
            ], 'The six stamped milestones. Keep these in step with the tracking statuses the system actually produces.'),

            self::listFor('terms', 'Terms', 'data.items', [
                Textarea::make('value')->label('Term')->rows(2)->required()->columnSpanFull(),
            ], null, simple: true),

            self::listFor('why', 'Reasons', 'data.items', [
                TextInput::make('i')->label('Icon')->helperText('shield, sliders, map, coins, tag, file, bell, clock'),
                TextInput::make('t')->label('Title')->required(),
                Textarea::make('d')->label('Detail')->rows(2)->columnSpanFull(),
            ]),

            self::listFor('services', 'Benefits', 'data.benefits', [
                TextInput::make('i')->label('Icon'),
                TextInput::make('t')->label('Title')->required(),
                Textarea::make('d')->label('Detail')->rows(2)->columnSpanFull(),
            ]),

            self::listFor('solutions', 'Solution cards', 'data.cards', [
                TextInput::make('i')->label('Icon'),
                TextInput::make('t')->label('Title')->required(),
                Textarea::make('d')->label('Detail')->rows(2)->columnSpanFull(),
                TextInput::make('g')->label('Background')->helperText('CSS gradient used behind the card art.')->columnSpanFull(),
            ]),

            self::listFor('stats', 'Stat cells', 'data.cells', [
                TextInput::make('tag')->label('Label')->required(),
                TextInput::make('num')->label('Number')->helperText('Counts up on scroll. Leave empty and use Text instead for things like "6–9".'),
                TextInput::make('text')->label('Text')->helperText('Used when the figure is not a number.'),
                TextInput::make('pre')->label('Prefix')->helperText('e.g. $'),
                TextInput::make('post')->label('Suffix')->helperText('e.g. + or /kg'),
                TextInput::make('l')->label('Caption')->required()->columnSpanFull(),
            ]),

            self::listFor('about', 'Assurances', 'data.assurances', [
                TextInput::make('n')->label('Figure')->required(),
                TextInput::make('l')->label('Caption')->required()->columnSpanFull(),
            ]),

            self::listFor('booking', 'Perks', 'data.perks', [
                Textarea::make('value')->label('Perk')->rows(2)->required()->columnSpanFull(),
            ], null, simple: true),

            self::listFor('dropoff', 'Drop-off rules', 'data.rules', [
                Textarea::make('value')->label('Rule')->rows(2)->required()->columnSpanFull(),
            ], null, simple: true),

            self::listFor('marquee', 'Ticker items', 'data.items', [
                TextInput::make('value')->label('Item')->required()->columnSpanFull(),
            ], 'The scrolling strip under the hero.', simple: true),

            self::listFor('hero', 'Hero chips', 'data.chips', [
                TextInput::make('value')->label('Chip')->required()->columnSpanFull(),
            ], null, simple: true),

            self::listFor('nav', 'Navigation links', 'data.links', [
                TextInput::make('label')->label('Label')->required(),
                TextInput::make('href')->label('Link')->required()->helperText('An anchor such as #rates, or a full URL.'),
            ]),

            self::listFor('hero', 'Hero slide labels', 'data.slideLabels', [
                TextInput::make('value')->label('Label')->required()->columnSpanFull(),
            ], 'One per background scene, shown beside the slide dots.', simple: true),

            self::listFor('footer', 'Footer badges', 'data.badges', [
                TextInput::make('value')->label('Badge')->required()->columnSpanFull(),
            ], null, simple: true),
        ];
    }

    /**
     * Lists whose rows are themselves pairs, kept separate so the repeaters above
     * stay readable.
     *
     * @return array<int, Section>
     */
    private static function pairEditors(): array
    {
        return [
            Section::make('Footer columns')
                ->visible(fn (Get $get): bool => $get('section_key') === 'footer')
                ->schema([
                    Repeater::make('data.columns')
                        ->label('Columns')
                        ->schema([
                            TextInput::make('title')->label('Heading')->required(),
                            Repeater::make('items')
                                ->label('Links')
                                ->schema([
                                    TextInput::make('0')->label('Link')->required(),
                                    TextInput::make('1')->label('Label')->required(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Section::make('Label request fields')
                ->visible(fn (Get $get): bool => $get('section_key') === 'dropoff')
                ->schema([
                    Repeater::make('data.labelFields')
                        ->label('Fields we ask for')
                        ->schema([
                            TextInput::make('0')->label('Field')->required(),
                            TextInput::make('1')->label('Hint')->required(),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @param  array<int, mixed>  $fields
     */
    private static function listFor(
        string $sectionKey,
        string $heading,
        string $statePath,
        array $fields,
        ?string $description = null,
        bool $simple = false,
    ): Section {
        $repeater = Repeater::make($statePath)
            ->label($heading)
            ->reorderable()
            ->collapsible()
            ->defaultItems(0)
            ->columns(2)
            ->columnSpanFull();

        // A list of plain strings reads better as one field per row than as a
        // single-key object.
        $repeater = $simple
            ? $repeater->simple($fields[0])
            : $repeater->schema($fields);

        return Section::make($heading)
            ->description($description)
            ->schema([$repeater])
            ->visible(fn (Get $get): bool => $get('section_key') === $sectionKey);
    }
}
