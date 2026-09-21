@inject('settings', 'App\\Support\\SiteSettings')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? $settings->get('business_name', 'BD-EX Cargo') }}</title>
    <meta name="description" content="{{ $settings->get('tagline') }}">

    @if ($settings->get('favicon_path'))
        <link rel="icon" href="{{ Storage::url($settings->get('favicon_path')) }}">
    @endif

    {{-- Brand tokens from the database, before the stylesheet that consumes them. --}}
    <x-theme-vars />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <header class="sticky top-0 z-40 border-b border-[var(--line)] bg-[var(--card)]/90 backdrop-blur">
        <div class="shell flex items-center justify-between gap-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if ($settings->get('logo_path'))
                    <img src="{{ Storage::url($settings->get('logo_path')) }}" alt="" class="h-9 w-auto">
                @else
                    <img src="{{ asset('images/bdex-logo.png') }}" alt="" class="h-9 w-auto">
                @endif
                <span class="font-display text-lg font-bold">{{ $settings->get('business_name') }}</span>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                <a href="{{ route('quote') }}" class="hover:text-[var(--em)]">Price calculator</a>
                <a href="{{ route('quote.tv') }}" class="hover:text-[var(--em)]">Send a TV</a>
                <a href="{{ route('home') }}#rates" class="hover:text-[var(--em)]">Rates</a>
                <a href="{{ route('home') }}#faq" class="hover:text-[var(--em)]">FAQ</a>
            </nav>

            @if ($settings->get('contact_whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', (string) $settings->get('contact_whatsapp')) }}"
                   class="rounded-full bg-[var(--sig)] px-4 py-2 text-sm font-semibold text-[var(--void)]">
                    Talk to us
                </a>
            @endif
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-24 bg-[var(--void)] text-white">
        <div class="shell grid gap-10 py-14 md:grid-cols-3">
            <div>
                <p class="font-display text-xl font-bold">{{ $settings->get('business_name') }}</p>
                <p class="mt-2 max-w-sm text-sm text-white/60">{{ $settings->get('tagline') }}</p>
            </div>

            <div class="text-sm">
                <p class="font-mono text-xs tracking-widest text-[var(--sig)] uppercase">Australia</p>
                @if ($settings->get('contact_phone_au'))
                    <p class="mt-2">{{ $settings->get('contact_phone_au') }}</p>
                @endif
                @if ($settings->get('address_au'))
                    <p class="mt-1 whitespace-pre-line text-white/60">{{ $settings->get('address_au') }}</p>
                @endif
            </div>

            <div class="text-sm">
                <p class="font-mono text-xs tracking-widest text-[var(--sig)] uppercase">Bangladesh</p>
                @if ($settings->get('contact_phone_bd'))
                    <p class="mt-2">{{ $settings->get('contact_phone_bd') }}</p>
                @endif
                @if ($settings->get('address_bd'))
                    <p class="mt-1 whitespace-pre-line text-white/60">{{ $settings->get('address_bd') }}</p>
                @endif
                @if ($settings->get('contact_email'))
                    <p class="mt-2"><a href="mailto:{{ $settings->get('contact_email') }}" class="text-[var(--soft)]">{{ $settings->get('contact_email') }}</a></p>
                @endif
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="shell py-5 text-xs text-white/40">
                © {{ now()->year }} {{ $settings->get('business_name') }}
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
