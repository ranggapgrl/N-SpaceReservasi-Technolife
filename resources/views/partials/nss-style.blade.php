{{--
    CSS mandiri untuk halaman Home & Booking.
    Tidak bergantung pada hasil build Tailwind, jadi layout tetap responsif
    walaupun class Tailwind baru belum di-compile.
    Simpan di: resources/views/partials/nss-style.blade.php
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">

<style>
:root {
    --nss-primary: #115e59;
    --nss-ink: #1f2a37;
    --nss-muted: #64748b;
    --nss-line: #dbe3e8;
    --nss-paper: #f3f6f7;
    --nss-ring: rgba(17, 94, 89, .22);
    --nss-tint: rgba(17, 94, 89, .06);
    --nss-hl: #f3dd5c;
}
[x-cloak] { display: none !important; }


/* ---------- dasar ---------- */
.nss { font-family: 'Albert Sans', ui-sans-serif, system-ui, sans-serif; color: var(--nss-ink); line-height: 1.5; -webkit-font-smoothing: antialiased; }
.nss *, .nss *::before, .nss *::after { box-sizing: border-box; }
:where(.nss) :is(h1, h2, h3, h4, p, ul, ol, dl, dd, figure, address) { margin: 0; padding: 0; }
:where(.nss) :is(ul, ol) { list-style: none; }
:where(.nss) button { font: inherit; color: inherit; background: none; border: 0; padding: 0; cursor: pointer; }
:where(.nss) img { display: block; max-width: 100%; }
:where(.nss) a { color: inherit; text-decoration: none; }
:where(.nss) address { font-style: normal; }
.nss :focus-visible { outline: 2px solid var(--nss-primary); outline-offset: 2px; }

.nss-display { font-family: 'Bricolage Grotesque', 'Albert Sans', ui-sans-serif, system-ui, sans-serif; letter-spacing: -.02em; }
.nss-num { font-variant-numeric: tabular-nums; }
.nss-muted { color: var(--nss-muted); }
.nss-small { font-size: .875rem; }
.nss-xs { font-size: .75rem; }
.nss-bold { font-weight: 700; }
.nss-semi { font-weight: 600; }

.nss-h1 { font-size: clamp(2.25rem, 7vw, 3.75rem); line-height: 1.08; font-weight: 700; }
.nss-h2 { font-size: clamp(1.75rem, 4.5vw, 2.5rem); line-height: 1.15; font-weight: 700; }
.nss-h3 { font-size: 1.125rem; font-weight: 600; line-height: 1.3; }

.nss-container { width: 100%; max-width: 80rem; margin: 0 auto; padding: 0 1.25rem; }
.nss-wrap { width: 100%; max-width: 64rem; margin: 0 auto; padding: 0 1rem; }
@media (min-width: 640px) { .nss-wrap { padding: 0 1.5rem; } }
@media (min-width: 768px) { .nss-container { padding: 0 2.5rem; } }

/* ---------- tombol ---------- */
.nss-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; height: 3rem; padding: 0 2rem; border-radius: 8px; background: var(--nss-primary); color: #fff; font-weight: 600; font-size: 1rem; line-height: 1; white-space: nowrap; transition: transform .15s, filter .15s, opacity .15s; }
.nss-btn:hover { filter: brightness(.9); }
.nss-btn:active { transform: scale(.97); }
.nss-btn:disabled { opacity: .4; cursor: not-allowed; filter: none; transform: none; }
.nss-btn--block { width: 100%; }
.nss-link { font-weight: 600; text-decoration: underline; text-decoration-color: var(--nss-hl); text-decoration-thickness: 4px; text-underline-offset: 4px; transition: text-decoration-color .2s; }
.nss-link:hover { text-decoration-color: var(--nss-primary); }

/* ---------- penanda stabilo ---------- */
.nss-mark { background: linear-gradient(transparent 55%, var(--nss-hl) 55%) no-repeat left / 0% 100%; transition: background-size .5s cubic-bezier(.22, .8, .3, 1) .1s; padding: 0 .15em; margin: 0 -.15em; }
.nss-mark.is-on, input:checked + .nss-tarif .nss-mark { background-size: 100% 100%; }

/* ---------- tiket day pass ---------- */
.nss-pass { position: relative; display: flex; flex-direction: column; background: #fff; border-radius: 10px; box-shadow: 0 10px 28px -14px rgba(14, 70, 72, .35); }
.nss-pass-body { padding: 1.25rem; }
.nss-pass-foot { padding: .75rem 1.25rem 1.25rem; }
.nss-perf { position: relative; height: 20px; }
.nss-perf::before, .nss-perf::after { content: ''; position: absolute; top: 0; width: 20px; height: 20px; border-radius: 50%; background: var(--nss-paper); }
.nss-perf::before { left: -10px; }
.nss-perf::after { right: -10px; }
.nss-perf span { position: absolute; left: 1.25rem; right: 1.25rem; top: 50%; border-top: 2px dashed var(--nss-line); }
.nss-row { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; }
.nss-row dd, .nss-row .nss-r { text-align: right; font-weight: 600; }
.nss-rows { display: grid; gap: .625rem; margin-top: 1.25rem; font-size: .875rem; }
.nss-lines { display: grid; gap: .5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--nss-line); font-size: .875rem; font-variant-numeric: tabular-nums; }
.nss-lines li { display: flex; justify-content: space-between; gap: 1rem; }
.nss-lines li span:first-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.nss-total { font-size: 1.875rem; font-weight: 700; line-height: 1; color: var(--nss-primary); font-variant-numeric: tabular-nums; }
.nss-bump { animation: nss-roll .25s ease-out; }
@keyframes nss-roll { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

/* ---------- galeri area (dipakai di home & booking) ---------- */
/* aspect-ratio: 16/9 lebih lebar dari 4/3 sehingga tidak terlalu tinggi di mobile */
/* min-height: 180px memastikan galeri tidak terlalu kecil di HP sempit */
.nss-gal-frame { position: relative; aspect-ratio: 16 / 9; min-height: 180px; overflow: hidden; border-radius: 10px; background: var(--nss-tint); box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .05); }
.nss-gal-slide { position: absolute; inset: 0; margin: 0; opacity: 0; transform: scale(1.04); pointer-events: none; transition: opacity .7s ease-out, transform .7s ease-out; }
.nss-gal-slide.is-active { opacity: 1; transform: none; pointer-events: auto; }
.nss-gal-fallback { position: absolute; inset: 0; display: grid; place-items: center; padding: 1.5rem; text-align: center; font-size: 1.5rem; font-weight: 600; color: var(--nss-primary); opacity: .5; }
.nss-gal-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }

.nss-tabs-wrap { border-bottom: 1px solid var(--nss-line); }
.nss-tabs { display: flex; gap: 1.5rem; overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.nss-tabs::-webkit-scrollbar { display: none; }
.nss-tab { flex: none; white-space: nowrap; padding: .75rem 0 .625rem; font-weight: 600; color: var(--nss-muted); border-bottom: 2px solid transparent; transition: color .2s, border-color .2s; }
.nss-tab:hover { color: var(--nss-ink); }
.nss-tab.is-active { color: var(--nss-ink); border-bottom-color: var(--nss-primary); }

.nss-stack { display: grid; margin-top: 1rem; }
.nss-stack > * { grid-area: 1 / 1; opacity: 0; transform: translateY(4px); pointer-events: none; transition: opacity .3s ease-out, transform .3s ease-out; }
.nss-stack > .is-active { opacity: 1; transform: none; pointer-events: auto; }
.nss-facilities { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem 1rem; margin-top: .75rem; font-size: .875rem; }
.nss-facilities li { display: flex; align-items: flex-start; gap: .5rem; }
.nss-facilities svg { flex: none; margin-top: .15rem; color: var(--nss-primary); }

/* ---------- form ---------- */
.nss-radio { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }
.nss-label { display: block; margin-bottom: .5rem; font-size: .875rem; font-weight: 600; }
.nss-field { display: block; width: 100%; height: 3rem; padding: 0 .75rem; border: 1px solid var(--nss-line); border-radius: 8px; background: #fff; font: inherit; color: inherit; transition: border-color .2s, box-shadow .2s; }
.nss-field:focus { outline: none; border-color: var(--nss-primary); box-shadow: 0 0 0 3px var(--nss-ring); }

/* ---------- animasi pindah langkah ---------- */
.nss-enter { transition: opacity .3s ease-out, transform .3s ease-out; }
.nss-enter-from { opacity: 0; transform: translateY(10px); }
.nss-enter-to { opacity: 1; transform: none; }
.nss-spin { animation: nss-spin .8s linear infinite; }
@keyframes nss-spin { to { transform: rotate(360deg); } }

@media (prefers-reduced-motion: reduce) {
    .nss *, .nss *::before, .nss *::after { transition-duration: .01ms !important; transition-delay: 0s !important; animation-duration: .01ms !important; }
}
</style>

<script>
/* Ambil warna brand dari Tailwind (n-primary, n-charcoal, dll) agar CSS di atas selalu senada. */
(function () {
    var root = document.documentElement;
    function probe(cls, prop) {
        var plain = document.createElement('i'), el = document.createElement('i');
        el.className = cls;
        [plain, el].forEach(function (n) {
            n.style.cssText = 'position:absolute;visibility:hidden;pointer-events:none;border-style:solid;border-width:0';
            document.body.appendChild(n);
        });
        var v = getComputedStyle(el)[prop], base = getComputedStyle(plain)[prop];
        document.body.removeChild(plain); document.body.removeChild(el);
        return v && v !== base && v !== 'rgba(0, 0, 0, 0)' ? v : null;
    }
    function alpha(c, a) {
        var m = c.match(/\d+(\.\d+)?/g);
        return m && c.indexOf('rgb') === 0 ? 'rgba(' + m[0] + ',' + m[1] + ',' + m[2] + ',' + a + ')' : null;
    }
    var p = probe('bg-n-primary', 'backgroundColor');
    if (p) {
        root.style.setProperty('--nss-primary', p);
        var ring = alpha(p, .22), tint = alpha(p, .06);
        if (ring) root.style.setProperty('--nss-ring', ring);
        if (tint) root.style.setProperty('--nss-tint', tint);
    }
    var map = [['--nss-ink', 'text-n-charcoal', 'color'], ['--nss-muted', 'text-n-gray', 'color'],
            ['--nss-line', 'border-n-border', 'borderTopColor'], ['--nss-paper', 'bg-n-bg', 'backgroundColor']];
    map.forEach(function (m) { var v = probe(m[1], m[2]); if (v) root.style.setProperty(m[0], v); });
})();
</script>