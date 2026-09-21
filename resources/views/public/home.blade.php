{{--
    BD-EX Cargo public site.

    Markup ported from the approved prototype in docs/reference/prototype and left
    structurally identical. Only the strings that live in the database are swapped
    in; every class, wrapper and inline style is the design's own.

    The sections that render as lists are filled by resources/js/app.js from the
    payload below, exactly as the prototype did — the difference is that the
    payload comes from the database instead of being hardcoded in the script.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $settings->get('business_name') }} — Australia ⇄ Bangladesh Air Freight &amp; Courier</title>
<meta name="description" content="{{ $settings->get('tagline') }}">
<link rel="icon" href="{{ $favicon }}">

{{-- Brand tokens from site_settings, ahead of the stylesheet that consumes them. --}}
<x-theme-vars />

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="prog"><i></i></div>

<!-- ============ TOP BAR ============ -->
<div class="topbar">
  <span style="display:flex;align-items:center;gap:8px"><span class="dot"></span>{{ $c->title('topbar') }}</span>
  <span style="opacity:.28">/</span>
  <a href="tel:{{ $telAu }}">{{ $phoneAu }}</a>
  <span style="opacity:.28">/</span>
  <span style="display:flex;gap:14px"><a href="#booking">LOGIN</a><a href="#booking">SIGN UP</a></span>
</div>

<!-- ============ NAV ============ -->
<header class="nav" id="nav">
  <div class="shell bar">
    <a href="#top" style="flex:0 0 auto"><img class="logo" src="{{ $logo }}" alt="BD-EX Cargo"></a>
    <div style="flex:1"></div>
    <nav class="nav-links" id="navLinks"></nav>
    <a href="#booking" class="btn btn-red nav-cta nav-links">Book a pickup</a>
    <button class="burger" id="burger" aria-label="Menu"><i></i><i></i><i></i></button>
  </div>
  <div class="drawer"><div class="shell" style="padding-bottom:22px"><div id="drawerLinks"></div>
    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
      <a href="#booking" class="btn btn-red" style="flex:1 1 150px" data-close>Book a pickup</a>
      <a href="tel:0411123393" class="btn btn-ghost" style="flex:1 1 130px">Call us</a>
    </div>
  </div></div>
</header>

<!-- ============ HERO ============ -->
<section class="hero" id="top">
  <div class="scene on" data-scene="0" style="background:radial-gradient(90% 70% at 18% 30%,rgba(0,105,75,.55),transparent 62%),radial-gradient(70% 60% at 82% 70%,rgba(18,69,127,.42),transparent 60%),linear-gradient(140deg,#04150f,#072319)"></div>
  <div class="scene" data-scene="1" style="background:radial-gradient(80% 65% at 75% 25%,rgba(200,16,46,.36),transparent 62%),radial-gradient(70% 70% at 15% 75%,rgba(0,105,75,.45),transparent 62%),linear-gradient(200deg,#04150f,#0a2419)"></div>
  <div class="scene" data-scene="2" style="background:radial-gradient(85% 70% at 50% 15%,rgba(18,69,127,.5),transparent 62%),radial-gradient(60% 60% at 85% 80%,rgba(37,227,154,.26),transparent 62%),linear-gradient(120deg,#04150f,#061f18)"></div>
  <div class="grid"></div>
  <div id="spot"></div>

  <div class="route" id="route">
    <svg viewBox="0 0 1200 420" width="1200" height="420" fill="none">
      <path d="M-40 340 C 260 120, 760 80, 1180 250" stroke="rgba(37,227,154,.3)" stroke-width="1.6" stroke-dasharray="10 14" style="animation:bdxDash 3.4s linear infinite"></path>
      <path d="M-40 392 C 300 220, 820 190, 1180 330" stroke="rgba(200,16,46,.2)" stroke-width="1.4" stroke-dasharray="4 18"></path>
      <circle cx="112" cy="278" r="5" fill="#25e39a"></circle>
      <circle cx="112" cy="278" r="17" fill="none" stroke="rgba(37,227,154,.3)" stroke-width="1.2" style="animation:bdxGlow 3s ease-in-out infinite"></circle>
      <circle cx="1049" cy="223" r="5" fill="#c8102e"></circle>
      <circle cx="1049" cy="223" r="17" fill="none" stroke="rgba(200,16,46,.32)" stroke-width="1.2" style="animation:bdxGlow 3s ease-in-out .9s infinite"></circle>
      <text x="132" y="284" fill="rgba(255,255,255,.5)" font-family="IBM Plex Mono, monospace" font-size="12" letter-spacing="2">DAC</text>
      <text x="1069" y="229" fill="rgba(255,255,255,.5)" font-family="IBM Plex Mono, monospace" font-size="12" letter-spacing="2">SYD</text>
    </svg>
    <div class="plane">
      <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="filter:drop-shadow(0 0 10px rgba(37,227,154,.85))"><path d="M21 4L3 11l7 3 3 7 8-17z"></path></svg>
    </div>
  </div>

  <div class="shell hero-grid">
    <div style="min-width:0">
      <div class="chipline"><span class="flagchip"></span>{{ $c->data('hero', 'kicker') }}</div>
      <h1>{!! $c->heading('hero') !!}</h1>
      <p class="lede" style="color:#a8c9ba;max-width:560px;margin-top:22px;animation:bdxUp .8s .18s ease both">{{ $c->body('hero') }}</p>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:22px;animation:bdxUp .8s .24s ease both" id="heroChips"></div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:32px;animation:bdxUp .8s .3s ease both">
        <a href="#booking" class="btn btn-red" style="animation:bdxPulse 3.2s infinite">Book online — no phone call
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"></path></svg></a>
        <a href="#calculator" class="btn btn-ghost">Get an instant quote</a>
      </div>
      <div style="display:flex;align-items:center;gap:16px;margin-top:40px;animation:bdxUp .8s .36s ease both">
        <div class="dots" id="slideDots"></div>
        <span class="mono" id="slideLabel" style="font-size:11.5px;letter-spacing:2px;color:rgba(255,255,255,.45)">01 / AIR FREIGHT</span>
      </div>
    </div>

    <div class="glass" style="min-width:0">
      <div style="position:relative;display:flex;align-items:center;justify-content:space-between;gap:12px">
        <div style="display:flex;align-items:center;gap:11px">
          <span class="ico-box"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 18a7 7 0 100-14 7 7 0 000 14zM16.2 16.2L21 21"></path></svg></span>
          <strong style="font-size:16.5px;color:#fff">Track your parcel</strong>
        </div>
        <span class="mono" style="font-size:10.5px;letter-spacing:2px;color:rgba(255,255,255,.4)">AWB</span>
      </div>
      <p style="margin:12px 0 16px;font-size:13.5px;color:#9fbfae;line-height:1.55">Enter your BD-EX tracking number — recipients need nothing else.</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <input id="trackIn" class="dark-in" style="flex:1 1 180px" placeholder="e.g. BDX-8421903">
        <button class="btn btn-sig" id="trackBtn">Track</button>
      </div>
      <div id="trackOut"></div>
    </div>
  </div>

  <div class="marquee"><div class="track" id="marquee"></div></div>
</section>

<!-- ============ INSTRUMENTS ============ -->
<section class="instr">
  <div class="shell grid4" id="stats"></div>
</section>

<div class="bay">
<!-- ============ ABOUT ============ -->
<section class="sec" id="about">
  <div class="shell split">
    <div style="min-width:0" data-reveal>
      <div class="eyebrow" style="color:var(--blue)">{{ $c->data('about', 'eyebrow') }}</div>
      <h2 class="h2">{!! $c->heading('about') !!}</h2>
      <p class="lede" style="margin-top:22px">{{ $c->body('about') }}</p>
      <p class="lede" style="margin-top:14px">{{ $c->data('about', 'body2') }}</p>
      <div class="tagrow" id="aboutTags"></div>
    </div>
    <div class="assure" id="assurances" data-reveal></div>
  </div>
</section>

<!-- ============ SOLUTIONS ============ -->
<section class="sec" id="solutions" style="padding-top:0">
  <div class="shell">
    <div style="display:flex;flex-wrap:wrap;gap:20px;justify-content:space-between;align-items:end" data-reveal>
      <div style="min-width:0">
        <div class="eyebrow">{{ $c->data('solutions', 'eyebrow') }}</div>
        <h2 class="h2">{!! $c->heading('solutions') !!}</h2>
      </div>
      <p class="lede" style="max-width:44ch">{{ $c->body('solutions') }}</p>
    </div>
    <div class="cards3" id="solutions-grid"></div>
  </div>
</section>

<!-- ============ BENEFITS ============ -->
<section class="sec" id="services" style="padding-top:0">
  <div class="shell">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:40px;align-items:end" data-reveal>
      <div style="min-width:0">
        <div class="eyebrow">{{ $c->data('services', 'eyebrow') }}</div>
        <h2 class="h2">{!! $c->heading('services') !!}</h2>
      </div>
      <p class="lede" style="font-size:17.5px">{{ $c->body('services') }}</p>
    </div>
    <div class="benefits" id="benefits"></div>
    <div class="note"><strong>Note:</strong> {{ $c->data('services', 'note') }}</div>
  </div>
</section>
</div>

<!-- ============ CALCULATOR ============ -->
<section class="console dark-bay sec" id="calculator">
  <div class="grid"></div><div class="halo"></div>
  <div class="shell inner">
    <div style="text-align:center;max-width:660px;margin:0 auto">
      <div class="eyebrow on-dark" style="justify-content:center">{{ $c->data('calculator', 'eyebrow') }}</div>
      <h2 class="h2" style="color:#fff">{!! $c->heading('calculator') !!}</h2>
      <p class="lede" style="color:#a8c9ba;margin-top:16px">{{ $c->body('calculator') }}</p>
    </div>

    <div class="calc">
      <div class="controls">
        <div class="seg" id="dirTabs"></div>
        <div id="ausbdFields">
          <label class="lbl">Sending from</label>
          <div class="pillrow" id="stateTabs" style="margin-bottom:22px"></div>
          <label class="lbl">Item category</label>
          <select class="fld" id="catSel" style="margin-bottom:22px"></select>
        </div>
        <div id="weightWrap">
          <label class="lbl" style="display:flex;justify-content:space-between"><span>Total weight</span><span id="wLabel" style="color:var(--red)">10 kg</span></label>
          <input type="range" min="1" max="80" step="0.5" value="10" id="wRange">
          <input class="fld" id="wNum" value="10" style="width:130px;margin-top:10px;font-family:var(--mono)">
        </div>
        <div id="bdausFields" style="margin-top:24px;display:none">
          <label class="lbl">Item type</label>
          <div class="pillrow" id="itemTabs"></div>
          <p style="margin:12px 0 0;font-size:13px;color:var(--muted)">Per-kg rates shown are for garment items; non-garments add a handling charge.</p>
        </div>
      </div>
      <div class="readout">
        <span class="mono" style="font-size:10.5px;letter-spacing:2.6px;color:var(--sig)">YOUR ESTIMATE</span>
        <div style="display:flex;align-items:baseline;gap:10px;margin:14px 0 4px">
          <span class="priceN" id="priceBig">$0.00</span>
          <span style="font-size:15px;font-weight:700;color:#8ba99a;font-family:var(--mono)">AUD</span>
        </div>
        <div style="font-size:13.5px;color:#8ba99a" id="etaTxt"></div>
        <div class="hr-d"></div>
        <div id="qLines"></div>
        <div class="hr-d"></div>
        <div style="display:flex;justify-content:space-between;gap:14px;font-size:17px;font-weight:800;color:#fff">
          <span>Total</span><span id="priceSmall" class="mono">$0.00</span>
        </div>
        <p style="margin:18px 0 0;font-size:12px;color:#7d968a;line-height:1.6">Estimate only. Government duty, volume and airport fees on TVs and oversized items are quoted separately — see the rate tables below.</p>
        <a href="#booking" class="btn btn-red" style="margin-top:auto;margin-bottom:0;display:block;padding-block:16px">Book this shipment</a>
      </div>
    </div>
  </div>
</section>

<div class="bay">
<!-- ============ RATES ============ -->
<section class="sec" id="rates">
  <div class="shell">
    <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:end;justify-content:space-between" data-reveal>
      <div style="min-width:0">
        <div class="eyebrow">{{ $c->data('rates', 'eyebrow') }}</div>
        <h2 class="h2">{!! $c->heading('rates') !!}</h2>
      </div>
      <div class="pillrow" id="rateTabs"></div>
    </div>
    <div id="ratePanel"></div>
  </div>
</section>

<!-- ============ BOOKING ============ -->
<section class="sec" id="booking" style="padding-top:0">
  <div class="shell" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:clamp(30px,4vw,54px)">
    <div style="min-width:0" data-reveal>
      <div class="eyebrow">{{ $c->data('booking', 'eyebrow') }}</div>
      <h2 class="h2">{!! $c->heading('booking') !!}</h2>
      <p class="lede" style="margin:22px 0 26px;max-width:520px">{{ $c->body('booking') }}</p>
      <div style="display:flex;flex-direction:column;gap:14px" id="perks"></div>
      <div class="promo">
        <div style="position:absolute;inset:0;background:radial-gradient(80% 90% at 20% 20%,rgba(0,105,75,.6),transparent 60%),radial-gradient(70% 80% at 90% 80%,rgba(18,69,127,.5),transparent 60%)"></div>
        <svg viewBox="0 0 400 200" style="position:absolute;inset:0;width:100%;height:100%;opacity:.55" fill="none">
          <path d="M-10 150 C 110 60, 280 50, 410 120" stroke="rgba(37,227,154,.45)" stroke-width="1.4" stroke-dasharray="7 10" style="animation:bdxDash 4s linear infinite"></path>
          <circle cx="42" cy="122" r="4" fill="#25e39a"></circle><circle cx="358" cy="104" r="4" fill="#c8102e"></circle>
        </svg>
        <div style="position:relative;display:flex;align-items:center;gap:12px">
          <span class="ico-box"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5V6h11v10.5M14 9.5h4l3 3.5v3.5h-7M6.5 20a2 2 0 100-4 2 2 0 000 4zM17.5 20a2 2 0 100-4 2 2 0 000 4z"></path></svg></span>
          <div><div style="color:#fff;font-weight:700;font-size:16px">{{ $c->data('booking', 'promoTitle') }}</div><div class="mono" style="color:rgba(255,255,255,.5);font-size:11px;letter-spacing:1.8px;margin-top:4px">{{ $c->data('booking', 'promoMeta') }}</div></div>
        </div>
      </div>
    </div>

    <div class="book-card" style="min-width:0" data-reveal>
      <div class="hd">
        <div class="bars" id="stepBars"><i></i><i></i><i></i><i></i></div>
        <div style="display:flex;justify-content:space-between;margin-top:14px;font-family:var(--mono);font-size:11.5px;letter-spacing:1.8px;color:rgba(255,255,255,.55)">
          <span id="stepNum">STEP 1 / 4</span><span id="stepName" style="color:var(--sig)">SERVICE</span>
        </div>
      </div>
      <div class="bd" id="bookBody"></div>
    </div>
  </div>
</section>

<!-- ============ JOURNEY ============ -->
<section class="sec" id="how" style="padding-top:0">
  <div class="shell">
    <div style="text-align:center;max-width:640px;margin:0 auto" data-reveal>
      <div class="eyebrow" style="justify-content:center">{{ $c->data('journey', 'eyebrow') }}</div>
      <h2 class="h2">{!! $c->heading('journey') !!}</h2>
      <p class="lede" style="margin-top:16px">{{ $c->body('journey') }}</p>
    </div>
    <div class="journey" id="journey"></div>
  </div>
</section>
</div>

<!-- ============ DROP OFF ============ -->
<section class="dropoff dark-bay sec" id="dropoff">
  <div class="orb"></div>
  <div class="shell" style="position:relative;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:clamp(30px,4vw,52px)">
    <div style="min-width:0">
      <div class="eyebrow" style="color:var(--blue-hi)">{{ $c->data('dropoff', 'eyebrow') }}</div>
      <h2 class="h2" style="color:#fff">{!! $c->heading('dropoff') !!}</h2>
      <p class="lede" style="color:#c2d6e8;margin:20px 0 22px">{{ $c->body('dropoff') }}</p>
      <div style="display:grid;gap:12px" id="dropRules"></div>
      <div style="margin-top:22px;font-size:14.5px;color:#c2d6e8">Questions? Call <a href="tel:0411123393" style="color:#fff;font-weight:700">0411 123 393</a> or <a href="tel:{{ $telBd }}" style="color:#fff;font-weight:700">{{ $phoneBd }}</a>.</div>
    </div>
    <div style="min-width:0;background:#fff;color:var(--ink);border-radius:24px;padding:clamp(22px,2.6vw,32px);box-shadow:0 30px 70px rgba(0,0,0,.34)">
      <h3 style="font-size:21px;letter-spacing:-.5px">{{ $c->data('dropoff', 'panelTitle') }}</h3>
      <p style="margin:8px 0 20px;font-size:14px;color:var(--muted);line-height:1.6">{{ $c->data('dropoff', 'panelBody') }}</p>
      <div style="display:grid;gap:11px" id="labelFields"></div>
      <div class="note" style="margin-top:18px;border-radius:13px;padding:13px 15px;font-size:13px">{{ $c->data('dropoff', 'panelNote') }}</div>
      <a href="#booking" class="btn btn-red" style="display:block;margin-top:18px">Request labels online</a>
    </div>
  </div>
</section>

<div class="bay">
<!-- ============ TERMS ============ -->
<section class="sec" id="terms">
  <div class="shell">
    <div style="display:flex;flex-wrap:wrap;gap:18px;align-items:end;justify-content:space-between;margin-bottom:36px" data-reveal>
      <div style="min-width:0">
        <div class="eyebrow">{{ $c->data('terms', 'eyebrow') }}</div>
        <h2 class="h2">{!! $c->heading('terms') !!}</h2>
      </div>
      <a href="#faq" style="font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:8px">Read the FAQ
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"></path></svg></a>
    </div>
    <div class="terms" id="terms-list"></div>
  </div>
</section>

<!-- ============ WHY / TESTIMONIALS ============ -->
<section class="sec" style="padding-top:0">
  <div class="shell" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(30px,4vw,52px);align-items:center">
    <div style="min-width:0" data-reveal>
      <div class="eyebrow">{{ $c->data('why', 'eyebrow') }}</div>
      <h2 class="h2">{!! $c->heading('why') !!}</h2>
      <div class="whygrid" style="margin-top:26px" id="why"></div>
    </div>
    <div style="min-width:0" data-reveal>
      <div id="quoteWrap"></div>
      <div class="dots" style="margin-top:18px" id="quoteDots"></div>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="sec" id="faq" style="padding-top:0">
  <div class="shell" style="max-width:920px">
    <div style="text-align:center;margin-bottom:38px" data-reveal>
      <div class="eyebrow" style="justify-content:center">{{ $c->data('faq', 'eyebrow') }}</div>
      <h2 class="h2">{!! $c->heading('faq') !!}</h2>
    </div>
    <div style="display:grid;gap:12px" id="faqs"></div>
  </div>
</section>
</div>

<!-- ============ CTA ============ -->
<section class="cta dark-bay sec">
  <svg viewBox="0 0 1200 400" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%;opacity:.5" fill="none">
    <path d="M-20 300 C 300 120, 900 110, 1220 260" stroke="rgba(37,227,154,.28)" stroke-width="1.5" stroke-dasharray="9 13" style="animation:bdxDash 4.2s linear infinite"></path>
    <path d="M-20 350 C 340 200, 880 190, 1220 320" stroke="rgba(200,16,46,.22)" stroke-width="1.3" stroke-dasharray="4 16"></path>
  </svg>
  <div style="position:absolute;left:50%;top:-30%;width:760px;height:760px;transform:translateX(-50%);border-radius:50%;background:radial-gradient(circle,rgba(0,105,75,.4),transparent 62%)"></div>
  <div class="shell" style="position:relative;max-width:980px">
    <div class="eyebrow on-dark" style="justify-content:center">{{ $c->data('cta', 'eyebrow') }}</div>
    <h2 style="margin:18px 0 0" class="h2">{!! $c->heading('cta') !!}</h2>
    <p class="lede" style="color:#a8c9ba;margin:20px auto 32px;max-width:580px">{{ $c->body('cta') }}</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="#booking" class="btn btn-red" style="padding:17px 32px;font-size:16px">Book a pickup</a>
      <a href="tel:0411123393" class="btn btn-ghost" style="padding:17px 32px;font-size:16px">Call {{ $phoneAu }}</a>
    </div>
  </div>
</section>

<a href="tel:0411123393" class="callfab">
  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 3.5h3l1.5 4-2 1.5a12 12 0 006 6l1.5-2 4 1.5v3a2 2 0 01-2.2 2A16.5 16.5 0 014.5 5.7 2 2 0 016.5 3.5z"></path></svg>
  <span>Call us</span>
</a>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="shell cols">
    <div style="min-width:0">
      <div style="display:inline-flex;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:12px 16px;margin-bottom:18px">
        <img src="assets/bdex-logo.png" alt="BD-EX Cargo" style="height:40px;width:auto;filter:brightness(0) invert(1)">
      </div>
      <p style="font-size:14px;line-height:1.7;max-width:36ch">{{ $c->data('footer', 'blurb') }}</p>
      <div style="display:flex;gap:10px;margin-top:20px">
        @foreach ($c->data('footer', 'badges', []) as $badge)
          <span class="mono" style="font-size:10.5px;letter-spacing:2px;color:rgba(255,255,255,.4);border:1px solid rgba(255,255,255,.12);padding:7px 12px;border-radius:999px">{{ $badge }}</span>
        @endforeach
      </div>
    </div>
    <div id="fcols" style="display:contents"></div>
  </div>
  <div class="btm"><div class="shell in">
    <span>{{ $c->data('footer', 'legal') }}</span>
    <span>{{ $c->data('footer', 'site') }} · {{ $phoneAu }}</span>
  </div></div>
</footer>

{{-- The data the page's scripts render from: copy, rate card, tiers, TV bands. --}}
<script type="application/json" id="bdex-data">@json($payload)</script>
</body>
</html>
