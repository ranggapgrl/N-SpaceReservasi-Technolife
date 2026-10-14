@extends('layouts.app')

@section('title', 'Book a Space | Needs Study Space')

@section('content')

@php
    $catalog = $products->mapWithKeys(function ($p) {
        return [$p->id => ['name' => $p->name, 'price' => (int) $p->price]];
    });
@endphp

@include('partials.nss-style')

<style>
    {{-- CSS khusus halaman booking. CSS global ada di partials/nss-style.blade.php --}}

    /* overflow:hidden mencegah scrollbar browser muncul akibat footer di bawah wizard.
       Scroll hanya boleh terjadi di dalam .nss-bk-main. height:100% agar body setinggi viewport. */
    html, body { overflow: hidden; height: 100%; }

    /* Wizard container: tinggi penuh layar, flex kolom (header atas, konten bawah).
       dvh = dynamic viewport height, memperhitungkan keyboard virtual di HP. */
    .nss-bk { --nss-offset: 0px; height: 100vh; height: calc(100dvh - var(--nss-offset)); display: flex; flex-direction: column; background: var(--nss-paper); }

    /* Header sticky: tetap terlihat saat konten discroll */
    .nss-bk-head { position: sticky; top: 0; z-index: 30; background: var(--nss-paper); }
    .nss-bk-top { display: flex; align-items: center; height: 3rem; }
    .nss-back { display: inline-flex; align-items: center; gap: .375rem; margin-left: -.5rem; padding: .5rem; border-radius: 8px; font-size: .875rem; font-weight: 600; transition: background .15s, transform .15s; }
    .nss-back:hover { background: #fff; }
    .nss-back:active { transform: scale(.95); }

    /* Progress bar langkah-langkah */
    .nss-steps { display: flex; gap: .375rem; padding-bottom: .625rem; }
    .nss-step { flex: 1; min-width: 0; } /* flex:1 = lebar sama rata */
    .nss-step-btn { display: block; width: 100%; text-align: left; }
    .nss-step-btn:disabled { cursor: default; }
    .nss-step-bar { display: block; height: 3px; border-radius: 99px; background: var(--nss-line); overflow: hidden; }
    .nss-step-fill { display: block; height: 100%; background: var(--nss-primary); transition: width .5s ease-out; }
    .nss-step-label { display: none; margin-top: .375rem; font-size: .75rem; color: var(--nss-muted); }
    .nss-step-label.is-current { font-weight: 600; color: var(--nss-ink); }
    @media (min-width: 640px) { .nss-step-label { display: block; } }

    /* Area konten scroll. min-height:0 WAJIB agar overflow-y:auto bekerja di dalam flexbox
       (default min-height:auto membuat elemen meluber keluar dari container). */
    .nss-bk-main { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; padding-top: .5rem; padding-bottom: 0; }

    /* min-height:100% = bar aksi sticky-bottom selalu ada di bawah meski form pendek */
    .nss-form-wrapper { display: flex; flex-direction: column; flex: 1; min-height: 100%; }

    /* Grid 1 kolom di mobile, 2 kolom di desktop */
    .nss-bk-grid { flex: 1; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-items: start; padding-bottom: 1rem; }
    .nss-bk-form-inner { order: 1; min-width: 0; display: flex; flex-direction: column; }

    /* Kartu ringkasan: tersembunyi di mobile, tampil saat Alpine.js tambah class is-open */
    .nss-bk-aside { order: 2; display: none; width: 100%; max-width: 24rem; margin: 0 auto; }
    .nss-bk-aside.is-open { display: block; }

    /* clamp(min, preferred, max) = ukuran font responsif */
    .nss-title { font-size: clamp(1.125rem, 3vw, 1.375rem); line-height: 1.2; font-weight: 700; letter-spacing: -.015em; }
    .nss-lead { margin-top: .125rem; max-width: 30rem; font-size: .8125rem; line-height: 1.4; color: var(--nss-muted); }
    .nss-panel { outline: none; padding-bottom: .5rem; }
    .nss-step-body { margin-top: .5rem; }
    .nss-alert { margin-bottom: .75rem; padding: .625rem .75rem; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: .875rem; }

    /* Input form */
    .nss-bk .nss-label { font-size: .8125rem; margin-bottom: .375rem; display: block; font-weight: 500; }
    .nss-bk .nss-field { width: 100%; box-sizing: border-box; padding: 0 0.875rem; border: 1px solid var(--nss-line); background: #fff; height: 2.75rem; font-size: 1rem; border-radius: 8px; transition: border-color .15s, box-shadow .15s; }
    .nss-bk .nss-field:focus { border-color: var(--nss-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--nss-primary) 18%, transparent); outline: none; }
    .nss-bk input[type="date"].nss-field { max-width: 14rem; }
    .nss-bk .nss-btn { height: 3rem; padding: 0 1.5rem; font-size: .875rem; min-width: 8rem; border-radius: 8px; }

    /* Kartu pilihan tarif: gap dikecilkan agar tombol Lanjut tidak terpotong */
    .nss-tarifs { display: grid; gap: .4rem; }
    .nss-tarif { display: flex; flex-wrap: wrap; align-items: center; gap: .2rem .5rem; padding: .625rem .875rem; background: #fff; border: 2px solid transparent; border-radius: 10px; cursor: pointer; transition: border-color .2s; }
    label:hover > .nss-tarif { border-color: var(--nss-line); }
    input:checked + .nss-tarif { border-color: var(--nss-primary); }
    input:focus-visible + .nss-tarif { outline: 2px solid var(--nss-primary); outline-offset: 2px; }

    /* Radio kustom: box-shadow inset meniru tampilan radio button lingkaran */
    .nss-dot { flex: none; width: 18px; height: 18px; border-radius: 50%; color: var(--nss-primary); box-shadow: inset 0 0 0 2px #c3cece; transition: box-shadow .2s ease-out; }
    input:checked + .nss-tarif .nss-dot { box-shadow: inset 0 0 0 6px currentColor; } /* terisi penuh saat dipilih */
    .nss-tarif-name { flex: 1 1 auto; font-size: .9375rem; font-weight: 600; }
    .nss-tarif-price { margin-left: auto; font-size: clamp(1.125rem, 4vw, 1.5rem); font-weight: 700; line-height: 1.1; white-space: nowrap; }
    .nss-tarif-price .rp  { font-size: .8125rem; font-weight: 600; }
    .nss-tarif-price .per { margin-left: .2rem; font-size: .75rem; font-weight: 500; color: var(--nss-muted); }

    /* Galeri: max-height membatasi tinggi agar tombol Lanjut selalu terlihat.
       min-height:160px mencegah galeri terlalu kecil di HP sempit. */
    .nss-areas-head { margin-top: .625rem; font-size: .9375rem; font-weight: 600; }
    .nss-gal-frame { margin-top: .375rem; min-height: 160px; max-height: 200px; aspect-ratio: 16 / 7; }
    @media (min-width: 640px)  { .nss-gal-frame { max-height: 240px; aspect-ratio: 16 / 6; } }
    @media (min-width: 1024px) { .nss-gal-frame { max-height: 280px; aspect-ratio: 16 / 6; } }
    .nss-tabs-wrap { margin-top: .375rem; }

    /* Tanggal & durasi */
    .nss-grid2 { display: grid; gap: .875rem; max-width: 34rem; }
    @media (min-width: 640px) { .nss-grid2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .nss-stepper { display: flex; max-width: 12rem; height: 2.75rem; border: 1px solid var(--nss-line); border-radius: 8px; background: #fff; overflow: hidden; }
    .nss-stepper:focus-within { border-color: var(--nss-primary); }
    .nss-stepper > button { display: grid; place-items: center; width: 2.75rem; transition: background .15s; }
    .nss-stepper > button:hover:not(:disabled) { background: var(--nss-paper); }
    .nss-stepper > button:disabled { opacity: .3; cursor: not-allowed; }
    .nss-stepper-val { flex: 1; display: flex; align-items: center; justify-content: center; gap: .375rem; border-left: 1px solid var(--nss-line); border-right: 1px solid var(--nss-line); }
    .nss-stepper-val input { width: 2rem; padding: 0; border: 0; background: none; text-align: center; font: inherit; font-weight: 700; color: inherit; outline: none; }
    .nss-note { margin-top: .5rem; font-size: .875rem; color: var(--nss-muted); }

    /* Daftar snack */
    .nss-list { max-width: 36rem; border-top: 1px solid var(--nss-line); border-bottom: 1px solid var(--nss-line); }
    .nss-list > li { display: flex; align-items: center; gap: .75rem; padding: .625rem 0; }
    .nss-list > li + li { border-top: 1px solid var(--nss-line); }
    .nss-thumb { flex: none; width: 3.5rem; height: 3.5rem; overflow: hidden; border-radius: 8px; background: #e5eaea; }
    .nss-thumb img { width: 100%; height: 100%; object-fit: cover; } /* cover = gambar mengisi kotak tanpa distorsi */
    .nss-item { flex: 1; min-width: 0; }
    .nss-item h3 { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .9375rem; font-weight: 600; }
    .nss-ctl { flex: none; display: flex; justify-content: flex-end; width: 6.5rem; }
    .nss-add { height: 2.125rem; padding: 0 1rem; border: 1px solid var(--nss-primary); border-radius: 6px; color: var(--nss-primary); font-size: .875rem; font-weight: 600; transition: background .15s, color .15s; }
    .nss-add:hover { background: var(--nss-primary); color: #fff; }
    .nss-qty { display: flex; height: 2.125rem; border: 1px solid var(--nss-primary); border-radius: 6px; overflow: hidden; }
    .nss-qty button { display: grid; place-items: center; width: 2.125rem; }
    .nss-qty .minus { color: var(--nss-primary); }
    .nss-qty .plus  { background: var(--nss-primary); color: #fff; }
    .nss-qty-val { display: grid; place-items: center; width: 2rem; background: #fff; font-size: .875rem; font-weight: 700; }

    /* Form data pemesan */
    .nss-fields { display: grid; gap: 1rem; max-width: 26rem; }

    /* Bar aksi sticky di bawah area scroll. env(safe-area-inset-bottom) = ruang notch iPhone. */
    .nss-bar { position: sticky; bottom: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: auto -1rem 0; padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom, 0px)); background: var(--nss-paper); border-top: 1px solid var(--nss-line); box-shadow: 0 -6px 16px -10px rgba(15, 30, 40, .18); }
    .nss-bar-info  { min-width: 0; margin-right: auto; }
    .nss-bar-total { font-size: 1.125rem; font-weight: 700; line-height: 1.2; color: var(--nss-primary); font-variant-numeric: tabular-nums; }

    /* Kartu tiket ringkasan pesanan */
    .nss-pass { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid var(--nss-line); overflow: hidden; position: relative; }
    .nss-pass-body  { padding: 1.5rem; }
    .nss-pass-title { font-size: .875rem; color: var(--nss-muted); margin-bottom: 0.25rem; }
    .nss-pass-name  { font-size: 1.75rem; font-weight: 800; color: var(--nss-charcoal); margin-bottom: 1.5rem; letter-spacing: -0.03em; }
    .nss-rows { display: grid; gap: 1rem; font-size: 0.9375rem; }
    .nss-row  { display: flex; justify-content: space-between; align-items: center; }
    .nss-row dt { color: var(--nss-muted); font-weight: 400; }
    .nss-row dd { font-weight: 600; color: var(--nss-charcoal); }
    .nss-divider { height: 1px; background: var(--nss-line); margin: 1.25rem 0; }

    /* Efek perforasi tiket (garis putus + lubang lingkaran kiri-kanan) */
    .nss-perf { position: relative; height: 1px; margin: 1.25rem 0; }
    .nss-perf span { position: absolute; top: 0; left: 1rem; right: 1rem; border-top: 2px dashed var(--nss-line); }
    .nss-perf::before, .nss-perf::after { content: ""; position: absolute; top: -10px; width: 20px; height: 20px; background: var(--nss-paper); border-radius: 50%; box-shadow: inset 1px 0 0 var(--nss-line); }
    .nss-perf::before { left: -11px; box-shadow: inset -1px 0 0 var(--nss-line); }
    .nss-perf::after  { right: -11px; box-shadow: inset 1px 0 0 var(--nss-line); }
    .nss-pass-foot  { padding: 0.5rem 1.5rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    .nss-total-label { font-size: 0.9375rem; color: var(--nss-muted); font-weight: 400; }
    .nss-total-price { font-size: 2rem; font-weight: 800; color: var(--nss-primary); letter-spacing: -0.02em; }

    /* HP sangat kecil (<380px) */
    @media (max-width: 380px) { .nss-bk .nss-btn { min-width: 0; padding: 0 1.125rem; } .nss-tarif { padding: .75rem; } }

    /* Tablet (>=640px) */
    @media (min-width: 640px) {
        .nss-bk-main    { padding-top: .75rem; }
        .nss-bk-form-inner { width: 100%; max-width: 40rem; margin-inline: auto; }
        .nss-bar        { margin-inline: 0; padding-inline: 0; box-shadow: none; }
        .nss-panel      { padding-bottom: .75rem; }
        .nss-tarif      { padding: .75rem 1rem; }
        .nss-bk-aside   { max-width: 22rem; }
    }

    /* Desktop (>=1024px): 2 kolom, aside sticky, bar tidak sticky lagi */
    @media (min-width: 1024px) {
        .nss-bk-main  { padding-top: 1rem; padding-bottom: 1rem; }
        .nss-bk-grid  { grid-template-columns: minmax(0, 1fr) 22rem; gap: 2rem; max-width: 66rem; margin-inline: auto; width: 100%; }
        .nss-bk-aside { order: 2; display: block !important; position: sticky; top: 1rem; max-width: none; margin: 0; }
        .nss-bk-form-inner { order: 1; max-width: none; margin: 0; }
        .nss-bar      { position: static; margin-top: 1rem; padding: .875rem 0 0; box-shadow: none; justify-content: flex-end; }
        .nss-bar-totalbox { display: none; }
    }

    /* Matikan animasi jika pengguna aktifkan Reduce Motion di sistem */
    @media (prefers-reduced-motion: reduce) {
        .nss-bk *, .nss-bk *::before, .nss-bk *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
    }

    /* ── Upload KTM ─────────────────────────────────────────────────────── */
    /* Kotak drop-zone: position:relative agar input file bisa dioverlay di atasnya */
    .nss-upload-box { margin-top: .5rem; border: 2px dashed var(--nss-line); border-radius: 10px; padding: 1.25rem 1rem; text-align: center; background: #f8fafa; transition: border-color .2s, background .2s; cursor: pointer; position: relative; }
    .nss-upload-box:hover    { border-color: var(--nss-primary); background: #f0f7f7; }
    .nss-upload-box.has-file { border-color: var(--nss-primary); background: #eef7f5; }

    /* Input file asli disembunyikan (opacity:0). Klik kotak = klik input karena inset:0 */
    .nss-upload-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .nss-upload-icon  { font-size: 2rem; margin-bottom: .375rem; }
    .nss-upload-label { font-size: .875rem; font-weight: 600; color: var(--nss-ink); }
    .nss-upload-hint  { font-size: .75rem; color: var(--nss-muted); margin-top: .25rem; }

    /* Preview nama file: text-overflow:ellipsis potong nama panjang dengan "..." */
    .nss-file-preview { display: flex; align-items: center; gap: .5rem; margin-top: .625rem; padding: .5rem .75rem; background: #fff; border: 1px solid var(--nss-line); border-radius: 8px; font-size: .8125rem; }
    .nss-file-preview-name  { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }
    .nss-file-preview-clear { flex: none; color: var(--nss-muted); cursor: pointer; }
    .nss-file-preview-clear:hover { color: #b91c1c; }

    /* Badge info KTM: kotak biru muda pemberitahuan wajib upload */
    .nss-ktm-info { display: flex; align-items: flex-start; gap: .5rem; padding: .625rem .875rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: .8125rem; color: #1e40af; margin-top: .75rem; }
    .nss-ktm-info svg { flex: none; margin-top: 1px; }
</style>

<div id="booking-wizard" x-data="bookingWizard()" class="nss nss-bk">

    <!-- Header -->
    <header class="nss-bk-head">
        <div class="nss-wrap nss-bk-top">
            <button type="button" class="nss-back" @click="back()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
        </div>
        <ol class="nss-wrap nss-steps">
            <template x-for="(label, i) in labels" :key="label">
                <li class="nss-step">
                    <button type="button" class="nss-step-btn" :disabled="i + 1 >= step" @click="goTo(i + 1)"
                            :aria-label="label" :aria-current="step === i + 1 ? 'step' : false">
                        <span class="nss-step-bar"><span class="nss-step-fill" :style="`width:${step > i ? 100 : 0}%`"></span></span>
                        <span class="nss-step-label" :class="{ 'is-current': step === i + 1 }" x-text="label"></span>
                    </button>
                </li>
            </template>
        </ol>
    </header>

    <main class="nss-wrap nss-bk-main">
        {{--
            enctype="multipart/form-data" WAJIB ada agar browser bisa mengirim file (KTM).
            Tanpa atribut ini, file tidak akan terkirim ke server meskipun field sudah diisi.
        --}}
        <form action="{{ route('booking.store') }}" method="POST" id="bookingForm"
            enctype="multipart/form-data"
            @submit="isSubmitting = true" class="nss-form-wrapper">
            @csrf

            <div class="nss-bk-grid">
                
                <!-- KONTEN FORM -->
                <div class="nss-bk-form-inner">
                    @if(session('error'))
                    <div role="alert" class="nss-alert">{{ session('error') }}</div>
                    @endif

                    <div x-ref="panel" tabindex="-1" class="nss-panel">
                        <h1 class="nss-display nss-title" x-text="stepTitle()"></h1>
                        <p class="nss-lead" x-text="stepIntro()"></p>

                        <div class="nss-step-body">

                            <!-- ============ LANGKAH 1: TARIF ============ -->
                            <div x-show="step === 1"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">

                                <input type="hidden" name="room_type" value="{{ optional($roomTypes->first())->type }}">

                                <fieldset style="border:0;margin:0;padding:0;min-width:0">
                                    <legend class="nss-radio">Pilih tarif</legend>
                                    <div class="nss-tarifs">
                                        <label>
                                            <input type="radio" name="visitor_category" value="mahasiswa" x-model="form.category" class="nss-radio">
                                            <div class="nss-tarif">
                                                <span class="nss-dot"></span>
                                                <span class="nss-tarif-name">Mahasiswa</span>
                                                <span class="nss-display nss-num nss-tarif-price"><span class="nss-mark"><span class="rp">Rp</span>35.000</span><span class="per">/hari</span></span>
                                            </div>
                                        </label>
                                        <label>
                                            <input type="radio" name="visitor_category" value="umum" x-model="form.category" class="nss-radio">
                                            <div class="nss-tarif">
                                                <span class="nss-dot"></span>
                                                <span class="nss-tarif-name">Umum</span>
                                                <span class="nss-display nss-num nss-tarif-price"><span class="nss-mark"><span class="rp">Rp</span>55.000</span><span class="per">/hari</span></span>
                                            </div>
                                        </label>
                                    </div>
                                </fieldset>

                                @if($roomTypes->isNotEmpty())
                                <section aria-labelledby="areas-title">
                                    <h2 id="areas-title" class="nss-display nss-areas-head">Area di dalam Needs Study Space</h2>

                                    {{-- Galeri 12 foto area, slideshow otomatis 3 detik --}}
                                    <div class="nss-gal-frame"
                                         @touchstart.passive="touchX=$event.changedTouches[0].clientX"
                                         @touchend.passive="swipe($event.changedTouches[0].clientX)">

                                        {{-- 12 slide foto nyata --}}
                                        @for($i = 1; $i <= 12; $i++)
                                        <figure class="nss-gal-slide" :class="{ 'is-active': area === {{ $i-1 }} }" :aria-hidden="area !== {{ $i-1 }}">
                                            <div class="nss-gal-fallback nss-display">N-Space</div>
                                            <img class="nss-gal-img" src="{{ asset('images/area/' . $i . '.jpg') }}" alt="Area N-Space {{ $i }}" loading="{{ $i <= 2 ? 'eager' : 'lazy' }}">
                                        </figure>
                                        @endfor

                                        {{-- Dot navigasi klik untuk pindah foto --}}
                                        <div style="position:absolute;bottom:.5rem;right:.75rem;display:flex;gap:.35rem;z-index:5">
                                            @for($i = 1; $i <= 12; $i++)
                                            <button type="button" @click="galJump({{ $i-1 }})" :style="area==={{ $i-1 }} ? 'background:#fff;transform:scale(1.5)' : 'background:rgba(255,255,255,.5)'" style="width:6px;height:6px;border-radius:50%;border:0;padding:0;cursor:pointer;transition:all .2s" :aria-label="'Foto {{ $i }}'"></button>
                                            @endfor
                                        </div>
                                    </div>

                                    @if($roomTypes->count() > 1)
                                    <div class="nss-tabs-wrap">
                                        <div role="tablist" aria-label="Area" class="nss-tabs"
                                            @keydown.right.prevent="moveArea(1, true)" @keydown.left.prevent="moveArea(-1, true)">
                                            @foreach($roomTypes as $room)
                                            <button type="button" role="tab" id="area-tab-{{ $loop->index }}" class="nss-tab"
                                                    :class="{ 'is-active': area === {{ $loop->index }} }"
                                                    :aria-selected="area === {{ $loop->index }}"
                                                    :tabindex="area === {{ $loop->index }} ? 0 : -1"
                                                    @click="area = {{ $loop->index }}">{{ $room->type }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <div class="nss-stack" aria-live="polite">
                                        @foreach($roomTypes as $room)
                                        <div :class="{ 'is-active': area === {{ $loop->index }} }" :aria-hidden="area !== {{ $loop->index }}">
                                            <p class="nss-small nss-muted">Maks. {{ $room->capacity }} orang</p>
                                            <p style="margin-top:.25rem;max-width:40rem;color:#4b5a66">{{ $room->description ?? 'Ruang kerja tenang dan nyaman.' }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </section>
                                @endif
                            </div>

                            <!-- ============ LANGKAH 2: TANGGAL & DURASI ============ -->
                            <div x-show="step === 2" x-cloak class="nss-grid2"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">
                                <div>
                                    <label for="date" class="nss-label">Tanggal mulai</label>
                                    <input type="date" id="date" name="date" x-model="form.date" min="{{ date('Y-m-d') }}" class="nss-field">
                                </div>

                                <div>
                                    <span class="nss-label">Durasi</span>
                                    <div class="nss-stepper">
                                        <button type="button" @click="changeDuration(-1)" :disabled="form.duration <= 1" aria-label="Kurangi satu hari">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.5" d="M5 12h14"/></svg>
                                        </button>
                                        <div class="nss-stepper-val">
                                            <input type="text" inputmode="numeric" name="duration" :value="form.duration" readonly aria-label="Jumlah hari" class="nss-num">
                                            <span class="nss-small nss-muted">hari</span>
                                        </div>
                                        <button type="button" @click="changeDuration(1)" :disabled="form.duration >= 30" aria-label="Tambah satu hari">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                    <p class="nss-note" x-show="form.date && form.duration > 1" x-transition.opacity>
                                        Berlaku sampai <span x-text="formatDate(endDate())"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- ============ LANGKAH 3: SNACK & MINUMAN ============ -->
                            <div x-show="step === 3" x-cloak
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">
                                <ul class="nss-list">
                                    @forelse($products as $product)
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
                                    <li>
                                        <div class="nss-thumb">
                                            <img src="{{ $localImage }}" alt="{{ $product->name }}" loading="lazy">
                                        </div>
                                        <div class="nss-item">
                                            <h3>{{ $product->name }}</h3>
                                            <p class="nss-small nss-muted nss-num">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                                        </div>

                                        <input type="hidden" name="products[{{ $product->id }}]" :value="getQty({{ $product->id }})">

                                        <div class="nss-ctl">
                                            <button type="button" class="nss-add" x-show="getQty({{ $product->id }}) === 0"
                                                    x-transition.opacity.duration.150ms
                                                    @click="updateProduct({{ $product->id }}, 1)">Tambah</button>
                                            <div class="nss-qty" x-show="getQty({{ $product->id }}) > 0" x-cloak
                                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">
                                                <button type="button" class="minus" @click="updateProduct({{ $product->id }}, -1)" aria-label="Kurangi {{ $product->name }}">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14"/></svg>
                                                </button>
                                                <span class="nss-qty-val nss-num" x-text="getQty({{ $product->id }})"></span>
                                                <button type="button" class="plus" @click="updateProduct({{ $product->id }}, 1)" aria-label="Tambah {{ $product->name }}">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    @empty
                                    <li class="nss-muted">Belum ada snack atau minuman yang tersedia.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <!-- ============ LANGKAH 4: DATA PEMESAN ============ -->
                            <div x-show="step === 4" x-cloak class="nss-fields"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">
                                <div>
                                    <label for="customer_name" class="nss-label">Nama lengkap</label>
                                    <input type="text" id="customer_name" name="customer_name" x-model="form.customer_name" required
                                        autocomplete="name" class="nss-field" placeholder="Nama sesuai identitas">
                                </div>
                                    <div>
                                    <label for="customer_phone" class="nss-label">Nomor WhatsApp</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" x-model="form.customer_phone" required
                                        autocomplete="tel" inputmode="tel"
                                        pattern="^(\+62|62|0)8[0-9]{8,12}$" title="Contoh: 081234567890"
                                        class="nss-field" placeholder="08xxxxxxxxxx">
                                    <p class="nss-note">Gunakan nomor WhatsApp yang aktif.</p>
                                </div>

                                {{--
                                    ═══ UPLOAD KTM (KHUSUS MAHASISWA) ═══
                                    
                                    x-show="form.category === 'mahasiswa'" = field ini HANYA tampil
                                    ketika pengunjung memilih kategori "Mahasiswa" di Langkah 1.
                                    Pengunjung "Umum" tidak perlu melihat field ini.
                                    
                                    x-data pada elemen ini membuat sub-state Alpine.js lokal
                                    untuk mengelola nama file yang dipilih (ktmFileName).
                                --}}
                                <div x-show="form.category === 'mahasiswa'" x-cloak
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to"
                                    x-data="{ ktmFileName: '' }">

                                    {{-- Label dengan tanda * (wajib) --}}
                                    <p class="nss-label">
                                        Upload KTM <span style="color:#b91c1c" aria-label="wajib">*</span>
                                    </p>

                                    {{--
                                        Kotak upload interaktif.
                                        :class="{ 'has-file': ktmFileName }" menambahkan class CSS
                                        'has-file' jika sudah ada file dipilih (mengubah tampilan kotak).
                                    --}}
                                    <div class="nss-upload-box" :class="{ 'has-file': ktmFileName }">
                                        {{--
                                            Input file disembunyikan secara visual (opacity:0) tapi
                                            tetap fungsional. Klik pada kotak di atasnya akan memicu
                                            dialog pilih file dari OS.
                                            
                                            @change: saat file dipilih, simpan nama filenya ke ktmFileName
                                            untuk ditampilkan sebagai preview.
                                            
                                            x-show="form.category === 'mahasiswa'" juga diterapkan di
                                            attribute 'required' agar validasi HTML5 tidak aktif untuk umum.
                                        --}}
                                        <input type="file"
                                            name="ktm_file"
                                            id="ktm_file"
                                            class="nss-upload-input"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            :required="form.category === 'mahasiswa'"
                                            @change="ktmFileName = $event.target.files[0]?.name || ''"
                                            aria-label="Upload file KTM">

                                        {{-- Ikon dan teks saat belum ada file --}}
                                        <div x-show="!ktmFileName">
                                            <div class="nss-upload-icon">🪪</div>
                                            <p class="nss-upload-label">Klik untuk pilih file KTM</p>
                                            <p class="nss-upload-hint">Format: JPG, PNG, atau PDF · Maks. 2 MB</p>
                                        </div>

                                        {{-- Teks konfirmasi saat file sudah dipilih --}}
                                        <div x-show="ktmFileName" x-cloak>
                                            <div class="nss-upload-icon">✅</div>
                                            <p class="nss-upload-label">File dipilih</p>
                                        </div>
                                    </div>

                                    {{-- Preview nama file yang dipilih + tombol hapus --}}
                                    <div class="nss-file-preview" x-show="ktmFileName" x-cloak>
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="nss-file-preview-name" x-text="ktmFileName"></span>
                                        {{-- Tombol hapus file: reset input dan state ktmFileName --}}
                                        <button type="button" class="nss-file-preview-clear"
                                                aria-label="Hapus file"
                                                @click="ktmFileName = ''; $refs.ktmInput.value = ''"
                                                x-ref="clearBtn">✕</button>
                                    </div>

                                    {{-- Info box: penjelasan kenapa KTM diperlukan --}}
                                    <div class="nss-ktm-info">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>KTM diperlukan untuk mendapatkan tarif mahasiswa (Rp35.000/hari). File akan diverifikasi oleh admin.</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- KARTU TICKET -->
                <aside class="nss-bk-aside" :class="{ 'is-open': step === 2 || step === 4 }" aria-label="Ringkasan pesanan">
                    <div class="nss-pass">
                        <div class="nss-pass-body">
                            <p class="nss-pass-title font-headline">Needs Study Space</p>
                            <h2 class="nss-display nss-pass-name">Day pass</h2>

                            <dl class="nss-rows">
                                <div class="nss-row">
                                    <dt>Tarif</dt>
                                    <dd x-text="categoryName()"></dd>
                                </div>
                                <div class="nss-row" x-show="form.date">
                                    <dt>Mulai</dt>
                                    <dd x-text="formatDate(form.date)"></dd>
                                </div>
                            </dl>
                            
                            <div class="nss-divider"></div>

                            <dl class="nss-rows">
                                <div class="nss-row">
                                    <dt>Sewa <span x-text="form.duration"></span> hari</dt>
                                    <dd style="font-weight: 400;">Rp<span x-text="formatPrice(pricePerDay() * form.duration)"></span></dd>
                                </div>
                                <template x-for="item in cart()" :key="item.id">
                                    <div class="nss-row">
                                        <dt><span x-text="item.name"></span> &times; <span x-text="item.qty"></span></dt>
                                        <dd style="font-weight: 400;">Rp<span x-text="formatPrice(item.subtotal)"></span></dd>
                                    </div>
                                </template>
                            </dl>
                        </div>

                        <div class="nss-perf" aria-hidden="true"><span></span></div>

                        <div class="nss-pass-foot" :class="{ 'nss-bump': bumped }">
                            <span class="nss-total-label">Total</span>
                            <span class="nss-display nss-total-price">Rp<span x-text="formatPrice(calculateTotal())"></span></span>
                        </div>
                    </div>
                </aside>

            </div>

            <!-- Sticky Bar -->
            <div class="nss-bar">
                <div class="nss-bar-info">
                    <p class="nss-small nss-muted" x-show="hint()" x-text="hint()"></p>
                    <div class="nss-bar-totalbox" x-show="!hint()" x-cloak>
                        <p class="nss-xs nss-muted">Total</p>
                        <p class="nss-display nss-bar-total">Rp<span x-text="formatPrice(calculateTotal())"></span></p>
                    </div>
                </div>

                <button type="button" class="nss-btn" x-show="step < 4" @click="nextStep()" :disabled="!canNext()">
                    <span x-text="nextLabel()"></span>
                </button>

                <button type="submit" class="nss-btn" x-show="step === 4" x-cloak :disabled="isSubmitting">
                    <svg class="nss-spin" x-show="isSubmitting" width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span x-text="isSubmitting ? 'Memproses...' : 'Bayar sekarang'"></span>
                </button>
            </div>
        </form>
    </main>
</div>

<script>
function bookingWizard() {
    const PRICES = { mahasiswa: 35000, umum: 55000 };
    const NAMES = { mahasiswa: 'Mahasiswa', umum: 'Umum' };
    const TITLES = ['Pilih tarif', 'Tanggal dan durasi', 'Snack dan minuman', 'Data pemesan'];
    const INTROS = [
        'Satu tarif berlaku untuk semua area di Needs Study Space.',
        'Tentukan tanggal mulai dan berapa hari kamu datang.',
        'Opsional. Boleh dilewati.',
        'Isi data untuk pesanan ini.'
    ];
    const HOME_URL = '{{ url('/') }}';
    const catalog = @json($catalog);
    const oldProducts = @json(old('products', []));
    const oldCategory = @json(old('visitor_category', request('tarif', 'mahasiswa')));
    const oldDuration = parseInt(@json(old('duration', 1)), 10);
    const restored = @json((bool) old('visitor_category'));

    return {
        step: restored ? 4 : 1,
        isSubmitting: false,
        bumped: false,
        area: 0,
        galTimer: null,   // timer slideshow galeri foto
        galCount: 12,     // total foto galeri
        touchX: 0,
        areaCount: {{ $roomTypes->count() }},
        labels: ['Tarif', 'Tanggal', 'Tambahan', 'Pemesan'],
        form: {
            category: PRICES[oldCategory] ? oldCategory : 'mahasiswa',
            date: @json(old('date', '')),
            duration: oldDuration >= 1 && oldDuration <= 30 ? oldDuration : 1,
            customer_name: @json(old('customer_name', '')),
            customer_phone: @json(old('customer_phone', '')),
            products: {}
        },

        init() {
            for (const id in oldProducts) {
                const qty = parseInt(oldProducts[id], 10);
                if (catalog[id] && qty > 0) this.form.products[id] = qty;
            }
            this.$watch(() => this.calculateTotal(), () => {
                this.bumped = false;
                requestAnimationFrame(() => requestAnimationFrame(() => { this.bumped = true; }));
            });
            // Mulai slideshow galeri otomatis setiap 3 detik
            this.galTimer = setInterval(() => { this.area = (this.area + 1) % this.galCount; }, 3000);
        },

        moveArea(delta, focusTab = false) {
            if (!this.areaCount) return;
            this.area = (this.area + delta + this.areaCount) % this.areaCount;
            if (focusTab) this.$nextTick(() => document.getElementById('area-tab-' + this.area)?.focus());
        },
        // Swipe galeri: geser kiri/kanan pindah foto (galCount=12, bukan areaCount tab)
        swipe(endX) {
            const dx = endX - this.touchX;
            if (Math.abs(dx) > 45) this.galJump((this.area + (dx < 0 ? 1 : -1) + this.galCount) % this.galCount);
        },
        // Klik dot: loncat ke foto tertentu, reset timer agar tidak langsung loncat lagi
        galJump(idx) {
            this.area = idx;
            clearInterval(this.galTimer);
            this.galTimer = setInterval(() => { this.area = (this.area + 1) % this.galCount; }, 3000);
        },

        stepTitle() { return TITLES[this.step - 1]; },
        stepIntro() { return INTROS[this.step - 1]; },

        goTo(n) {
            this.step = n;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const behavior = reduce ? 'auto' : 'smooth';
            window.scrollTo({ top: 0, behavior });
            this.$refs.panel.closest('.nss-bk-main')?.scrollTo({ top: 0, behavior });
            this.$nextTick(() => this.$refs.panel.focus({ preventScroll: true }));
        },
        back() {
            if (this.step > 1) this.goTo(this.step - 1);
            else window.location.href = HOME_URL;
        },
        nextStep() {
            if (this.canNext()) this.goTo(this.step + 1);
        },
        canNext() {
            if (this.step === 1) return !!this.form.category;
            if (this.step === 2) return !!this.form.date;
            return true;
        },
        hint() {
            if (this.step === 2 && !this.form.date) return 'Pilih tanggal mulai untuk lanjut';
            return '';
        },
        nextLabel() {
            if (this.step === 3) return this.cartCount() === 0 ? 'Lewati' : 'Lanjutkan';
            return 'Lanjutkan';
        },

        pricePerDay() { return PRICES[this.form.category] || PRICES.mahasiswa; },
        categoryName() { return NAMES[this.form.category] || ''; },
        changeDuration(delta) {
            const next = this.form.duration + delta;
            if (next >= 1 && next <= 30) this.form.duration = next;
        },
        endDate() {
            if (!this.form.date) return '';
            const d = new Date(this.form.date + 'T00:00:00');
            d.setDate(d.getDate() + this.form.duration - 1);
            const pad = n => String(n).padStart(2, '0');
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        },

        getQty(id) { return this.form.products[id] || 0; },
        updateProduct(id, delta) {
            const next = Math.max(0, Math.min(20, this.getQty(id) + delta));
            this.form.products = { ...this.form.products, [id]: next };
        },
        cart() {
            return Object.keys(this.form.products)
                .filter(id => this.form.products[id] > 0 && catalog[id])
                .map(id => ({
                    id,
                    name: catalog[id].name,
                    qty: this.form.products[id],
                    subtotal: catalog[id].price * this.form.products[id]
                }));
        },
        cartCount() {
            return this.cart().reduce((sum, i) => sum + i.qty, 0);
        },

        calculateTotal() {
            return this.pricePerDay() * this.form.duration
                + this.cart().reduce((sum, i) => sum + i.subtotal, 0);
        },
        formatPrice(price) {
            return new Intl.NumberFormat('id-ID').format(price);
        },
        formatDate(value) {
            if (!value) return '-';
            return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {
                weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
            });
        }
    }
}
</script>
@endsection