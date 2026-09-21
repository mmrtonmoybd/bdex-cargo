/*
 * BD-EX Cargo public site behaviour.
 *
 * Ported from the approved prototype in docs/reference/prototype and kept
 * structurally identical — the reveal observer, hero scene rotator, spotlight,
 * marquee, counters, price tween, rate tabs, booking wizard, FAQ and testimonial
 * rotators all behave exactly as designed.
 *
 * Two things changed, both so the page cannot state something the system does not
 * hold: prices come from PricingEngine over the quote endpoint instead of arrays
 * in this file, and tracking and booking talk to the server instead of inventing
 * a timeline and a reference number.
 */
(function(){
'use strict';

/* ================= ICONS ================= */
var I = {
  check:'M4 12.5l5 5L20 6.5',
  plane:'M21 4L3 11l7 3 3 7 8-17z',
  box:'M3.5 7.5L12 3l8.5 4.5v9L12 21l-8.5-4.5v-9zM3.5 7.5L12 12l8.5-4.5M12 12v9',
  pin:'M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11zM12 12.2a2 2 0 100-4 2 2 0 000 4z',
  truck:'M3 16.5V6h11v10.5M14 9.5h4l3 3.5v3.5h-7M6.5 20a2 2 0 100-4 2 2 0 000 4zM17.5 20a2 2 0 100-4 2 2 0 000 4z',
  money:'M3 8h14l-3-3M21 16H7l3 3',
  cal:'M4 6.5h16v14H4zM4 10.5h16M8.5 3.5v4M15.5 3.5v4',
  headset:'M4 14v-2a8 8 0 1116 0v2M4 14h3v5H5.5A1.5 1.5 0 014 17.5zm16 0h-3v5h1.5a1.5 1.5 0 001.5-1.5z',
  shield:'M12 3l7.5 3v6c0 5-3.4 8-7.5 9-4.1-1-7.5-4-7.5-9V6z',
  sliders:'M4 8h10M18 8h2M4 16h4M12 16h8M14 8a2 2 0 104 0 2 2 0 10-4 0M8 16a2 2 0 104 0 2 2 0 10-4 0',
  map:'M3 6.5l6-2.5 6 2.5 6-2.5v14l-6 2.5-6-2.5-6 2.5zM9 4v14M15 6.5v14',
  coins:'M4 7c0-1.7 3.1-3 7-3s7 1.3 7 3-3.1 3-7 3-7-1.3-7-3zM4 7v5c0 1.7 3.1 3 7 3s7-1.3 7-3V7M4 12v5c0 1.7 3.1 3 7 3s7-1.3 7-3v-5',
  tag:'M3.5 11.5l8-8H20v8.5l-8 8zM16 8.2h.01',
  file:'M6 3.5h8l4 4v13H6zM14 3.5V8h4M9 12.5h6M9 16.5h4',
  bell:'M6 9a6 6 0 1112 0c0 5 2 6 2 6H4s2-1 2-6zM10 20a2 2 0 004 0',
  clock:'M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5.2l3.2 2',
  building:'M4 21V5.5L13 3v18M13 9h7v12M7.5 8.5h2M7.5 12.5h2M7.5 16.5h2M16 13h1.5M16 17h1.5',
  user:'M12 12a4 4 0 100-8 4 4 0 000 8zM4.5 20.5a7.5 7.5 0 0115 0',
  store:'M4 9.5V20h16V9.5M3 9.5L5 4h14l2 5.5a3 3 0 01-6 0 3 3 0 01-6 0 3 3 0 01-6 0zM9.5 20v-5.5h5V20',
  search:'M11 18a7 7 0 100-14 7 7 0 000 14zM16.2 16.2L21 21',
  warehouse:'M3 20V9l9-5 9 5v11M7 20v-7h10v7',
  globe:'M12 21a9 9 0 100-18 9 9 0 000 18zM3.5 9.5h17M3.5 14.5h17M12 3c-5 6-5 12 0 18 5-6 5-12 0-18z',
  alert:'M12 3.5a9 9 0 100 18 9 9 0 000-18zM12 8v5.5M12 16.5h.01',
  ok:'M12 21a9 9 0 100-18 9 9 0 000 18zM8 12.2l2.8 2.8L16 9.5',
  home:'M4 11l8-6.5 8 6.5v9H4zM9 15l2 2 4-4',
  crosshair:'M12 21a9 9 0 100-18 9 9 0 000 18zM12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M12 14a2 2 0 100-4 2 2 0 000 4z',
  arrow:'M4 12h15M13 6l6 6-6 6',
  chev:'M9 5l7 7-7 7',
  plus:'M12 5v14M5 12h14'
};
function ico(n, s, w){ return '<svg viewBox="0 0 24 24" width="'+(s||18)+'" height="'+(s||18)+'" fill="none" stroke="currentColor" stroke-width="'+(w||1.7)+'" stroke-linecap="round" stroke-linejoin="round"><path d="'+I[n]+'"></path></svg>'; }
function $(s){ return document.querySelector(s); }
function el(id){ return document.getElementById(id); }

/* ================= DATA =================
   Identical arrays to the prototype, read from the page instead of hardcoded.
   Copy comes from content_blocks; the rate card, tier sheet and TV bands come
   from the pricing tables, so the page and the calculator can never disagree. */
var B = JSON.parse(document.getElementById('bdex-data').textContent);
var NAV = B.nav, CHIPS = B.chips, MARQ = B.marquee, SLIDE_LABELS = B.slideLabels;
var STATS = B.stats, ABOUT_TAGS = B.aboutTags, ASSURANCES = B.assurances;
var SOLUTIONS = B.solutions, BENEFITS = B.benefits, PERKS = B.perks;
var CATS = B.cats, BD_TIERS = B.bdTiers, AUSBD_ROWS = B.ausbdRows;
var TV_CARDS = B.tvCards, TV_TAX = B.tvTax;
var JOURNEY = B.journey, DROP_RULES = B.dropRules, LABEL_FIELDS = B.labelFields;
var TERMS = B.terms, WHY = B.why, QUOTES = B.quotes, FAQS = B.faqs, FCOLS = B.footerColumns;
var ROUTES = B.routes, COPY = B.copy, ENDPOINTS = B.endpoints, CSRF = B.csrf;

/* ================= STATE ================= */
var S = {
  slide:0, quote:0, faq:0,
  dir:'ausbd', st:'nsw', cat:'general', weight:10, itemType:'garments',
  rateTab:'ausbd',
  step:1, done:false, ref:'',
  b:{senderName:'',senderPhone:'',senderAddr:'',recName:'',recPhone:'',recAddr:'',boxes:'2',notes:'',date:'',slot:'morning',service:'standard',agree:false}
};

/* ================= STATIC RENDERS ================= */
el('navLinks').innerHTML = NAV.map(function(l){ return '<a class="nlink" href="'+l.href+'">'+l.label+'</a>'; }).join('');
el('drawerLinks').innerHTML = NAV.map(function(l){ return '<a class="d" href="'+l.href+'" data-close>'+l.label+'<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5"><path d="'+I.chev+'"></path></svg></a>'; }).join('');
el('heroChips').innerHTML = CHIPS.map(function(c){ return '<span class="chip">'+c+'</span>'; }).join('');
el('marquee').innerHTML = (function(){ var one = MARQ.map(function(m){ return '<span>'+m+'</span>'; }).join(''); return one+one; })();
el('stats').innerHTML = STATS.map(function(s,i){
  return '<div class="cell"><div class="tag">'+s.tag+'</div><div class="n" data-count="'+(s.num||'')+'" data-pre="'+(s.pre||'')+'" data-post="'+(s.post||'')+'" data-comma="'+(s.comma?1:0)+'">'+(s.text||((s.pre||'')+'0'+(s.post||'')))+'</div><div class="l">'+s.l+'</div></div>';
}).join('');
el('aboutTags').innerHTML = ABOUT_TAGS.map(function(t){ return '<span>'+ico(t.i,16)+t.t+'</span>'; }).join('');
el('assurances').innerHTML = ASSURANCES.map(function(a){ return '<div class="row"><div class="n">'+a.n+'</div><div class="l">'+a.l+'</div></div>'; }).join('');
el('solutions-grid').innerHTML = SOLUTIONS.map(function(s,i){
  return '<div class="card" data-reveal><div class="art"><div class="mesh" style="background:'+s.g+'"></div>'+
    '<div class="idx">0'+(i+1)+'</div><div class="big" style="color:rgba(255,255,255,.13)"><svg viewBox="0 0 24 24" width="150" height="150" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="'+I[s.i]+'"></path></svg></div></div>'+
    '<div class="bd"><div style="display:flex;align-items:center;gap:10px;color:var(--blue)">'+ico(s.i,18)+'<h3>'+s.t+'</h3></div><p>'+s.d+'</p></div><span class="accent"></span></div>';
}).join('');
el('benefits').innerHTML = BENEFITS.map(function(b){
  return '<div class="benefit" data-reveal><span class="bi">'+ico(b.i,18)+'</span><div style="min-width:0"><h3>'+b.t+'</h3><p>'+b.d+'</p></div></div>';
}).join('');
el('perks').innerHTML = PERKS.map(function(p){ return '<div class="perk">'+ico('check',16,2.2)+'<span style="font-size:15px;line-height:1.55;color:#3a4d46">'+p+'</span></div>'; }).join('');
el('journey').innerHTML = JOURNEY.map(function(j){
  return '<div class="jcard" data-reveal><span class="bead"></span><span class="n">'+j.n+'</span><div class="t">'+j.t+'</div><p>'+j.d+'</p></div>';
}).join('');
el('dropRules').innerHTML = DROP_RULES.map(function(d){ return '<div class="rule">'+ico('ok',17)+'<span style="font-size:14.5px;line-height:1.6;color:#e7f0fa">'+d+'</span></div>'; }).join('');
el('labelFields').innerHTML = LABEL_FIELDS.map(function(f){ return '<div class="labelrow"><span style="font-size:13px;font-weight:700;color:#41544c">'+f[0]+'</span><span style="font-size:13.5px;color:#8a9a93">'+f[1]+'</span></div>'; }).join('');
el('terms-list').innerHTML = TERMS.map(function(t,i){
  var warn = (i===4||i===5);
  return '<div class="term" data-reveal><span style="color:'+(warn?'var(--red)':'var(--em)')+';margin-top:2px">'+ico(warn?'alert':'ok',18)+'</span><span>'+t+'</span></div>';
}).join('');
el('why').innerHTML = WHY.map(function(w){
  return '<div class="why"><span class="wi">'+ico(w.i,16)+'</span><div style="min-width:0"><div style="font-size:14.5px;font-weight:700">'+w.t+'</div><div style="font-size:13px;color:var(--muted);line-height:1.5;margin-top:2px">'+w.d+'</div></div></div>';
}).join('');
el('faqs').innerHTML = FAQS.map(function(f,i){
  return '<div class="faq'+(i===0?' on':'')+'" data-faq="'+i+'" data-reveal><button type="button"><span style="display:flex;align-items:baseline"><span class="qn">0'+(i+1)+'</span>'+f.q+'</span><span class="pm">'+ico('plus',15,2.1)+'</span></button><div class="body"><p>'+f.a+'</p></div></div>';
}).join('');
el('fcols').innerHTML = FCOLS.map(function(c){
  return '<div style="min-width:0"><div class="ct">'+c.title+'</div><div style="display:grid;gap:9px">'+c.items.map(function(i){ return '<a href="'+i[0]+'">'+i[1]+'</a>'; }).join('')+'</div></div>';
}).join('');

/* ================= NAV ================= */
var nav = el('nav');
el('burger').addEventListener('click', function(){ nav.classList.toggle('open'); });
document.addEventListener('click', function(e){
  var a = e.target.closest ? e.target.closest('[data-close]') : null;
  if (a) nav.classList.remove('open');
});
var prog = el('prog').firstElementChild;
function onScroll(){
  var y = window.scrollY || document.documentElement.scrollTop;
  var h = document.documentElement.scrollHeight - window.innerHeight;
  prog.style.width = (h > 0 ? (y / h * 100) : 0) + '%';
  if (y > 40) nav.classList.add('small'); else nav.classList.remove('small');
}
window.addEventListener('scroll', onScroll, {passive:true});
onScroll();

/* ================= HERO ================= */
var scenes = document.querySelectorAll('.hero .scene');
el('slideDots').innerHTML = SLIDE_LABELS.map(function(_,i){ return '<button type="button" data-slide="'+i+'"'+(i===0?' class="on"':'')+'></button>'; }).join('');
function setSlide(i){
  S.slide = i;
  for (var k=0;k<scenes.length;k++) scenes[k].classList.toggle('on', k===i);
  var ds = el('slideDots').children;
  for (var j=0;j<ds.length;j++) ds[j].classList.toggle('on', j===i);
  el('slideLabel').textContent = SLIDE_LABELS[i];
}
el('slideDots').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  setSlide(parseInt(b.getAttribute('data-slide'),10));
});
setInterval(function(){ setSlide((S.slide+1) % SLIDE_LABELS.length); }, 9000);

var hero = document.querySelector('.hero'), spot = el('spot'), route = el('route');
var TOUCH = window.matchMedia('(hover:none)').matches || ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
function moveSpot(cx, cy){
  var r = hero.getBoundingClientRect();
  var x = ((cx - r.left) / r.width * 100).toFixed(1);
  var y = ((cy - r.top) / r.height * 100).toFixed(1);
  spot.style.transform = 'none';
  spot.style.background = 'radial-gradient(520px circle at '+x+'% '+y+'%,rgba(37,227,154,.19),transparent 68%)';
}
/* no pointer on the device: the spotlight drifts on its own */
if (TOUCH) spot.classList.add('sweep');
hero.addEventListener('mousemove', function(e){
  spot.classList.remove('sweep');
  moveSpot(e.clientX, e.clientY);
});
hero.addEventListener('touchmove', function(e){
  if (!e.touches || !e.touches.length) return;
  spot.classList.remove('sweep');
  moveSpot(e.touches[0].clientX, e.touches[0].clientY);
  setTimeout(function(){ spot.style.transform=''; spot.classList.add('sweep'); }, 2600);
}, {passive:true});
window.addEventListener('scroll', function(){
  var y = window.scrollY || 0;
  if (y < 1000 && route){ var sc = window.innerWidth <= 900 ? 0.72 : 1;
    route.style.transform = 'translate(-50%,-50%) translateY(' + (y * 0.16) + 'px) scale(' + sc + ')'; }
}, {passive:true});

/* ================= TRACKING =================
   Same panel as the design. The steps come from the server rather than a fixed
   demo timeline, so an unknown reference honestly reports that it is unknown. */
function trackMarkup(ref, status, steps, at){
  return '<div style="margin-top:20px;border-top:1px dashed rgba(255,255,255,.2);padding-top:18px;animation:bdxUp .5s ease both">'+
    '<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px"><span style="font-size:13px;color:#9fbfae">Ref <strong class="mono" style="color:#fff">'+ref+'</strong></span>'+
    '<span class="mono" style="font-size:10.5px;font-weight:600;letter-spacing:1.6px;color:#04150f;background:var(--sig);padding:5px 11px;border-radius:999px">'+status+'</span></div>'+
    steps.map(function(t,i){
      var done = i <= at, last = i === steps.length-1;
      return '<div style="display:grid;grid-template-columns:22px 1fr;gap:12px;align-items:start">'+
        '<div style="display:grid;justify-items:center;gap:2px"><span style="width:11px;height:11px;border-radius:50%;background:'+(done?'var(--sig)':'rgba(255,255,255,.18)')+(i===at?';animation:bdxRing 2s infinite':'')+'"></span>'+
        '<span style="width:2px;height:'+(last?'0':'26px')+';background:'+(i<at?'var(--sig)':'rgba(255,255,255,.14)')+';display:block"></span></div>'+
        '<div style="padding-bottom:14px"><div style="font-size:14px;font-weight:'+(done?700:500)+';color:'+(done?'#fff':'rgba(255,255,255,.45)')+'">'+t[0]+'</div>'+
        '<div class="mono" style="font-size:11px;color:rgba(255,255,255,.38);margin-top:2px">'+t[1]+'</div></div></div>';
    }).join('')+'</div>';
}
function trackError(msg){
  return '<div style="margin-top:14px;font-size:13px;color:#ff8aa0;background:rgba(200,16,46,.16);border:1px solid rgba(200,16,46,.35);padding:12px 14px;border-radius:12px">'+msg+'</div>';
}
function doTrack(){
  var v = (el('trackIn').value || '').trim();
  var out = el('trackOut');
  if (!v){ out.innerHTML = trackError(COPY.trackEmpty); return; }
  out.innerHTML = '<div style="margin-top:14px;font-size:13px;color:#9fbfae">Checking…</div>';
  fetch(ENDPOINTS.track + encodeURIComponent(v), {headers:{'Accept':'application/json'}})
    .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }); })
    .then(function(res){
      if (!res.ok || !res.body.found){ out.innerHTML = trackError(res.body.message || COPY.trackMissing); return; }
      out.innerHTML = trackMarkup(res.body.reference, res.body.status_label, res.body.steps, res.body.current_step);
    })
    .catch(function(){ out.innerHTML = trackError(COPY.trackMissing); });
}
el('trackBtn').addEventListener('click', doTrack);
el('trackIn').addEventListener('keydown', function(e){ if (e.key === 'Enter') doTrack(); });

/* ================= CALCULATOR =================
   The prototype worked the rates out in the browser. Here the numbers come from
   PricingEngine over /api/quote/preview, so the page can only ever show a price
   the rate card actually holds. The readout, the tween and the itemised lines are
   the design's own. */
function catById(id){ for (var i=0;i<CATS.length;i++) if (CATS[i].id===id) return CATS[i]; return CATS[0]; }
function money(n){ return '$' + Number(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

var shown = 0, raf = 0, lastTarget = null;
function tweenPrice(target){
  if (lastTarget === target) return;
  lastTarget = target;
  cancelAnimationFrame(raf);
  var from = shown, t0 = performance.now(), dur = 520;
  function step(now){
    var k = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - k, 3);
    shown = from + (target - from) * e;
    el('priceBig').textContent = money(shown);
    el('priceSmall').textContent = money(shown);
    if (k < 1) raf = requestAnimationFrame(step);
  }
  raf = requestAnimationFrame(step);
}

var lastQuote = {total: 0, lines: [], eta: ''};
var quoteTimer = 0, quoteSeq = 0;

function quotePayload(){
  return {
    direction: S.dir,
    zone: S.st,
    category: S.cat,
    weight_kg: Math.max(0, parseFloat(S.weight) || 0),
    item_type: S.itemType
  };
}

function paintQuote(q){
  lastQuote = q;
  el('etaTxt').textContent = q.eta || '';
  el('qLines').innerHTML = (q.lines || []).map(function(l){
    return '<div class="qline"><span>'+l[0]+'</span><strong>'+l[1]+'</strong></div>';
  }).join('');
  tweenPrice(q.total || 0);
}

function renderQuote(){
  clearTimeout(quoteTimer);
  quoteTimer = setTimeout(function(){
    var seq = ++quoteSeq;
    fetch(ENDPOINTS.quote, {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
      body: JSON.stringify(quotePayload())
    })
    .then(function(r){ return r.json(); })
    .then(function(q){ if (seq === quoteSeq) paintQuote(q); })
    .catch(function(){ /* leave the last good figure on screen */ });
  }, 180);
}

function renderCalcControls(){
  el('dirTabs').innerHTML =
    '<button type="button" data-dir="ausbd" class="'+(S.dir==='ausbd'?'on':'')+'">'+ROUTES.ausbd.name+'</button>'+
    '<button type="button" data-dir="bdaus" class="'+(S.dir==='bdaus'?'on':'')+'">'+ROUTES.bdaus.name+'</button>';
  el('stateTabs').innerHTML = B.zones.map(function(z){
    return '<button type="button" class="pill '+(S.st===z.id?'on':'')+'" data-st="'+z.id+'">'+z.label+'</button>';
  }).join('');
  el('itemTabs').innerHTML =
    '<button type="button" class="pill '+(S.itemType==='garments'?'on':'')+'" data-it="garments">Garments</button>'+
    '<button type="button" class="pill red '+(S.itemType==='non-garments'?'on':'')+'" data-it="non-garments">Non-garments</button>';
  if (!el('catSel').options.length){
    el('catSel').innerHTML = CATS.map(function(c){ return '<option value="'+c.id+'">'+c.label+'</option>'; }).join('');
  }
  el('catSel').value = S.cat;
  el('ausbdFields').style.display = S.dir === 'ausbd' ? '' : 'none';
  el('bdausFields').style.display = S.dir === 'bdaus' ? '' : 'none';
  var flat = S.dir === 'ausbd' && catById(S.cat).type === 'flat';
  el('weightWrap').style.display = flat ? 'none' : '';
  el('wLabel').textContent = S.weight + ' kg';
  el('wRange').value = S.weight;
  if (document.activeElement !== el('wNum')) el('wNum').value = S.weight;
  renderQuote();
}
el('dirTabs').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  S.dir = b.getAttribute('data-dir'); renderCalcControls();
});
el('stateTabs').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  S.st = b.getAttribute('data-st'); renderCalcControls();
});
el('itemTabs').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  S.itemType = b.getAttribute('data-it'); renderCalcControls();
});
el('catSel').addEventListener('change', function(e){ S.cat = e.target.value; renderCalcControls(); });
el('wRange').addEventListener('input', function(e){ S.weight = parseFloat(e.target.value); renderCalcControls(); });
el('wNum').addEventListener('input', function(e){
  var v = parseFloat(e.target.value);
  S.weight = isNaN(v) ? 0 : v;
  el('wLabel').textContent = S.weight + ' kg';
  el('wRange').value = Math.min(80, Math.max(1, S.weight));
  renderQuote();
});

/* ================= RATE TABLES ================= */
function renderRateTabs(){
  el('rateTabs').innerHTML = [['ausbd','Australia → Bangladesh'],['bdaus','Bangladesh → Australia'],['tv','Sending a TV to Bangladesh']]
    .map(function(t){ return '<button type="button" class="pill red '+(S.rateTab===t[0]?'on':'')+'" data-rt="'+t[0]+'">'+t[1]+'</button>'; }).join('');
}
function renderRatePanel(){
  var h = '';
  if (S.rateTab === 'ausbd'){
    h = '<div class="waybill"><div class="wb-top"><span>AIR WAYBILL · RATE CARD</span><span>AUS → BD · EFFECTIVE 2026</span></div><div class="perf"></div>'+
      '<div class="rt-head"><div class="c1">ITEM CATEGORY</div><div class="c2">NSW RATE</div><div class="c3">ACT · VIC · QLD · SA · WA · TAS</div></div>'+
      AUSBD_ROWS.map(function(r){
        return '<div class="rt-row"><div class="nm">'+r.name+(r.note?'<div class="nt">'+r.note+'</div>':'')+'</div><div class="v1">'+r.nsw+'</div><div class="v2">'+r.other+'</div></div>';
      }).join('')+
      '<div class="wb-foot">$35 airport fee per shipment · Free home delivery after customs clearance · 10–18 working days from flight date.</div></div>';
  } else if (S.rateTab === 'bdaus'){
    h = '<div style="margin-top:34px;display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:18px">'+
      '<div class="waybill" style="margin-top:0"><div class="wb-top"><span>TIER SHEET</span><span>BD → AUS</span></div><div class="perf"></div>'+
      '<div class="tiers"><div class="th a">WEIGHT (KG)</div><div class="th b">PRICE PER KG</div>'+
      BD_TIERS.map(function(t,i){ return '<div class="td" style="background:'+(i%2?'#fbfdfc':'#fff')+'">'+t.w+'</div><div class="td b" style="background:'+(i%2?'#fbfdfc':'#fff')+'">$'+t.rate+'</div>'; }).join('')+
      '</div><div class="wb-foot" style="color:var(--red);font-weight:700">*For garment items only</div></div>'+
      '<div style="display:flex;flex-direction:column;gap:18px;min-width:0">'+
        '<div class="eta-card"><div class="mono" style="font-size:10.5px;letter-spacing:2.6px;color:#9fdcc0">DELIVERY TIME</div><div class="big">4+</div><div style="font-size:15px;color:#cfeadd">estimated business days, Bangladesh to Australia</div></div>'+
        '<div style="background:#fff;border:1px solid var(--line);border-radius:22px;padding:26px"><h3 style="font-size:19px;letter-spacing:-.4px">Booking formalities</h3>'+
        '<p style="margin:12px 0;font-size:14.5px;line-height:1.7;color:#41544c">Book online below, or SMS <strong>'+B.phoneAu+'</strong> with the sender (Bangladesh) and receiver (Australia) name, address and mobile number. Our Dhaka pickup team calls your sender within 24 hours.</p>'+
        '<p style="font-size:14.5px;line-height:1.7;color:var(--muted)">Nothing to pay up front. Your sender hands the parcel to our driver, and payment can be settled on either side — Bangladesh or Australia.</p></div>'+
      '</div></div>';
  } else {
    h = '<div style="margin-top:34px"><p class="lede" style="max-width:780px;margin-bottom:24px">Approximate cost guideline for sending a TV from Sydney to Bangladesh. Every line below is added together — freight by weight, airport processing, volume fee, Bangladesh government duty and local home delivery.</p>'+
      '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:18px">'+
      TV_CARDS.map(function(t){
        return '<div class="tvcard"><div class="hd"><strong>'+t.size+'</strong><span class="mono" style="font-size:10.5px;letter-spacing:1.8px;color:#bfd6ef">TELEVISION</span></div>'+
          '<div style="padding:8px 20px 18px">'+t.lines.map(function(l){ return '<div class="kv"><span>'+l[0]+'</span><strong>'+l[1]+'</strong></div>'; }).join('')+'</div></div>';
      }).join('')+'</div>'+
      '<div style="margin-top:20px;background:#fff;border:1px solid var(--line);border-radius:22px;padding:26px"><h3 style="font-size:18px;margin-bottom:14px">TV tax in Bangladesh (BDT)</h3>'+
      '<div style="display:flex;flex-wrap:wrap;gap:10px">'+TV_TAX.map(function(x){ return '<div style="background:#f6faf7;border:1px solid var(--line);border-radius:13px;padding:10px 16px;font-size:14px"><strong>'+x[0]+'</strong> <span style="color:var(--muted)">·</span> <span class="mono">'+x[1]+'</span></div>'; }).join('')+'</div></div></div>';
  }
  el('ratePanel').innerHTML = h;
  tagReveals();
  litScan();
}
el('rateTabs').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  S.rateTab = b.getAttribute('data-rt'); renderRateTabs(); renderRatePanel();
});

/* ================= BOOKING ================= */
var STEP_NAMES = ['SERVICE','ADDRESSES','PARCEL','REVIEW'];
function fld(name, label, ph, type){
  var v = S.b[name] || '';
  return '<div><label class="lbl">'+label+'</label><input class="fld" data-b="'+name+'" type="'+(type||'text')+'" value="'+v.replace(/"/g,'&quot;')+'" placeholder="'+(ph||'')+'"></div>';
}
function area(name, label, ph, rows){
  return '<div><label class="lbl">'+label+'</label><textarea class="fld" data-b="'+name+'" rows="'+(rows||2)+'" placeholder="'+(ph||'')+'">'+(S.b[name]||'')+'</textarea></div>';
}
function renderBooking(){
  var bars = el('stepBars').children;
  for (var i=0;i<bars.length;i++) bars[i].classList.toggle('on', i < S.step);
  el('stepNum').textContent = 'STEP ' + S.step + ' / 4';
  el('stepName').textContent = STEP_NAMES[S.step-1];

  if (S.done){
    el('bookBody').innerHTML = '<div class="done"><div style="width:64px;height:64px;border-radius:50%;background:#e7f5ed;color:var(--em);display:grid;place-items:center;margin:0 auto 18px">'+ico('ok',28,1.8)+'</div>'+
      '<h3 style="font-size:26px;letter-spacing:-.8px">Booking received</h3>'+
      '<p style="margin:10px 0 18px;font-size:15px;line-height:1.6;color:#41544c">We\'ll call your sender within 24 hours and email the box labels. Keep this reference handy.</p>'+
      '<div class="ref">'+S.ref+'</div>'+
      '<div style="margin-top:22px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><button type="button" class="btn btn-line" data-act="reset">Make another booking</button>'+
      '<a href="#top" class="btn" style="background:var(--blue);color:#fff;padding:14px 22px;border-radius:999px;font-size:15px">Track this parcel</a></div></div>';
    return;
  }
  var h = '';
  if (S.step === 1){
    h = '<div style="display:grid;gap:18px">'+
      '<div><label class="lbl">Direction</label><div class="pillrow">'+
        '<button type="button" class="pill '+(S.dir==='bdaus'?'on':'')+'" data-bdir="bdaus">Bangladesh → Australia</button>'+
        '<button type="button" class="pill '+(S.dir==='ausbd'?'on':'')+'" data-bdir="ausbd">Australia → Bangladesh</button></div></div>'+
      '<div><label class="lbl">Service</label><div class="pillrow">'+
        ['standard','express','drop-off at Australia Post'].map(function(x){
          return '<button type="button" class="pill blue '+(S.b.service===x?'on':'')+'" data-svc="'+x+'">'+x.charAt(0).toUpperCase()+x.slice(1)+'</button>';
        }).join('')+'</div></div>'+
      '<div class="grid2">'+fld('date','Preferred pickup date','','date')+
      '<div><label class="lbl">Time slot</label><select class="fld" data-b="slot">'+
        [['morning','Morning · 9am–1pm'],['afternoon','Afternoon · 1pm–5pm'],['evening','Evening · 5pm–8pm']].map(function(o){
          return '<option value="'+o[0]+'"'+(S.b.slot===o[0]?' selected':'')+'>'+o[1]+'</option>'; }).join('')+
      '</select></div></div></div>';
  } else if (S.step === 2){
    h = '<div style="display:grid;gap:16px">'+
      '<div class="grid2">'+fld('senderName','Sender name','Full name')+fld('senderPhone','Sender mobile','+880…')+'</div>'+
      area('senderAddr','Pickup address','House, road, area, city')+
      '<div style="height:1px;background:var(--line)"></div>'+
      '<div class="grid2">'+fld('recName','Receiver name','Full name')+fld('recPhone','Receiver mobile','04…')+'</div>'+
      area('recAddr','Delivery address','Street, suburb, state, postcode')+'</div>';
  } else if (S.step === 3){
    h = '<div style="display:grid;gap:16px">'+
      '<div class="grid2">'+fld('boxes','Number of boxes','2')+
      '<div><label class="lbl">Approx. weight (kg)</label><input class="fld" data-w="1" value="'+S.weight+'"></div></div>'+
      '<div><label class="lbl">Contents</label><select class="fld" data-cat="1">'+CATS.map(function(c){ return '<option value="'+c.id+'"'+(S.cat===c.id?' selected':'')+'>'+c.label+'</option>'; }).join('')+'</select></div>'+
      area('notes','Packing list / handling notes','Quantity of each item, size of liquids (ml/g), number of sharee, kamiz, shirts, pants…',3)+
      '<div class="warn">Prohibited: perfume, body spray, flammables, alcohol, cigarettes, gold, diamonds, currency, AA/AAA batteries, adult toys. Each box max 20 kg and under 1 metre.</div></div>';
  } else {
    var q = lastQuote;
    var rows = [
      ['Direction', S.dir === 'ausbd' ? 'Australia → Bangladesh' : 'Bangladesh → Australia'],
      ['Service', S.b.service],
      ['Pickup', (S.b.date || 'date not set') + ' · ' + S.b.slot],
      ['Sender', (S.b.senderName || '—') + ' · ' + (S.b.senderPhone || '—')],
      ['Receiver', (S.b.recName || '—') + ' · ' + (S.b.recPhone || '—')],
      ['Parcel', S.b.boxes + ' box(es) · ' + S.weight + ' kg']
    ];
    h = '<div class="review">'+rows.map(function(r){ return '<div class="kv"><span style="color:var(--muted)">'+r[0]+'</span><strong style="text-align:right">'+r[1]+'</strong></div>'; }).join('')+
      '<div style="display:flex;justify-content:space-between;gap:16px;padding-top:14px;font-size:18px;font-weight:800"><span>Estimated cost</span><span style="color:var(--red)" class="mono">'+money(q.total)+'</span></div></div>'+
      '<label style="display:flex;gap:10px;align-items:flex-start;margin-top:16px;font-size:13.5px;color:#41544c;line-height:1.55;cursor:pointer">'+
      '<input type="checkbox" data-agree="1"'+(S.b.agree?' checked':'')+' style="margin-top:3px;accent-color:var(--em);width:17px;height:17px">'+
      '<span>I confirm the packing list is complete and contains no prohibited items, and I accept BD-EX Cargo\'s shipping terms.</span></label>';
  }
  h += '<div id="bookError" style="margin-top:14px;color:var(--red);font-size:13.5px"></div>';
  h += '<div style="display:flex;gap:10px;margin-top:26px;flex-wrap:wrap">'+
    (S.step > 1 ? '<button type="button" class="btn btn-line" data-act="back">Back</button>' : '')+
    '<button type="button" class="btn btn-red" style="flex:1 1 160px" data-act="next">'+(S.step === 4 ? 'Confirm booking' : 'Continue')+'</button></div>';
  el('bookBody').innerHTML = h;
}
el('bookBody').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  var act = b.getAttribute('data-act');
  if (b.hasAttribute('data-bdir')){ S.dir = b.getAttribute('data-bdir'); renderCalcControls(); renderBooking(); return; }
  if (b.hasAttribute('data-svc')){ S.b.service = b.getAttribute('data-svc'); renderBooking(); return; }
  if (act === 'back'){ S.step = Math.max(1, S.step - 1); renderBooking(); }
  if (act === 'reset'){ S.done = false; S.step = 1; renderBooking(); }
  if (act === 'next'){
    if (S.step < 4){ S.step++; renderBooking(); return; }
    // The reference is issued by the server against a stored enquiry, so the number
    // the customer writes down is one staff can actually look up.
    b.disabled = true;
    fetch(ENDPOINTS.booking, {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
      body: JSON.stringify({
        direction: S.dir, service: S.b.service, date: S.b.date, slot: S.b.slot,
        sender_name: S.b.senderName, sender_phone: S.b.senderPhone, sender_address: S.b.senderAddr,
        receiver_name: S.b.recName, receiver_phone: S.b.recPhone, receiver_address: S.b.recAddr,
        boxes: S.b.boxes, weight_kg: S.weight, category: S.cat,
        notes: S.b.notes, agree: S.b.agree, zone: S.st, item_type: S.itemType
      })
    })
    .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, body:j}; }); })
    .then(function(res){
      b.disabled = false;
      if (!res.ok){ el('bookError').textContent = res.body.message || COPY.bookingFailed; return; }
      S.done = true; S.ref = res.body.reference; renderBooking();
    })
    .catch(function(){ b.disabled = false; el('bookError').textContent = COPY.bookingFailed; });
  }
});
el('bookBody').addEventListener('input', function(e){
  var t = e.target, k = t.getAttribute('data-b');
  if (k){ S.b[k] = t.value; return; }
  if (t.hasAttribute('data-w')){ var v = parseFloat(t.value); S.weight = isNaN(v) ? 0 : v; renderCalcControls(); }
  if (t.hasAttribute('data-cat')){ S.cat = t.value; renderCalcControls(); }
  if (t.hasAttribute('data-agree')){ S.b.agree = t.checked; }
});
el('bookBody').addEventListener('change', function(e){
  var t = e.target;
  if (t.getAttribute('data-b') === 'slot'){ S.b.slot = t.value; }
  if (t.hasAttribute('data-agree')){ S.b.agree = t.checked; }
});

/* ================= FAQ ================= */
el('faqs').addEventListener('click', function(e){
  var card = e.target.closest('.faq'); if (!card) return;
  var i = parseInt(card.getAttribute('data-faq'), 10);
  S.faq = (S.faq === i) ? -1 : i;
  var all = el('faqs').children;
  for (var k=0;k<all.length;k++) all[k].classList.toggle('on', k === S.faq);
});

/* ================= TESTIMONIALS ================= */
function initials(n){ return n.split(' ').map(function(p){ return p.charAt(0); }).join(''); }
function renderQuoteCard(){
  var q = QUOTES[S.quote];
  el('quoteWrap').innerHTML = '<div class="boarding">'+
    '<div class="mono" style="font-size:10.5px;letter-spacing:2.4px;color:var(--muted);display:flex;justify-content:space-between"><span>CUSTOMER · 0'+(S.quote+1)+'</span><span>BD-EX</span></div>'+
    '<div style="font-family:var(--disp);font-size:40px;color:var(--red);line-height:.7;margin:18px 0 4px">“</div>'+
    '<p style="font-size:18px;line-height:1.65;color:#22332d;text-wrap:pretty;margin-bottom:26px">'+q.text+'</p>'+
    '<div style="display:flex;align-items:center;gap:12px;padding-top:22px"><span class="mono-av">'+initials(q.name)+'</span>'+
    '<div><div style="font-size:14.5px;font-weight:700">'+q.name+'</div><div class="mono" style="font-size:11px;color:var(--muted);letter-spacing:1.4px;margin-top:2px">'+q.city.toUpperCase()+'</div></div></div></div>';
  el('quoteDots').innerHTML = QUOTES.map(function(_,i){ return '<button type="button" data-q="'+i+'" class="'+(i===S.quote?'on':'')+'" style="background:'+(i===S.quote?'var(--red)':'var(--line)')+'"></button>'; }).join('');
}
el('quoteDots').addEventListener('click', function(e){
  var b = e.target.closest('button'); if (!b) return;
  S.quote = parseInt(b.getAttribute('data-q'), 10); renderQuoteCard();
});
setInterval(function(){ S.quote = (S.quote + 1) % QUOTES.length; renderQuoteCard(); }, 7000);


/* ================= TOUCH: SCROLL-DRIVEN HOVER STATES ================= */
var LIT_SEL = '.card,.benefit,.jcard,.rule,.why,.term,.tvcard,.assure .row,.rt-row';
var litIO = null;
if (TOUCH && 'IntersectionObserver' in window){
  litIO = new IntersectionObserver(function(entries){
    entries.forEach(function(en){ en.target.classList.toggle('lit', en.isIntersecting); });
  }, {rootMargin:'-42% 0px -42% 0px', threshold:0});
}
function litScan(){
  if (!litIO) return;
  Array.prototype.forEach.call(document.querySelectorAll(LIT_SEL), function(n){
    if (n.dataset.lit) return;
    n.dataset.lit = '1';
    litIO.observe(n);
  });
}

/* ================= REVEAL + COUNTERS ================= */
var io = new IntersectionObserver(function(entries){
  entries.forEach(function(en){
    if (!en.isIntersecting) return;
    en.target.classList.add('seen');
    io.unobserve(en.target);
  });
}, {rootMargin:'0px 0px -6% 0px', threshold:0.08});
function tagReveals(){
  var nodes = document.querySelectorAll('[data-reveal]:not(.seen)');
  Array.prototype.forEach.call(nodes, function(n, i){
    if (n.dataset.tagged) return;
    n.dataset.tagged = '1';
    n.style.animationDelay = ((i % 4) * 0.07) + 's';
    io.observe(n);
  });
}
var counted = false;
var cio = new IntersectionObserver(function(entries){
  entries.forEach(function(en){
    if (!en.isIntersecting || counted) return;
    counted = true;
    Array.prototype.forEach.call(document.querySelectorAll('[data-count]'), function(n){
      var target = parseFloat(n.getAttribute('data-count'));
      if (!target) return;
      var pre = n.getAttribute('data-pre') || '', post = n.getAttribute('data-post') || '';
      var comma = n.getAttribute('data-comma') === '1', t0 = performance.now(), dur = 1400;
      (function tick(now){
        var k = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - k, 3);
        var v = Math.round(target * e);
        n.textContent = pre + (comma ? v.toLocaleString('en-US') : v) + post;
        if (k < 1) requestAnimationFrame(tick);
      })(performance.now());
    });
  });
}, {threshold:0.15, rootMargin:'0px 0px -10% 0px'});
cio.observe(document.querySelector('.instr'));

/* ================= INIT ================= */
renderCalcControls();
renderRateTabs();
renderRatePanel();
renderBooking();
renderQuoteCard();
tagReveals();
litScan();
[300, 1200, 2600].forEach(function(ms){ setTimeout(function(){ tagReveals(); litScan(); }, ms); });
})();
