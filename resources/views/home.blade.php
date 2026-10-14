@extends('layouts.app')

@section('title', 'Needy Study Space | Premium Workspace')

@section('content')

@php
    // Satu tempat, beberapa area di dalamnya: area hanya informasi, bukan pilihan harga
    $areas = collect($roomTypes)->values()->map(function ($room) {
        $f = $room->facilities ?? [];
        $list = is_array($f) ? $f : (json_decode($f, true) ?? []);
        return [
            'type'        => $room->type,
            'capacity'    => $room->capacity,
            'description' => $room->description,
            'image'       => $room->image,
            'facilities'  => $list,
        ];
    });

    $tarifs = [
        ['key' => 'mahasiswa', 'name' => 'Mahasiswa', 'price' => '35.000'],
        ['key' => 'umum',      'name' => 'Umum',      'price' => '55.000'],
    ];
@endphp

@include('partials.nss-style')

<style>
    /* ---- khusus halaman home ---- */
    .nss-sec { padding: 4rem 0; overflow: hidden; }
    .nss-sec--white { background: #fff; }
    .nss-sec--paper { background: var(--nss-paper); }
    @media (min-width: 768px) { .nss-sec { padding: 6rem 0; } }
    .nss-sec-head { max-width: 36rem; }
    .nss-sec-head p, .nss-split-head p { margin-top: .75rem; color: var(--nss-muted); }
    .nss-split-head p { max-width: 20rem; }

    .nss-split { display: grid; gap: 2.5rem; }
    @media (min-width: 1024px) { .nss-split { grid-template-columns: minmax(0, 4fr) minmax(0, 8fr); gap: 3.5rem; } }

    /* hero */
    .nss-hero { padding: 2.5rem 0 4rem; overflow: hidden; }
    .nss-hero-grid { display: grid; gap: 2rem; align-items: center; }
    .nss-hero-text p { margin-top: 1.25rem; max-width: 24rem; color: var(--nss-muted); }
    .nss-hero-text p.price { margin-top: 1rem; color: var(--nss-ink); }
    .nss-hero-cta { display: flex; flex-direction: column; align-items: stretch; gap: 1.25rem; margin-top: 2rem; }
    .nss-hero-cta .nss-link { align-self: flex-start; }
    
    .nss-hero-img { aspect-ratio: 4 / 3; overflow: hidden; border-radius: 10px; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .05); }
    .nss-hero-img img { width: 100%; height: 100%; object-fit: cover; }
    
    @media (min-width: 640px) {
        .nss-hero-cta { flex-direction: row; align-items: center; }
        .nss-hero-cta .nss-link { align-self: auto; }
    }
    @media (min-width: 768px) {
        .nss-hero { padding: 4rem 0 6rem; }
        .nss-hero-grid { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: 3rem; }
        .nss-hero-img { aspect-ratio: 5 / 4; }
    }
    /* Layout 2 Kolom Penuh Khusus Layar Laptop/PC */
    @media (min-width: 1024px) {
        .nss-hero { padding: 6rem 0 8rem; }
        .nss-hero-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); gap: 5rem; }
        .nss-hero-text p { max-width: 100%; font-size: 1.1rem; }
    }

    /* area */
    .nss-areas-grid { display: grid; gap: 2rem; margin-top: 2.5rem; }
    
    /* PERBAIKAN UTAMA: Mencegah teks dan layout melebar menabrak batas kanan layar */
    .nss-areas-info { min-width: 0; } 
    
    .nss-areas-info .nss-stack { margin-top: 1.5rem; }
    .nss-areas-info .desc { margin-top: .5rem; line-height: 1.65; color: #4b5a66; }
    .nss-areas-info h3.fac { margin-top: 1.5rem; font-size: .875rem; font-weight: 600; }
    
    /* PENGATURAN TAB (Sangat Elegan untuk Mobile) */
    .nss-tabs-wrap { position: relative; }
    @media (max-width: 1023px) {
        /* Efek bayangan memudar (fade) di ujung kanan layar */
        .nss-tabs-wrap::after {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; width: 45px;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%);
            pointer-events: none;
        }
    }
    
    .nss-areas .nss-tabs { 
        display: flex;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        gap: 1.5rem;
        border-bottom: 1px solid var(--nss-line);
        
        /* Menyembunyikan scrollbar agar terlihat seperti aplikasi native iOS/Android */
        -ms-overflow-style: none;  /* IE & Edge */
        scrollbar-width: none;  /* Firefox */
    }
    
    .nss-areas .nss-tabs::-webkit-scrollbar { 
        display: none; /* Chrome, Safari, Opera */
    }

    @media (min-width: 768px) { .nss-areas-grid { margin-top: 3.5rem; } }
    
    /* Pengaturan Tab khusus saat di Laptop (berubah jadi vertikal) */
    @media (min-width: 1024px) {
        .nss-areas-grid { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); gap: 3.5rem; }
        .nss-areas .nss-tabs-wrap { border-bottom: 0; border-left: 1px solid var(--nss-line); }
        .nss-areas .nss-tabs { 
            flex-direction: column; 
            gap: .25rem; 
            overflow: visible; 
            padding-bottom: 0; 
            border-bottom: 0;
        }
        .nss-areas .nss-tab { margin-left: -1px; padding: .5rem 0 .5rem 1.25rem; border-bottom: 0; border-left: 2px solid transparent; font-size: 1.5rem; text-align: left; }
        .nss-areas .nss-tab.is-active { border-left-color: var(--nss-primary); }
    }

    /* harga */
    .nss-tickets { display: grid; gap: 1.5rem; }
    @media (min-width: 640px) { .nss-tickets { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .nss-ticket-body { padding: 1.5rem; flex: 1; }
    .nss-ticket-price { display: flex; align-items: baseline; gap: .375rem; margin-top: 1.5rem; }
    .nss-ticket-price .big { font-size: clamp(2.5rem, 6vw, 3rem); font-weight: 700; line-height: 1.1; }
    .nss-ticket-price .rp { font-size: 1.25rem; font-weight: 600; }
    .nss-ticket-list { display: grid; gap: .375rem; margin-top: 1.25rem; font-size: .875rem; color: var(--nss-muted); }
    .nss-ticket-foot { padding: .75rem 1.5rem 1.5rem; }
    .nss-pass .nss-perf span { left: 1.5rem; right: 1.5rem; }

    /* cara memesan */
    .nss-how { display: grid; gap: 2rem; }
    .nss-how li { padding-top: 1rem; border-top: 2px solid var(--nss-line); }
    .nss-how .n { font-size: 1.5rem; font-weight: 700; color: var(--nss-primary); }
    .nss-how h3 { margin-top: .5rem; font-weight: 600; }
    .nss-how p { margin-top: .375rem; font-size: .875rem; line-height: 1.6; color: var(--nss-muted); }
    @media (min-width: 640px) { .nss-how { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

    /* snack */
    .nss-prod-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem 1rem; margin-top: 2.5rem; }
    .nss-prod-img { aspect-ratio: 1 / 1; overflow: hidden; border-radius: 6px; background: #e5eaea; }
    .nss-prod-img img { width: 100%; height: 100%; object-fit: cover; }
    .nss-prod-grid h3 { margin-top: .75rem; font-weight: 600; }
    @media (min-width: 768px) { .nss-prod-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 3rem; } }
    @media (min-width: 1024px) { .nss-prod-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); column-gap: 1.5rem; } }

    /* Micro-interactions */
    .reveal-up {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal-up.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    .nss-hero-img img, .nss-prod-img img, .nss-gal-img {
        transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .nss-hero-img:hover img, .nss-prod-grid li:hover .nss-prod-img img {
        transform: scale(1.05);
    }
    .nss-pass {
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .nss-pass:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px -10px rgba(0,0,0,0.08), 0 10px 15px -5px rgba(0,0,0,0.03);
    }
</style>

<div id="home" class="nss">

{{-- ========== HERO ========== --}}
<section class="nss-hero">
    <div class="nss-container nss-hero-grid">
        <div class="nss-hero-text reveal-up">
            <h1 class="nss-display nss-h1">Space to think.<br>Room to <span style="color: #DC143C">focus.</span></h1>

            <p>Lingkungan tenang dan profesional untuk belajar dan bekerja. Pesan dalam hitungan detik.</p>
            <p class="price">
                Mahasiswa <strong class="nss-bold nss-num">Rp35.000</strong>,
                umum <strong class="nss-bold nss-num">Rp55.000</strong>, per hari.
            </p>

            <div class="nss-hero-cta">
                <a href="{{ url('/booking') }}" class="nss-btn">Pesan sekarang</a>
                <a href="#spaces" class="nss-link">Lihat area</a>
            </div>
        </div>

        <div class="nss-hero-img reveal-up" style="transition-delay: 150ms;">
            <img src="{{ asset('images/background.png') }}" 
                 onerror="this.src='https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&q=85'"
                 alt="Needs Study Space">
        </div>
    </div>
</section>

{{-- ========== AREA DI DALAM ========== --}}
<section id="spaces" class="nss-sec nss-sec--white nss-areas"
         x-data="{
            area: 0,
            count: {{ $areas->count() }},
            touchX: 0,
            move(d, focusTab = false) {
                if (!this.count) return;
                this.area = (this.area + d + this.count) % this.count;
                if (focusTab) this.$nextTick(() => document.getElementById('home-area-' + this.area)?.focus());
            },
            swipe(x) { const dx = x - this.touchX; if (Math.abs(dx) > 45) this.move(dx < 0 ? 1 : -1); }
         }">
    <div class="nss-container">

        <div class="nss-sec-head reveal-up">
            <h2 class="nss-display nss-h2">Area di dalam Needs Study Space</h2>
            <p>Semua area ada dalam satu tempat dan berlaku dengan tarif yang sama. Saat memesan, yang dipilih hanya tarifnya.</p>
        </div>

        @if($areas->isNotEmpty())
        <div class="nss-areas-grid reveal-up" style="transition-delay: 150ms;">

            <div>
                <div class="nss-gal-frame"
                     @touchstart.passive="touchX = $event.changedTouches[0].clientX"
                     @touchend.passive="swipe($event.changedTouches[0].clientX)">
                    @foreach($areas as $a)
                    @php
                        // Menyesuaikan gambar area dengan folder images/area/ (area1.png, area2.png, dst)
                        $areaImageNum = $loop->index + 1;
                        $localAreaImage = asset('images/area/area' . $areaImageNum . '.png');
                    @endphp
                    <figure class="nss-gal-slide" :class="{ 'is-active': area === {{ $loop->index }} }"
                            :aria-hidden="area !== {{ $loop->index }}">
                        <div class="nss-gal-fallback nss-display">{{ $a['type'] }}</div>
                        <img class="nss-gal-img" src="{{ $localAreaImage }}" onerror="this.src='{{ $a['image'] }}'" alt="{{ $a['type'] }}">
                    </figure>
                    @endforeach
                </div>
            </div>

            <div class="nss-areas-info">
                @if($areas->count() > 1)
                <div class="nss-tabs-wrap">
                    <div role="tablist" aria-label="Area" class="nss-tabs nss-display"
                         @keydown.right.prevent="move(1, true)" @keydown.left.prevent="move(-1, true)"
                         @keydown.down.prevent="move(1, true)" @keydown.up.prevent="move(-1, true)">
                        @foreach($areas as $a)
                        <button type="button" role="tab" id="home-area-{{ $loop->index }}" class="nss-tab"
                                :class="{ 'is-active': area === {{ $loop->index }} }"
                                :aria-selected="area === {{ $loop->index }}"
                                :tabindex="area === {{ $loop->index }} ? 0 : -1"
                                @click="area = {{ $loop->index }}">{{ $a['type'] }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="nss-stack" aria-live="polite">
                    @foreach($areas as $a)
                    <div :class="{ 'is-active': area === {{ $loop->index }} }" :aria-hidden="area !== {{ $loop->index }}">
                        <p class="nss-small nss-muted">Maks. {{ $a['capacity'] }} orang</p>
                        <p class="desc">{{ $a['description'] }}</p>

                        @if(count($a['facilities']))
                        <h3 class="fac">Fasilitas</h3>
                        <ul class="nss-facilities">
                            @foreach($a['facilities'] as $facility)
                            <li>
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $facility }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- ========== HARGA ========== --}}
<section id="harga" class="nss-sec nss-sec--paper"
         x-data="{ seen: false }"
         x-init="const io = new IntersectionObserver(([e]) => { if (e.isIntersecting) { seen = true; io.disconnect() } }, { threshold: .35 }); io.observe($el)">
    <div class="nss-container nss-split">

        <div class="nss-split-head reveal-up">
            <h2 class="nss-display nss-h2">Harga</h2>
            <p>Tarif per hari, berlaku untuk semua area. Tidak ada biaya terpisah per ruangan.</p>
        </div>

        <div class="nss-tickets">
            @foreach($tarifs as $t)
            <div class="nss-pass reveal-up" style="transition-delay: {{ $loop->index * 150 }}ms;">
                <div class="nss-ticket-body">
                    <p class="nss-small nss-muted">Day pass</p>
                    <h3 class="nss-display" style="font-size:1.5rem;font-weight:700;line-height:1.2">{{ $t['name'] }}</h3>

                    <p class="nss-ticket-price">
                        <span class="nss-mark nss-display nss-num big" :class="{ 'is-on': seen }"><span class="rp">Rp</span>{{ $t['price'] }}</span>
                        <span class="nss-small nss-muted">/hari</span>
                    </p>

                    <ul class="nss-ticket-list">
                        <li>Akses ke semua area</li>
                        <li>Berlaku seharian penuh</li>
                    </ul>
                </div>

                <div class="nss-perf" aria-hidden="true"><span></span></div>

                <div class="nss-ticket-foot">
                    <a href="{{ url('/booking?tarif=' . $t['key']) }}" class="nss-btn nss-btn--block">Pesan tarif {{ strtolower($t['name']) }}</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== CARA MEMESAN ========== --}}
<section id="how" class="nss-sec nss-sec--white">
    <div class="nss-container nss-split">
        <div class="nss-split-head reveal-up">
            <h2 class="nss-display nss-h2">Cara memesan</h2>
            <p>Pemesanan mandiri, selesai dalam tiga langkah singkat.</p>
        </div>

        <ol class="nss-how">
            @foreach([
                ['Pilih tarif', 'Mahasiswa atau umum. Semua area bisa dipakai.'],
                ['Tentukan tanggal', 'Atur tanggal mulai dan jumlah hari.'],
                ['Konfirmasi', 'Bayar secara digital dan dapatkan akses instan.'],
            ] as $i => $step)
            <li class="reveal-up" style="transition-delay: {{ $i * 150 }}ms;">
                <span class="n nss-display">{{ $i + 1 }}</span>
                <h3>{{ $step[0] }}</h3>
                <p>{{ $step[1] }}</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ========== SNACK & MINUMAN ========== --}}
@if($products->count() > 0)
<section class="nss-sec nss-sec--paper">
    <div class="nss-container">
        <div class="nss-sec-head reveal-up">
            <h2 class="nss-display nss-h2">Snack dan minuman</h2>
            <p>Bisa ditambahkan saat memesan. Langsung diantar ke tempatmu.</p>
        </div>

        <ul class="nss-prod-grid">
            @foreach($products as $product)
            @php
                $productNameLower = strtolower($product->name);
                $localImage = '';
                
                if ($loop->index === 3 || str_contains($productNameLower, 'lays') || str_contains($productNameLower, 'chip')) {
                    $localImage = asset('images/produk/lays.png');
                } elseif (str_contains($productNameLower, 'butter') || str_contains($productNameLower, 'croissant')) {
                    $localImage = asset('images/produk/Butter.png');
                } elseif (str_contains($productNameLower, 'coffee') || str_contains($productNameLower, 'latte')) {
                    $localImage = asset('images/produk/coffelate.png');
                } elseif (str_contains($productNameLower, 'mineral') || str_contains($productNameLower, 'water') || str_contains($productNameLower, 'aqua')) {
                    $localImage = asset('images/produk/mineral.png');
                } else {
                    $localImage = $product->image ?: asset('images/produk/coffelate.png');
                }
            @endphp
            <li class="reveal-up" style="transition-delay: {{ $loop->index * 100 }}ms;">
                <div class="nss-prod-img">
                    <img src="{{ $localImage }}" alt="{{ $product->name }}" loading="lazy">
                </div>
                <h3>{{ $product->name }}</h3>
                <p class="nss-small nss-muted nss-num">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
            </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

</div>

{{-- SCRIPT UNTUK TRIGGER ANIMASI SCROLL REVEAL --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        });

        document.querySelectorAll('.reveal-up').forEach((el) => {
            observer.observe(el);
        });
    });
</script>

@endsection