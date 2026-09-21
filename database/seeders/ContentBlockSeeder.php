<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Every word and list on the public site, taken from the approved prototype.
 *
 * The page renders whatever is in this table: headings, eyebrows, chips, stat
 * cells, benefit rows, journey steps, terms, FAQs and footer columns. A `|` in a
 * title is the line break the design puts there.
 *
 * Rates are deliberately absent — those come from the pricing tables so the rate
 * card and the calculator can never disagree.
 */
final class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'section_key' => 'nav',
                'icon' => null,
                'title' => null,
                'body' => null,
                'data' => [
                    'links' => [
                        [
                            'href' => '#about',
                            'label' => 'About us',
                        ],
                        [
                            'href' => '#solutions',
                            'label' => 'Services',
                        ],
                        [
                            'href' => '#calculator',
                            'label' => 'Calculator',
                        ],
                        [
                            'href' => '#rates',
                            'label' => 'Rates',
                        ],
                        [
                            'href' => '#top',
                            'label' => 'Track order',
                        ],
                        [
                            'href' => '#dropoff',
                            'label' => 'Drop-off',
                        ],
                        [
                            'href' => '#faq',
                            'label' => 'FAQ',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'topbar',
                'icon' => null,
                'title' => 'DAILY SHIPMENTS · DHAKA ⇄ SYDNEY',
                'body' => null,
                'data' => [],
            ],
            [
                'section_key' => 'hero',
                'icon' => null,
                'title' => 'Parcels from Bangladesh|to Australia in <em>6–9 days</em>',
                'body' => 'Free pickup from any address in Bangladesh, free box and packaging, live tracking, and home delivery anywhere in Australia. Book online — no phone call needed.',
                'data' => [
                    'chips' => [
                        'Free pickup',
                        'Free packaging',
                        'Live tracking',
                        'Home delivery',
                        'Pay either side',
                    ],
                    'slideLabels' => [
                        '01 / AIR FREIGHT',
                        '02 / ONLINE BOOKING',
                        '03 / DROP-OFF NETWORK',
                    ],
                    'kicker' => 'FAST · AFFORDABLE · HASSLE-FREE',
                ],
            ],
            [
                'section_key' => 'marquee',
                'icon' => null,
                'title' => null,
                'body' => null,
                'data' => [
                    'items' => [
                        'FREE PICKUP ALL OVER BANGLADESH',
                        '·',
                        'FREE BOX & PACKAGING',
                        '·',
                        'LIVE TRACKING',
                        '·',
                        '6–9 BUSINESS DAYS',
                        '·',
                        'HOME DELIVERY AUSTRALIA WIDE',
                        '·',
                        'PAY IN BD OR AUS',
                        '·',
                        'DAILY SHIPMENTS',
                        '·',
                    ],
                ],
            ],
            [
                'section_key' => 'stats',
                'icon' => null,
                'title' => null,
                'body' => null,
                'data' => [
                    'cells' => [
                        [
                            'tag' => 'DROP-OFF NETWORK',
                            'num' => 4500,
                            'post' => '+',
                            'comma' => true,
                            'l' => 'Australia Post outlets as authorised drop-off partners',
                        ],
                        [
                            'tag' => 'TRANSIT TIME',
                            'text' => '6–9',
                            'l' => 'Business days, Bangladesh to Australia',
                        ],
                        [
                            'tag' => 'FROM NSW',
                            'pre' => '$',
                            'num' => 17,
                            'post' => '/kg',
                            'l' => 'General goods, Australia to Bangladesh',
                        ],
                        [
                            'tag' => 'FREQUENCY',
                            'text' => 'Daily',
                            'l' => 'Shipments leaving Bangladesh, every working day',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'about',
                'icon' => null,
                'title' => 'A cargo service built for the|Bangladeshi community abroad',
                'body' => 'BD-EX Cargo is an international courier delivery aggregator and broker. After a decade of express shipping in the United Kingdom, we began serving Australia and North America in April 2012 — shipping goods to loved ones in Bangladesh with maximum convenience and guaranteed satisfaction.',
                'data' => [
                    'body2' => 'Our network runs through drop-off stations in Sydney and in New York and New Jersey, backed by daily consolidated shipments, customs clearance handled end to end, and free home delivery across Bangladesh.',
                    'eyebrow' => '01 — ABOUT BD-EX CARGO',
                    'tags' => [
                        [
                            'i' => 'plane',
                            't' => 'Serving Australia since 2012',
                        ],
                        [
                            'i' => 'warehouse',
                            't' => 'Drop-off stations in Sydney, NY & NJ',
                        ],
                        [
                            'i' => 'globe',
                            't' => 'A decade of UK express shipping',
                        ],
                    ],
                    'assurances' => [
                        [
                            'n' => '100%',
                            'l' => 'Your goods are insured on every consolidated shipment',
                        ],
                        [
                            'n' => 'Cash back',
                            'l' => 'Refund offered if a product is damaged or stolen in transit',
                        ],
                        [
                            'n' => '64',
                            'l' => 'Districts of Bangladesh covered by free home delivery',
                        ],
                        [
                            'n' => 'Free',
                            'l' => 'Send your parcel to our Sydney head office at no charge',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'solutions',
                'icon' => null,
                'title' => 'Tailored solutions|for your needs',
                'body' => 'Every customer ships differently. We work in three lanes — corporate accounts, personal parcels and online stores.',
                'data' => [
                    'eyebrow' => '02 — OUR SERVICE',
                    'cards' => [
                        [
                            'i' => 'building',
                            't' => 'Corporate needs',
                            'd' => 'Register a business account and start sending — consolidated freight, itemised invoicing and contract rates.',
                            'g' => 'radial-gradient(90% 90% at 20% 20%,rgba(0,105,75,.75),transparent 62%),linear-gradient(140deg,#072319,#04150f)',
                        ],
                        [
                            'i' => 'user',
                            't' => 'Personal needs',
                            'd' => 'You pick the service, the delivery time and the price. Free pickup in Bangladesh, home delivery in Australia.',
                            'g' => 'radial-gradient(90% 90% at 80% 20%,rgba(200,16,46,.6),transparent 62%),linear-gradient(140deg,#0a1c16,#04150f)',
                        ],
                        [
                            'i' => 'store',
                            't' => 'Online store needs',
                            'd' => 'Automate your sales with bulk label creation, tracking numbers per order and a seller dashboard.',
                            'g' => 'radial-gradient(90% 90% at 50% 10%,rgba(18,69,127,.75),transparent 62%),linear-gradient(140deg,#071a20,#04150f)',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'services',
                'icon' => null,
                'title' => 'What you get with|every BD-EX shipment',
                'body' => 'Everything below is included in the rate you see — free pickup from any address in Bangladesh, free box and packaging, live tracking, and home delivery anywhere in Australia.',
                'data' => [
                    'note' => 'A handling charge is added for non-garments items. The calculator below includes it automatically.',
                    'eyebrow' => '03 — BANGLADESH → AUSTRALIA',
                    'benefits' => [
                        [
                            'i' => 'home',
                            't' => 'Free pickup, anywhere in Bangladesh',
                            'd' => 'Our Dhaka team collects from any address in the country — no courier office visit.',
                        ],
                        [
                            'i' => 'box',
                            't' => 'Free box and packaging',
                            'd' => 'We bring the box and pack it properly, at no charge.',
                        ],
                        [
                            'i' => 'crosshair',
                            't' => 'Live tracking',
                            'd' => 'Follow your parcel through all six milestones from your phone.',
                        ],
                        [
                            'i' => 'plane',
                            't' => '6–9 working day delivery',
                            'd' => 'Daily flights out of Bangladesh keep transit times short.',
                        ],
                        [
                            'i' => 'truck',
                            't' => 'Home delivery Australia wide',
                            'd' => 'Delivered to the door in any state or territory.',
                        ],
                        [
                            'i' => 'money',
                            't' => 'Pay in Bangladesh or Australia',
                            'd' => 'Settle in BDT or AUD, whichever side is easier for you.',
                        ],
                        [
                            'i' => 'cal',
                            't' => 'Shipments every day',
                            'd' => 'Bookings taken 24/7 online and dispatched each working day.',
                        ],
                        [
                            'i' => 'headset',
                            't' => 'Real people on the phone',
                            'd' => 'Call 0411 123 393 any time you want a human answer.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'calculator',
                'icon' => null,
                'title' => 'Price calculator',
                'body' => 'No hidden fees. The number on screen is the number you pay.',
                'data' => [
                    'eyebrow' => '04 — INSTANT QUOTE',
                ],
            ],
            [
                'section_key' => 'rates',
                'icon' => null,
                'title' => 'Transparent pricing,|both directions',
                'body' => null,
                'data' => [
                    'eyebrow' => '05 — RATE CARD',
                ],
            ],
            [
                'section_key' => 'booking',
                'icon' => null,
                'title' => 'Book a pickup|in four steps',
                'body' => 'No WhatsApp thread, no waiting for a reply. Fill this in and our Dhaka or Sydney team confirms within 24 hours with your label and tracking number.',
                'data' => [
                    'promoTitle' => 'Free pickup, anywhere in Bangladesh',
                    'promoMeta' => '64 DISTRICTS · WITHIN 24 HOURS',
                    'eyebrow' => '06 — ONLINE BOOKING',
                    'perks' => [
                        'Free pickup from any address in Bangladesh, arranged within 24 hours',
                        'Free box and packaging — we bring it to your sender',
                        'Instant quote before you confirm, itemised to the dollar',
                        'Tracking number and printable labels emailed straight away',
                    ],
                ],
            ],
            [
                'section_key' => 'journey',
                'icon' => null,
                'title' => 'Six stamped milestones',
                'body' => 'Every step is stamped with date and time and fires a notification automatically — email, SMS, WhatsApp or app.',
                'data' => [
                    'eyebrow' => '07 — THE JOURNEY',
                    'steps' => [
                        [
                            'n' => '01',
                            't' => 'Booked',
                            'd' => 'Confirmation, reference number and itemised quote issued.',
                        ],
                        [
                            'n' => '02',
                            't' => 'Picked up',
                            'd' => 'Driver collects from the door in your chosen slot.',
                        ],
                        [
                            'n' => '03',
                            't' => 'In warehouse',
                            'd' => 'Weighed, scanned, labelled and consolidated for the flight.',
                        ],
                        [
                            'n' => '04',
                            't' => 'In transit',
                            'd' => 'On the aircraft, with the air waybill number on record.',
                        ],
                        [
                            'n' => '05',
                            't' => 'Customs',
                            'd' => 'Declarations filed and duty cleared on your behalf.',
                        ],
                        [
                            'n' => '06',
                            't' => 'Delivered',
                            'd' => 'Photo proof of delivery with time and place.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'dropoff',
                'icon' => null,
                'title' => 'Drop your box at any|Australia Post outlet',
                'body' => '4,500+ outlets are our authorised partners. Time convenient, no hassle, and no postage fee when your parcel is 15 kg or more.',
                'data' => [
                    'panelTitle' => 'Request your box labels',
                    'panelBody' => 'Send these details and we\'ll email a printable label for each box.',
                    'panelNote' => 'Extra \$15 applies per box under 15 kg. Max 20 kg and 1 metre per box.',
                    'eyebrow' => '08 — AUSTRALIA WIDE DROP-OFF',
                    'rules' => [
                        'No postage fee at the outlet if the parcel is 15 kg or more — extra $15 applies per box under 15 kg.',
                        'SMS 0411 123 393 with your name, Australian address, mobile, email, number of boxes and approximate weight.',
                        'We email a label for each box — print it, stick it on, and drop it at any Australia Post outlet.',
                        'Each box must be under 20 kg and no longer than 1 metre.',
                    ],
                    'labelFields' => [
                        [
                            'Name',
                            'as on your ID',
                        ],
                        [
                            'Australian address',
                            'street, suburb, state, postcode',
                        ],
                        [
                            'Mobile no.',
                            'for SMS status updates',
                        ],
                        [
                            'Email ID',
                            'where labels are sent',
                        ],
                        [
                            'No. of boxes',
                            'one label per box',
                        ],
                        [
                            'Approx. weight',
                            'per box, in kg',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'terms',
                'icon' => null,
                'title' => 'Australia to Bangladesh',
                'body' => null,
                'data' => [
                    'eyebrow' => '09 — SHIPPING TERMS',
                    'items' => [
                        '$35 airport fee per shipment.',
                        'No perfumes or flammable items allowed. The customer is responsible and will be charged for any undeclared items in the box.',
                        'Up to $50 money back for lost, stolen or damaged products.',
                        'Australia-wide drop-off facility at any Australia Post outlet.',
                        'We clear customs and deliver free home delivery to your destination point.',
                        '10 to 18 working days delivery time from flight date.',
                        'BD-Ex Cargo reserves the right to check boxes. Luxury items may be taxed in BD customs — please advise in the packing list; an invoice may be required.',
                        'Parcel status is notified to the sender by SMS.',
                        'A full refund for lost and damaged products is processed if the client purchases Product Cover Insurance.',
                        'Non-garments items from Bangladesh attract an additional handling charge.',
                    ],
                ],
            ],
            [
                'section_key' => 'why',
                'icon' => null,
                'title' => 'Two countries, one platform,|zero guesswork',
                'body' => null,
                'data' => [
                    'eyebrow' => '10 — WHY BD-EX',
                    'items' => [
                        [
                            'i' => 'shield',
                            't' => 'Dependability',
                            'd' => 'On-time delivery, carefully chosen carriers',
                        ],
                        [
                            'i' => 'sliders',
                            't' => 'Flexibility',
                            'd' => 'Door-to-door or specialised, built around you',
                        ],
                        [
                            'i' => 'map',
                            't' => 'Live tracking',
                            'd' => 'GPS on a map, not a guess',
                        ],
                        [
                            'i' => 'coins',
                            't' => 'Two currencies',
                            'd' => 'AUD & BDT, live conversion',
                        ],
                        [
                            'i' => 'tag',
                            't' => 'Transparent pricing',
                            'd' => 'Exact cost before you book',
                        ],
                        [
                            'i' => 'file',
                            't' => 'Automated paperwork',
                            'd' => 'Quotes, invoices, labels, customs',
                        ],
                        [
                            'i' => 'bell',
                            't' => 'Four alert channels',
                            'd' => 'Email, SMS, WhatsApp, app',
                        ],
                        [
                            'i' => 'clock',
                            't' => 'Open 24/7',
                            'd' => 'No calls, no office hours',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'testimonials',
                'icon' => null,
                'title' => null,
                'body' => null,
                'data' => [
                    'quotes' => [
                        [
                            'text' => 'Sent four boxes from Dhaka to Melbourne. The pickup team called the next morning and everything arrived in eight days, nothing missing.',
                            'name' => 'Rizwana H.',
                            'city' => 'Melbourne, VIC',
                        ],
                        [
                            'text' => 'The price calculator matched the invoice to the dollar. No surprise fees at either end, which is rare in this business.',
                            'name' => 'Shakib A.',
                            'city' => 'Sydney, NSW',
                        ],
                        [
                            'text' => 'Dropped two 18 kg boxes at my local post office on a Saturday and paid nothing extra. Tracking updated at every step.',
                            'name' => 'Nasrin K.',
                            'city' => 'Brisbane, QLD',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'faq',
                'icon' => null,
                'title' => 'Frequently asked',
                'body' => null,
                'data' => [
                    'eyebrow' => '11 — QUESTIONS',
                    'items' => [
                        [
                            'q' => 'How do I book a pickup in Bangladesh?',
                            'a' => 'Use the online booking form on this page, or SMS 0411 123 393 with the sender and receiver name, address and mobile number. Our Dhaka office pickup team calls your sender within 24 hours and arranges a free pickup from anywhere in Bangladesh.',
                        ],
                        [
                            'q' => 'What does it cost to send a parcel from Australia to Bangladesh?',
                            'a' => 'General goods are $17/kg from NSW and $18/kg from ACT, VIC, QLD, SA, WA and TAS, plus a $35 airport fee per shipment. Toiletries, makeup, supplements, auto parts and tech items have their own per-kg rates — see the full rate card above.',
                        ],
                        [
                            'q' => 'How long does delivery take?',
                            'a' => 'Bangladesh to Australia is 6–9 business days (estimated 4+ business days for express garment shipments). Australia to Bangladesh is 10–18 working days from the flight date, including customs clearance and home delivery.',
                        ],
                        [
                            'q' => 'What cannot go in the box?',
                            'a' => 'No perfume, body spray, flammable items, alcohol, cigarettes, gold, diamonds, currency, AA or AAA batteries or adult toys. Undeclared prohibited items are the customer’s responsibility and will be charged.',
                        ],
                        [
                            'q' => 'Is my parcel covered if something is lost or damaged?',
                            'a' => 'Up to $50 money back applies to lost, stolen or damaged products. A full refund is processed if you purchase Product Cover Insurance at booking.',
                        ],
                        [
                            'q' => 'Can I pay in Bangladesh instead of Australia?',
                            'a' => 'Yes. Payment can be made on either side — Bangladesh or Australia — in BDT or AUD, whichever suits you and your sender.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'cta',
                'icon' => null,
                'title' => 'Ready to send your parcel home?',
                'body' => 'Book online in minutes. Free pickup, free packaging, live tracking, home delivery.',
                'data' => [
                    'eyebrow' => 'READY WHEN YOU ARE',
                    'primaryLabel' => 'Book a pickup',
                ],
            ],
            [
                'section_key' => 'footer',
                'icon' => null,
                'title' => null,
                'body' => null,
                'data' => [
                    'blurb' => 'Air freight and courier between Australia and Bangladesh. Daily shipments, transparent pricing, door to door.',
                    'badges' => ['DAC', 'SYD', 'EST. 2012'],
                    'legal' => '© 2026 BD-EX CARGO · ALL RIGHTS RESERVED',
                    'site' => 'WWW.BDEXCARGO.COM.AU',
                    'columns' => [
                        [
                            'title' => 'SERVICES',
                            'items' => [
                                [
                                    '#services',
                                    'Bangladesh → Australia',
                                ],
                                [
                                    '#rates',
                                    'Australia → Bangladesh',
                                ],
                                [
                                    '#dropoff',
                                    'Australia Post drop-off',
                                ],
                                [
                                    '#rates',
                                    'Sending a TV',
                                ],
                            ],
                        ],
                        [
                            'title' => 'CUSTOMERS',
                            'items' => [
                                [
                                    '#booking',
                                    'Book a pickup',
                                ],
                                [
                                    '#calculator',
                                    'Price calculator',
                                ],
                                [
                                    '#top',
                                    'Track a parcel',
                                ],
                                [
                                    '#faq',
                                    'FAQ',
                                ],
                            ],
                        ],
                        [
                            'title' => 'COMPANY',
                            'items' => [
                                [
                                    '#about',
                                    'About us',
                                ],
                                [
                                    '#terms',
                                    'Shipping terms',
                                ],
                                [
                                    '#how',
                                    'How it works',
                                ],
                                [
                                    'tel:0411123393',
                                    '0411 123 393',
                                ],
                                [
                                    'tel:0406524252',
                                    '0406 524 252',
                                ],
                            ],
                        ],
                    ],
                ],
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
