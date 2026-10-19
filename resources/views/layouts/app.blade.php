<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Needs Study Space')</title>
    
    {{-- Google Fonts (Poppins untuk Headline, Inter untuk Body/Label) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- Styling Global & Animasi Halus (Micro-interactions) --}}
    <style>
        :root {
            --n-primary: #DC143C;     /* Merah Crimson */
            --n-secondary: #121212;   /* Hitam Pekat */
            --n-tertiary: #28A745;    /* Hijau */
            --n-neutral: #4A4A4A;     /* Abu-abu Netral */
            --n-bg: #F8F9FA;          /* Background Lembut */
            --n-border: #E5E7EB;      /* Garis Pembatas */
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--n-neutral);
            background-color: var(--n-bg);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6, .font-headline {
            font-family: 'Poppins', sans-serif;
            color: var(--n-secondary);
        }

        .text-n-primary { color: var(--n-primary) !important; }
        .bg-n-primary { background-color: var(--n-primary) !important; }
        .border-n-primary { border-color: var(--n-primary) !important; }

        .text-n-charcoal { color: var(--n-secondary) !important; }
        .bg-n-charcoal { background-color: var(--n-secondary) !important; }

        .text-n-gray { color: var(--n-neutral) !important; }
        .bg-n-bg { background-color: var(--n-bg) !important; }

        /* Animasi Sederhana & Elegan (Fade In & Slide Up) */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Transisi Universal untuk Kartu & Tombol */
        button, a, .card-interactive {
            transition: all 0.2s ease-in-out;
        }

        [x-cloak] { display: none !important; }

        html { scroll-behavior: smooth; scroll-padding-top: 5.25rem; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }


        /* ---- Navbar ---- */
    .nv { --nv-bg: #fff; --nv-border: #E5E7EB; --nv-text: #121212; --nv-muted: #4A4A4A; --nv-hover: #F3F4F6;
          position: sticky; top: 0; z-index: 50; padding: .625rem 0; background: var(--n-bg, #F8F9FA); }
    .nv-wrap { position: relative; max-width: 80rem; margin: 0 auto; padding: 0 1rem; }
    @media (min-width: 768px) { .nv-wrap { padding: 0 2.5rem; } }

    .nv-bar { display: flex; align-items: center; gap: .75rem; height: 3.5rem; padding: 0 .5rem 0 1rem;
              background: var(--nv-bg); border: 1px solid var(--nv-border); border-radius: 16px;
              box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 10px 24px -14px rgba(0,0,0,.15); }
    .nv-brand { margin-right: auto; font-family: 'Poppins', sans-serif; font-size: 1.0625rem; font-weight: 700; letter-spacing: -.01em; color: var(--nv-text); white-space: nowrap; }
    .nv-brand:hover { opacity: .85; }

    /* tautan */
    .nv-links { display: none; flex: 1; align-items: center; justify-content: center; gap: .25rem; margin: 0; padding: 0; list-style: none; }
    .nv-link { position: relative; display: block; padding: .5rem .875rem; border-radius: 10px; font-size: .875rem; font-weight: 500; color: var(--nv-muted); }
    .nv-link:hover { color: var(--n-primary, #DC143C); }
    .nv-link.is-active { color: var(--n-primary, #DC143C); font-weight: 600; }
    .nv-link:focus-visible, .nv-icon-btn:focus-visible, .nv-cta:focus-visible { outline: 2px solid var(--n-primary, #DC143C); outline-offset: 2px; }

    /* aksi */
    .nv-actions { display: flex; align-items: center; gap: .25rem; }
    .nv-icon-btn { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: 10px; color: var(--nv-muted); }
    .nv-icon-btn:hover { color: var(--nv-text); background: var(--nv-hover); }
    .nv-cta { display: none; align-items: center; justify-content: center; height: 2.5rem; padding: 0 1.125rem; border-radius: 10px; background: var(--n-primary, #DC143C); color: #fff; font-size: .875rem; font-weight: 600; white-space: nowrap; }
    .nv-cta:hover { background: #c01236; }
    .nv-cta:active { transform: scale(.97); }

    @media (min-width: 768px) {
        .nv-brand { margin-right: 0; }
        .nv-links { display: flex; }
        .nv-cta { display: inline-flex; }
        .nv-burger { display: none; }
    }

    /* menu mobile */
    .nv-menu { position: absolute; left: 1rem; right: 1rem; top: calc(100% + .125rem); padding: .5rem; background: var(--nv-bg); border: 1px solid var(--nv-border); border-radius: 16px; box-shadow: 0 16px 32px -16px rgba(0,0,0,.25); }
    .nv-menu .nv-link { padding: .75rem 1rem; font-size: .9375rem; }
    .nv-menu .nv-cta { display: flex; width: 100%; height: 2.75rem; margin-top: .5rem; }
    @media (min-width: 768px) { .nv-menu { display: none !important; } }

        /* ---- Footer ---- */
        .nf { margin-top: auto; width: 100%; background: #121212; color: #fff; border-top: 1px solid rgba(255,255,255,.1); }
        .nf-inner { max-width: 80rem; margin: 0 auto; padding: 2.5rem 1.25rem 1.5rem; }
        .nf-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-areas: "brand brand" "nav contact" "eco eco";
            gap: 2.25rem 1.5rem;
        }
        .nf-brand { grid-area: brand; }
        .nf-nav { grid-area: nav; }
        .nf-contact { grid-area: contact; }
        .nf-eco { grid-area: eco; }

        .nf-head { margin: 0 0 1rem; font-family: 'Poppins', sans-serif; font-size: .9375rem; font-weight: 600; color: #fff; }
        .nf-list { margin: 0; padding: 0; list-style: none; display: grid; gap: .625rem; }
        .nf-link, .nf-text { font-size: .875rem; font-weight: 300; line-height: 1.6; color: rgba(255,255,255,.7); }
        .nf-link:hover { color: #fff; }
        .nf-contact address { margin: 0; font-style: normal; display: grid; gap: .75rem; }
        .nf-slogan { margin: .875rem 0 0; max-width: 18rem; font-size: .875rem; font-weight: 300; line-height: 1.6; color: rgba(255,255,255,.7); }

        /* Logo berlatar terang -> ditaruh di kotak putih. .nf-crop memangkas ruang
           kosong di sekeliling gambar; rasio wrapper = rasio isi logo. */
        .nf-logo { display: block; width: fit-content; background: #fff; border-radius: 10px; padding: .5rem .75rem; }
        .nf-crop { position: relative; display: block; overflow: hidden; }
        .nf-crop img { position: absolute; max-width: none; }
        .nf-logo-nss .nf-crop { width: 9rem; aspect-ratio: 368 / 102; }
        .nf-logo-nss img { width: 121.5%; height: 438.2%; left: -12.8%; top: -164.7%; }
        .nf-logo-tl { flex: none; padding: .375rem .5rem; }
        .nf-logo-tl .nf-crop { width: 2.75rem; aspect-ratio: 301 / 259; }
        .nf-logo-tl img { width: 148.5%; height: 172.6%; left: -22.9%; top: -30.9%; }

        .nf-eco { display: flex; align-items: center; gap: .875rem; padding-top: 1.75rem; border-top: 1px solid rgba(255,255,255,.1); }
        .nf-eco small { display: block; font-size: .75rem; color: rgba(255,255,255,.5); }
        .nf-eco strong { display: inline-flex; align-items: center; gap: .25rem; margin-top: .125rem; font-size: .875rem; font-weight: 600; color: #fff; }
        .nf-eco:hover strong { text-decoration: underline; }

        .nf-bottom { display: flex; flex-direction: column; gap: .5rem; margin-top: 2.5rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,.1); font-size: .75rem; font-weight: 300; color: rgba(255,255,255,.5); }
        .nf-bottom a { color: rgba(255,255,255,.5); }
        .nf-bottom a:hover { color: #fff; }

        @media (min-width: 768px) {
            .nf-inner { padding: 4rem 2.5rem 2rem; }
            .nf-grid {
                grid-template-columns: minmax(0, 1.5fr) minmax(0, .8fr) minmax(0, 1.2fr);
                grid-template-areas: "brand nav contact" "eco nav contact";
                grid-template-rows: auto 1fr;
                column-gap: 4rem; row-gap: 2rem;
            }
            .nf-eco { align-self: end; padding-top: 0; border-top: 0; }
            .nf-bottom { flex-direction: row; justify-content: space-between; align-items: center; margin-top: 3rem; }
        }
    </style>
</head>
<body class="bg-n-bg text-n-charcoal font-sans antialiased min-h-screen flex flex-col overflow-x-hidden selection:bg-rose-100 selection:text-rose-700">

    {{-- Navbar: disembunyikan di wizard booking dan checkout, tetap tampil di /booking/check --}}
    @php
        $hideNav    = (request()->is('booking*') && !request()->is('booking/check*')) || request()->is('checkout*');
        $navIsHome  = request()->is('/');
        $navCurrent = $navIsHome ? 'home' : (request()->is('booking/check*') ? 'check' : '');
    @endphp
    @unless($hideNav)
    <header class="nv" x-data="nssNav('{{ $navCurrent }}', {{ $navIsHome ? 'true' : 'false' }})"
            @click.away="open = false" @keydown.escape.window="open = false">
        <div class="nv-wrap">
            <div class="nv-bar">
                <a href="{{ url('/') }}" class="nv-brand" @click="goHome($event)">Needs Study Space<span style="color: #DC143C;">.</span></a>
    
                <ul class="nv-links">
                    <li><a href="{{ url('/') }}" @click="goHome($event)" class="nv-link {{ $navCurrent === 'home' ? 'is-active' : '' }}"
                        :class="{ 'is-active': current === 'home' }" :aria-current="current === 'home' ? 'page' : false">Home</a></li>
                    <li><a href="{{ url('/#spaces') }}" @click="goArea($event)" class="nv-link"
                        :class="{ 'is-active': current === 'area' }" :aria-current="current === 'area' ? 'location' : false">Area</a></li>
                    <li><a href="{{ url('/booking/check') }}" class="nv-link {{ $navCurrent === 'check' ? 'is-active' : '' }}"
                        :class="{ 'is-active': current === 'check' }" :aria-current="current === 'check' ? 'page' : false">Cek Booking</a></li>
                </ul>
    
                <div class="nv-actions">
                    <a href="{{ url('/booking') }}" class="nv-cta">Booking Sekarang</a>
    
                    <button type="button" class="nv-icon-btn nv-burger" @click="open = !open" :aria-expanded="open" aria-controls="nv-menu" aria-label="Buka menu">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                            <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
    
            {{-- Menu mobile --}}
            <div id="nv-menu" class="nv-menu" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                <a href="{{ url('/') }}" @click="goHome($event)" class="nv-link {{ $navCurrent === 'home' ? 'is-active' : '' }}"
                :class="{ 'is-active': current === 'home' }">Home</a>
                <a href="{{ url('/#spaces') }}" @click="goArea($event)" class="nv-link"
                :class="{ 'is-active': current === 'area' }">Area</a>
                <a href="{{ url('/booking/check') }}" class="nv-link {{ $navCurrent === 'check' ? 'is-active' : '' }}"
                :class="{ 'is-active': current === 'check' }">Cek Booking</a>
                <a href="{{ url('/booking') }}" class="nv-cta">Booking Sekarang</a>
            </div>
        </div>
    </header>
    @endunless

    {{-- Main Content dengan Animasi Masuk Halus --}}
    <main class="flex-grow flex flex-col animate-fade-in">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="nf">
        <div class="nf-inner">
            <div class="nf-grid">

                {{-- Brand --}}
                <div class="nf-brand">
                    <a href="{{ url('/') }}" class="nf-logo nf-logo-nss" aria-label="Needs Study Space, ke beranda">
                        <span class="nf-crop"><img src="{{ asset('images/logo-nss.png') }}" alt="Needs Study Space" loading="lazy"></span>
                    </a>
                    <p class="nf-slogan">Ruang tenang untuk pikiran yang tajam.</p>
                </div>

                {{-- Navigasi --}}
                <nav class="nf-nav" aria-label="Navigasi footer">
                    <h4 class="nf-head">Navigasi</h4>
                    <ul class="nf-list">
                        <li><a href="{{ url('/') }}" class="nf-link">Beranda</a></li>
                        <li><a href="{{ url('/#spaces') }}" class="nf-link">Area</a></li>
                        <li><a href="{{ url('/booking') }}" class="nf-link">Booking</a></li>
                        <li><a href="{{ url('/booking/check') }}" class="nf-link">Cek booking</a></li>
                    </ul>
                </nav>

                {{-- Kontak --}}
                <div class="nf-contact">
                    <h4 class="nf-head">Kontak &amp; Lokasi</h4>
                    <address>
                        <span class="nf-text">Jl. Ir. Soekarno No.4, RW.6, Cibeusi, Kec. Jatinangor, Kabupaten Sumedang, Jawa Barat 45363</span>
                        <a href="mailto:hello@technolife.co.id" class="nf-link" style="word-break: break-all;">hello@technolife.co.id</a>
                    </address>
                </div>

                {{-- Induk perusahaan --}}
                <a href="https://technolife.co.id" target="_blank" rel="noopener noreferrer" class="nf-eco">
                    <span class="nf-logo nf-logo-tl">
                        <span class="nf-crop"><img src="{{ asset('images/logo-technolife.png') }}" alt="Techno Life" loading="lazy"></span>
                    </span>
                    <span>
                        <small>Bagian dari ekosistem</small>
                        <strong>
                            PT Techno Life Indonesia
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="color: #DC143C;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </strong>
                    </span>
                </a>

            </div>

            <div class="nf-bottom">
                <span>&copy; {{ date('Y') }} Needs Study Space &bull; PT Techno Life Indonesia. All rights reserved.</span>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}">Admin Portal</a>
                @endif
            </div>
        </div>
    </footer>

    <script>
        /* Penanda menu aktif: di beranda, "Area" menyala saat bagian #spaces sedang terlihat. */
        function nssNav(initial, isHome) {
            const behavior = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    
            return {
                open: false,
                current: initial,
                locked: false,
    
                init() {
                    if (!isHome) return;
                    let ticking = false;
                    const run = () => { ticking = false; this.sync(); };
                    const queue = () => { if (!ticking) { ticking = true; requestAnimationFrame(run); } };
                    window.addEventListener('scroll', queue, { passive: true });
                    window.addEventListener('resize', queue);
                    window.addEventListener('load', queue);
                    window.addEventListener('hashchange', queue);
                    this.sync();
                },
    
                /* tentukan menu aktif dari posisi gulir */
                sync() {
                    if (!isHome || this.locked) return;
                    const area = document.getElementById('spaces');
                    if (!area) { this.current = 'home'; return; }
                    const r = area.getBoundingClientRect();
                    const line = window.innerHeight * 0.3;
                    this.current = (r.top <= line && r.bottom > line) ? 'area' : 'home';
                },
    
                /* kunci sebentar agar tidak berkedip saat animasi gulir berjalan */
                lock(name) {
                    this.current = name;
                    this.locked = true;
                    setTimeout(() => { this.locked = false; this.sync(); }, 800);
                },
    
                goHome(e) {
                    this.open = false;
                    if (!isHome) return;
                    e.preventDefault();
                    this.lock('home');
                    history.replaceState(null, '', location.pathname + location.search);
                    window.scrollTo({ top: 0, behavior: behavior() });
                },
    
                goArea(e) {
                    this.open = false;
                    const area = document.getElementById('spaces');
                    if (!isHome || !area) return;
                    e.preventDefault();
                    this.lock('area');
                    history.replaceState(null, '', '#spaces');
                    area.scrollIntoView({ behavior: behavior(), block: 'start' });
                }
            };
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>