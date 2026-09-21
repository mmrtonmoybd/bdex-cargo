<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Bengali copy for the public site.
 *
 * Only the sections whose wording carries the message; anything without a Bengali
 * row falls back to English rather than rendering blank, so the site is usable
 * while translation is still in progress.
 *
 * Have a native speaker review this before launch. Shipping vocabulary carries
 * local conventions — "কাস্টমস ছাড়" reads naturally in Dhaka in a way a literal
 * translation of "customs clearance" does not.
 */
final class BengaliContentSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'section_key' => 'topbar',
                'title' => 'প্রতিদিন শিপমেন্ট · ঢাকা ⇄ সিডনি',
            ],
            [
                'section_key' => 'hero',
                'title' => 'বাংলাদেশ থেকে অস্ট্রেলিয়া|৬–৯ দিনে পার্সেল',
                'body' => 'বাংলাদেশের যেকোনো ঠিকানা থেকে ফ্রি পিকআপ, ফ্রি বক্স ও প্যাকেজিং, লাইভ ট্র্যাকিং এবং অস্ট্রেলিয়া জুড়ে হোম ডেলিভারি। অনলাইনে বুক করুন — ফোন করার দরকার নেই।',
                'data' => [
                    'kicker' => 'দ্রুত · সাশ্রয়ী · ঝামেলাহীন',
                    'chips' => ['ফ্রি পিকআপ', 'ফ্রি প্যাকেজিং', 'লাইভ ট্র্যাকিং', 'হোম ডেলিভারি', 'যেকোনো প্রান্তে পেমেন্ট'],
                ],
            ],
            [
                'section_key' => 'about',
                'title' => 'প্রবাসী বাংলাদেশিদের জন্য গড়া|কার্গো সার্ভিস',
                'body' => 'বিডি-এক্স কার্গো একটি আন্তর্জাতিক কুরিয়ার ডেলিভারি অ্যাগ্রিগেটর ও ব্রোকার। যুক্তরাজ্যে এক দশকের এক্সপ্রেস শিপিংয়ের অভিজ্ঞতা নিয়ে ২০১২ সালের এপ্রিল থেকে আমরা অস্ট্রেলিয়া ও উত্তর আমেরিকায় সেবা দিচ্ছি।',
                'data' => ['eyebrow' => '০১ — বিডি-এক্স কার্গো সম্পর্কে'],
            ],
            [
                'section_key' => 'calculator',
                'title' => 'মূল্য ক্যালকুলেটর',
                'body' => 'কোনো লুকানো খরচ নেই। স্ক্রিনে যে সংখ্যা দেখছেন, সেটিই আপনি দেবেন।',
                'data' => ['eyebrow' => '০৪ — তাৎক্ষণিক দর'],
            ],
            [
                'section_key' => 'rates',
                'title' => 'স্বচ্ছ মূল্য,|দুই দিকেই',
                'data' => ['eyebrow' => '০৫ — রেট কার্ড'],
            ],
            [
                'section_key' => 'booking',
                'title' => 'চার ধাপে|পিকআপ বুক করুন',
                'body' => 'হোয়াটসঅ্যাপে অপেক্ষা নয়। ফর্মটি পূরণ করুন — আমাদের ঢাকা বা সিডনি টিম ২৪ ঘণ্টার মধ্যে লেবেল ও ট্র্যাকিং নম্বর সহ নিশ্চিত করবে।',
                'data' => ['eyebrow' => '০৬ — অনলাইন বুকিং'],
            ],
            [
                'section_key' => 'journey',
                'title' => 'ছয়টি নির্ধারিত ধাপ',
                'body' => 'প্রতিটি ধাপে তারিখ ও সময় রেকর্ড হয় এবং স্বয়ংক্রিয়ভাবে বিজ্ঞপ্তি যায় — ইমেইল, এসএমএস, হোয়াটসঅ্যাপ বা অ্যাপে।',
                'data' => [
                    'eyebrow' => '০৭ — যাত্রাপথ',
                    'steps' => [
                        ['n' => '০১', 't' => 'বুকিং সম্পন্ন', 'd' => 'নিশ্চিতকরণ, রেফারেন্স নম্বর ও বিস্তারিত দর দেওয়া হয়েছে।'],
                        ['n' => '০২', 't' => 'সংগ্রহ করা হয়েছে', 'd' => 'আপনার নির্বাচিত সময়ে চালক দরজা থেকে সংগ্রহ করেছেন।'],
                        ['n' => '০৩', 't' => 'গুদামে', 'd' => 'ওজন, স্ক্যান, লেবেলিং ও ফ্লাইটের জন্য একত্রীকরণ সম্পন্ন।'],
                        ['n' => '০৪', 't' => 'পথে আছে', 'd' => 'উড়োজাহাজে, এয়ারওয়েবিল নম্বর রেকর্ডে আছে।'],
                        ['n' => '০৫', 't' => 'কাস্টমসে', 'd' => 'আপনার পক্ষে ঘোষণা জমা ও শুল্ক ছাড় করা হচ্ছে।'],
                        ['n' => '০৬', 't' => 'ডেলিভারি সম্পন্ন', 'd' => 'সময় ও স্থানসহ ছবি প্রমাণ রেকর্ড করা হয়েছে।'],
                    ],
                ],
            ],
            [
                'section_key' => 'dropoff',
                'title' => 'যেকোনো অস্ট্রেলিয়া পোস্ট|আউটলেটে বক্স জমা দিন',
                'body' => '৪,৫০০+ আউটলেট আমাদের অনুমোদিত পার্টনার। পার্সেল ১৫ কেজি বা তার বেশি হলে কোনো পোস্টেজ ফি নেই।',
                'data' => ['eyebrow' => '০৮ — সারা অস্ট্রেলিয়ায় ড্রপ-অফ'],
            ],
            [
                'section_key' => 'faq',
                'title' => 'সাধারণ জিজ্ঞাসা',
                'data' => ['eyebrow' => '১১ — প্রশ্ন'],
            ],
            [
                'section_key' => 'cta',
                'title' => 'পার্সেল পাঠাতে|প্রস্তুত?',
                'body' => 'কয়েক মিনিটে অনলাইনে বুক করুন। ফ্রি পিকআপ, ফ্রি প্যাকেজিং, লাইভ ট্র্যাকিং, হোম ডেলিভারি।',
                'data' => ['eyebrow' => 'আপনি প্রস্তুত হলেই'],
            ],
        ];

        foreach ($blocks as $index => $block) {
            ContentBlock::query()->updateOrCreate(
                ['section_key' => $block['section_key'], 'locale' => 'bn'],
                [
                    'title' => $block['title'] ?? null,
                    'body' => $block['body'] ?? null,
                    'data' => $block['data'] ?? null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
