<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Public-site copy, seeded from the reference prototype.
 *
 * The site renders whatever is in this table, so changing a headline is an admin
 * edit. Sections with no row simply do not render.
 */
final class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'section_key' => 'hero',
                'title' => 'Parcels from Bangladesh to Australia in 6–9 days',
                'body' => 'Free pickup from any address in Bangladesh, free box and packaging, live tracking, and home delivery anywhere in Australia. Book online — no phone call needed.',
                'icon' => 'heroicon-o-paper-airplane',
            ],
            [
                'section_key' => 'about',
                'title' => 'A cargo service built for the Bangladeshi community abroad',
                'body' => 'BD-EX Cargo is an international courier delivery aggregator and broker. After a decade of express shipping in the United Kingdom, we began serving Australia and North America in April 2012 — shipping goods to loved ones in Bangladesh with maximum convenience and guaranteed satisfaction.',
                'icon' => 'heroicon-o-building-office-2',
            ],
            [
                'section_key' => 'solutions',
                'title' => 'Tailored solutions for your needs',
                'body' => 'Every customer ships differently. We work in three lanes — corporate accounts, personal parcels and online stores.',
                'icon' => 'heroicon-o-squares-2x2',
            ],
            [
                'section_key' => 'services',
                'title' => 'What you get with every BD-EX shipment',
                'body' => 'Everything below is included in the rate you see — free pickup from any address in Bangladesh, free box and packaging, live tracking, and home delivery anywhere in Australia.',
                'icon' => 'heroicon-o-check-badge',
            ],
            [
                'section_key' => 'calculator',
                'title' => 'Price calculator',
                'body' => 'No hidden fees. The number on screen is the number you pay.',
                'icon' => 'heroicon-o-calculator',
            ],
            [
                'section_key' => 'rates',
                'title' => 'Transparent pricing, both directions',
                'body' => 'Rates are published per item category for Australia → Bangladesh and per total weight for Bangladesh → Australia.',
                'icon' => 'heroicon-o-table-cells',
            ],
            [
                'section_key' => 'booking',
                'title' => 'Book a pickup in four steps',
                'body' => 'No WhatsApp thread, no waiting for a reply. Fill this in and our Dhaka or Sydney team confirms within 24 hours with your label and tracking number.',
                'icon' => 'heroicon-o-clipboard-document-check',
            ],
            [
                'section_key' => 'journey',
                'title' => 'Six stamped milestones',
                'body' => 'Every step is stamped with date and time and fires a notification automatically — email, SMS, WhatsApp or app.',
                'icon' => 'heroicon-o-map',
            ],
            [
                'section_key' => 'dropoff',
                'title' => 'Drop your box at any Australia Post outlet',
                'body' => '4,500+ outlets are our authorised partners. Time convenient, no hassle, and no postage fee when your parcel is 15 kg or more.',
                'icon' => 'heroicon-o-inbox-arrow-down',
            ],
            [
                'section_key' => 'terms',
                'title' => 'Shipping terms',
                'body' => 'What we can carry, what we cannot, and how cover works if something goes wrong.',
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'section_key' => 'faq',
                'title' => 'Frequently asked questions',
                'body' => 'The questions our Dhaka and Sydney teams answer most often.',
                'icon' => 'heroicon-o-question-mark-circle',
            ],
        ];

        foreach ($blocks as $index => $block) {
            ContentBlock::query()->updateOrCreate(
                ['section_key' => $block['section_key'], 'locale' => 'en'],
                $block + ['sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
