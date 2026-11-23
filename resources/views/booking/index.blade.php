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

    html, body { overflow: hidden; height: 100%; }

    .nss-bk { height: 100vh; height: 100dvh; display: flex; flex-direction: column; background: var(--nss-paper); }

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

    .nss-bk-main { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; padding-top: .5rem; padding-bottom: 0; }

    .nss-form-wrapper { display: flex; flex-direction: column; flex: 1; min-height: 100%; }

    /* Kolom ringkasan baru dipisah pada desktop agar konten form tetap lega. */
    .nss-bk-grid { flex: 1; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-items: start; padding-bottom: 1rem; }

    /* ── Penyesuaian Responsif Mobile Agar ke Tengah ───────────────────── */
    .nss-bk-form-inner {
        order: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 38rem;
        margin-inline: auto;
        padding-inline: 1rem;
    }

    /* Kartu ringkasan: tersembunyi di mobile, tampil saat Alpine.js tambah class is-open */
    .nss-bk-aside {
        order: 2;
        display: none;
        width: 100%;
        max-width: 38rem;
        margin: 1.5rem auto 0 auto;
    }
    .nss-bk-aside.is-open { display: block; }

    /* clamp(min, preferred, max) = ukuran font responsif */
    .nss-title { font-size: clamp(1.125rem, 3vw, 1.375rem); line-height: 1.2; font-weight: 700; letter-spacing: -.015em; }
    .nss-lead { margin-top: .125rem; max-width: 30rem; font-size: .8125rem; line-height: 1.4; color: var(--nss-muted); }
    .nss-panel { outline: none; padding-bottom: .5rem; width: 100%; max-width: 38rem; margin-inline: auto; }
    .nss-step-body { margin-top: .5rem; }
    .nss-alert { margin-bottom: .75rem; padding: .625rem .75rem; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: .875rem; }

    /* Input form */
    .nss-bk .nss-label { font-size: .8125rem; margin-bottom: .375rem; display: block; font-weight: 500; }
    .nss-bk .nss-field { width: 100%; box-sizing: border-box; padding: 0 0.875rem; border: 1px solid var(--nss-line); background: #fff; height: 2.75rem; font-size: 1rem; border-radius: 8px; transition: border-color .15s, box-shadow .15s; }
    .nss-bk .nss-field:focus { border-color: var(--nss-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--nss-primary) 18%, transparent); outline: none; }
    .nss-time-fields { display: grid; grid-template-columns: minmax(0, 1fr); gap: .75rem; margin-top: 0; align-items: start; }
    .nss-field-error { margin-top: .25rem; color: #b91c1c; font-size: .75rem; }
    .nss-bk .nss-btn { height: 3rem; padding: 0 1.5rem; font-size: .875rem; min-width: 8rem; border-radius: 8px; }

    /* Kartu pilihan tarif: gap dikecilkan agar tombol Lanjut tidak terpotong */
    .nss-tarifs { display: grid; gap: .4rem; width: 100%; margin-inline: auto; }
    .nss-tarif { display: flex; flex-wrap: wrap; align-items: center; gap: .2rem .5rem; padding: .625rem .875rem; background: #fff; border: 2px solid var(--nss-line); border-radius: 10px; cursor: pointer; transition: border-color .2s, background .2s; }
    label:hover > .nss-tarif { border-color: color-mix(in srgb, var(--nss-primary) 35%, var(--nss-line)); }
    input:checked + .nss-tarif { border-color: var(--nss-primary); background: color-mix(in srgb, var(--nss-primary) 5%, #fff); }
    input:focus-visible + .nss-tarif { outline: 2px solid var(--nss-primary); outline-offset: 2px; }

    /* Radio kustom: box-shadow inset meniru tampilan radio button lingkaran */
    .nss-dot { flex: none; width: 18px; height: 18px; border-radius: 50%; color: var(--nss-primary); box-shadow: inset 0 0 0 2px #c3cece; transition: box-shadow .2s ease-out; }
    input:checked + .nss-tarif .nss-dot { box-shadow: inset 0 0 0 6px currentColor; } /* terisi penuh saat dipilih */
    .nss-tarif-name { flex: 1 1 auto; font-size: .9375rem; font-weight: 600; }
    .nss-tarif-price { margin-left: auto; font-size: clamp(1.125rem, 4vw, 1.5rem); font-weight: 700; line-height: 1.1; white-space: nowrap; }
    .nss-tarif-price .rp  { font-size: .8125rem; font-weight: 600; }
    .nss-tarif-price .per { margin-left: .2rem; font-size: .75rem; font-weight: 500; color: var(--nss-muted); }
    .nss-bk .nss-tarif-price { font-size: 1.0625rem; }
    .nss-bk .nss-tarif-price .rp { font-size: .75rem; }

    /* Pilihan jenis tempat: dua kartu berdampingan, masing-masing menjelaskan isinya */
    .nss-modes { display: grid; grid-template-columns: minmax(0, 1fr); gap: .5rem; }
    .nss-modes > label { display: block; }
    .nss-mode-card { display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: .75rem; row-gap: .125rem; align-items: start; height: 100%; box-sizing: border-box; padding: .875rem 1rem; }
    .nss-mode-card .nss-dot { grid-row: 1 / span 3; margin-top: .125rem; }
    .nss-mode-name { font-size: .9375rem; font-weight: 600; }
    .nss-mode-desc { font-size: .8125rem; line-height: 1.4; color: var(--nss-muted); }
    .nss-mode-spec { margin-top: .375rem; font-size: .75rem; font-weight: 500; font-variant-numeric: tabular-nums; }
    @media (min-width: 640px) { .nss-modes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    /* Daftar venue: satu baris per ruangan, terpilih ditandai garis dan titik yang sama dengan radio lain */
    .nss-venues { display: grid; gap: .5rem; margin-top: .5rem; }
    .nss-venue { display: flex; align-items: flex-start; gap: .875rem; width: 100%; box-sizing: border-box; padding: .875rem 1rem; text-align: left; background: #fff; border: 2px solid var(--nss-line); border-radius: 10px; cursor: pointer; transition: border-color .2s, background .2s; }
    .nss-venue:hover:not(:disabled) { border-color: color-mix(in srgb, var(--nss-primary) 35%, var(--nss-line)); }
    .nss-venue[aria-pressed="true"] { border-color: var(--nss-primary); background: color-mix(in srgb, var(--nss-primary) 5%, #fff); }
    .nss-venue[aria-pressed="true"] .nss-dot { box-shadow: inset 0 0 0 6px currentColor; }
    .nss-venue:focus-visible { outline: 2px solid var(--nss-primary); outline-offset: 2px; }
    .nss-venue:disabled { opacity: .6; cursor: not-allowed; }
    .nss-venue-img { flex: none; width: 5rem; height: 5rem; object-fit: cover; border-radius: 8px; background: #e5eaea; }
    .nss-venue-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .125rem; }
    .nss-venue-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
    .nss-venue-name { font-size: .9375rem; font-weight: 600; }
    .nss-venue-type { font-size: .8125rem; color: var(--nss-muted); }
    .nss-venue-spec { display: flex; flex-wrap: wrap; gap: .125rem 1rem; margin-top: .375rem; font-size: .8125rem; font-variant-numeric: tabular-nums; }
    .nss-venue-spec b { font-weight: 600; }
    .nss-venue-desc { margin-top: .375rem; font-size: .8125rem; line-height: 1.45; color: var(--nss-muted); }
    .nss-venue-fac { font-size: .75rem; line-height: 1.45; color: var(--nss-muted); }
    .nss-venue-warn { margin-top: .375rem; font-size: .8125rem; font-weight: 500; color: #b91c1c; }
    @media (min-width: 640px) { .nss-venue-img { width: 7rem; height: 7rem; } }

    /* Galeri mobile mengikuti lebar kolom; tinggi dibatasi agar rasio gambar
       tidak memicu overflow horizontal pada layar sempit. */
    .nss-areas-head { margin-top: .625rem; font-size: .9375rem; font-weight: 600; }
    .nss-gal-frame { width: 100%; min-width: 0; height: clamp(10rem, 52vw, 13rem); min-height: 0; max-height: none; margin-top: .375rem; aspect-ratio: auto; }
    @media (min-width: 640px)  { .nss-gal-frame { height: auto; max-height: 240px; aspect-ratio: 16 / 6; } }
    @media (min-width: 1024px) { .nss-gal-frame { max-height: 280px; aspect-ratio: 16 / 6; } }
    .nss-tabs-wrap { margin-top: .375rem; }

    /* Paket tetap selebar form; tanggal dan jumlah orang dibagi setelah ruang cukup. */
    .nss-step2 { display: grid; gap: 1.25rem; width: 100%; }
    .nss-package-fieldset { min-width: 0; margin: 0; padding: 0; border: 0; }
    .nss-package-options { display: grid; grid-template-columns: minmax(0, 1fr); gap: .75rem; margin-top: .5rem; }
    .nss-package-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: .125rem .75rem; min-height: 4.5rem; padding: .875rem 1rem; box-sizing: border-box; background: #fff; border: 2px solid var(--nss-line); border-radius: 10px; cursor: pointer; transition: border-color .2s, background .2s; }
    .nss-package-options > label { display: block; }
    label:hover > .nss-package-card { border-color: color-mix(in srgb, var(--nss-primary) 35%, var(--nss-line)); }
    input:checked + .nss-package-card { border-color: var(--nss-primary); background: color-mix(in srgb, var(--nss-primary) 5%, #fff); }
    input:focus-visible + .nss-package-card { outline: 2px solid var(--nss-primary); outline-offset: 2px; }
    .nss-package-card-name { font-size: .9375rem; font-weight: 600; }
    .nss-package-card-duration { font-size: .8125rem; color: var(--nss-muted); }
    .nss-package-card-price { font-size: .875rem; font-weight: 600; font-variant-numeric: tabular-nums; }
    .nss-package-card-name { grid-column: 1; }
    .nss-package-card-duration { grid-column: 1; }
    .nss-package-card-price { grid-column: 2; grid-row: 1 / span 2; max-width: 9rem; text-align: right; line-height: 1.35; }
    .nss-date-participant-grid { display: grid; grid-template-columns: minmax(0, 1fr); align-items: start; gap: 1.25rem; }
    .nss-date-column { min-width: 0; display: flex; flex-direction: column; gap: .75rem; }
    .nss-attendee-panel { min-width: 0; padding: 1rem; border: 1px solid var(--nss-line); border-radius: 12px; background: #fff; }
    .nss-capacity-warning { margin-bottom: .75rem; padding: .75rem; border: 1px solid #fed7aa; border-radius: 8px; background: #fff7ed; color: #9a3412; font-size: .875rem; }
    .nss-schedule-list { display: grid; gap: .75rem; }
    .nss-schedule-card { min-width: 0; padding: 1rem; border: 1px solid var(--nss-line); border-radius: 12px; background: #fff; }
    /* float:left + width:100% membuat legend jadi judul biasa di dalam kartu (tidak menembus garis border) */
    .nss-schedule-card legend { float: left; width: 100%; max-width: 100%; margin: 0 0 .75rem; padding: 0; color: var(--nss-ink); font-size: .875rem; font-weight: 600; line-height: 1.4; }
    .nss-schedule-card legend + * { clear: both; }
    .nss-cal { width: 100%; max-width: 22rem; background: #fff; border: 1px solid var(--nss-line); border-radius: 10px; padding: .75rem; }
    .nss-stepper { display: flex; max-width: 12rem; height: 2.75rem; border: 1px solid var(--nss-line); border-radius: 8px; background: #fff; overflow: hidden; }
    .nss-stepper:focus-within { border-color: var(--nss-primary); }
    .nss-stepper > button { display: grid; place-items: center; width: 2.75rem; transition: background .15s; }
    .nss-stepper > button:hover:not(:disabled) { background: var(--nss-paper); }
    .nss-stepper > button:disabled { opacity: .3; cursor: not-allowed; }
    .nss-stepper-val { flex: 1; display: flex; align-items: center; justify-content: center; gap: .375rem; border-left: 1px solid var(--nss-line); border-right: 1px solid var(--nss-line); }
    .nss-stepper-val input { width: 2rem; padding: 0; border: 0; background: none; text-align: center; font: inherit; font-weight: 700; color: inherit; outline: none; }
    .nss-note { margin-top: .5rem; font-size: .875rem; color: var(--nss-muted); }

    /* ── Kalender kustom (tanggal ditutup admin tidak bisa diklik) ─────── */
    .nss-cal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; }
    .nss-cal-head button { width: 2rem; height: 2rem; border-radius: 6px; font-size: 1.125rem; }
    .nss-cal-head button:hover:not(:disabled) { background: var(--nss-paper); }
    .nss-cal-head button:disabled { opacity: .3; cursor: not-allowed; }
    .nss-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .nss-cal-dow span { font-size: .6875rem; color: var(--nss-muted); padding: .25rem 0; }
    .nss-day { aspect-ratio: 1; border-radius: 6px; font-size: .875rem; font-variant-numeric: tabular-nums; }
    .nss-day:hover:not(:disabled) { background: var(--nss-paper); }
    .nss-day.is-selected { background: var(--nss-primary); color: #fff; font-weight: 700; }
    .nss-day.is-range { background: color-mix(in srgb, var(--nss-primary) 15%, transparent); }
    .nss-day.is-past { opacity: .3; cursor: not-allowed; }
    .nss-day.is-blocked { background: #fef2f2; color: #b91c1c; text-decoration: line-through; cursor: not-allowed; }
    .nss-day:empty { visibility: hidden; }
    .nss-cal-legend { display: flex; gap: .75rem; margin-top: .625rem; font-size: .75rem; color: var(--nss-muted); }
    .nss-cal-legend i { display: inline-block; width: .625rem; height: .625rem; border-radius: 3px; margin-right: .25rem; background: #fecaca; }

    /* Daftar snack */
    .nss-list { width: 100%; max-width: 36rem; margin-inline: auto; border-top: 1px solid var(--nss-line); border-bottom: 1px solid var(--nss-line); }
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
    .nss-fields { display: grid; gap: 1rem; width: 100%; max-width: 28rem; margin-inline: auto; }

    /* Bar aksi selalu terjangkau di bawah area konten booking. */
    .nss-bar { position: sticky; bottom: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: auto -1rem 0; padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom, 0px)); background: var(--nss-paper); border-top: 1px solid var(--nss-line); box-shadow: 0 -6px 16px -10px rgba(15, 30, 40, .18); }
    .nss-bar-info  { min-width: 0; margin-right: auto; }
    .nss-bar-total { font-size: 1.125rem; font-weight: 700; line-height: 1.2; color: var(--nss-primary); font-variant-numeric: tabular-nums; }

    /* Kartu ringkasan mengikuti contoh visual, sementara rincian tetap tersedia pada langkah lanjutan. */
    .nss-pass { background: #fff; border: 1px solid #e4e5e7; border-radius: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, .03); overflow: hidden; color: var(--nss-ink); }
    .nss-pass-body { padding: 2rem 2.25rem 0; }
    .nss-pass-title { margin-bottom: 1.25rem; color: var(--nss-muted); font-size: .875rem; }
    .nss-pass-name { margin-bottom: 2.75rem; color: var(--nss-ink); font-size: 1.75rem; font-weight: 700; letter-spacing: -.03em; }
    .nss-rows { display: grid; gap: 1rem; font-size: .9375rem; }
    .nss-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
    .nss-row dt { color: var(--nss-muted); font-weight: 400; }
    .nss-row dd { color: var(--nss-ink); font-weight: 600; text-align: right; }
    .nss-summary-dates { display: grid; justify-items: end; gap: .5rem; }
    .nss-summary-date { display: grid; gap: .125rem; }
    .nss-summary-date-label { font-size: .8125rem; line-height: 1.4; }
    .nss-summary-date-time { color: var(--nss-muted); font-size: .75rem; font-weight: 400; font-variant-numeric: tabular-nums; }
    .nss-divider { height: 1px; margin: 1rem 0 0; background: #e4e5e7; }
    .nss-pass-breakdown { padding-top: 1rem; }
    .nss-pass-breakdown .nss-row dd { font-weight: 400; }
    .nss-perf { display: none; }
    .nss-pass-foot { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 2.75rem 2.25rem 2rem; }
    .nss-total-label { color: var(--nss-ink); font-size: 1rem; font-weight: 400; }
    .nss-total-price { color: #dc143c; font-size: clamp(1.5rem, 5vw, 2rem); font-weight: 800; letter-spacing: -.02em; text-align: right; font-variant-numeric: tabular-nums; }
    .nss-pass:not(.nss-pass--tariff) .nss-pass-name { margin-bottom: 1.5rem; }
    .nss-pass:not(.nss-pass--tariff) .nss-pass-foot { padding-top: 1.5rem; }
    @media (max-width: 380px) {
        .nss-pass-body { padding-inline: 1.25rem; }
        .nss-pass-foot { padding-inline: 1.25rem; }
        .nss-total-price { font-size: 1.375rem; }
    }

    /* HP sangat kecil (<380px) */
    @media (max-width: 380px) { .nss-bk .nss-btn { min-width: 0; padding: 0 1.125rem; } .nss-tarif { padding: .75rem; } .nss-package-card { padding-inline: .75rem; } }

    /* Tablet (>=640px) */
    @media (min-width: 640px) {
        .nss-bk-main    { padding-top: .75rem; }
        .nss-bk-form-inner { width: 100%; max-width: 40rem; margin-inline: auto; }
        .nss-bar        { margin-inline: 0; padding-inline: 0; box-shadow: none; }
        .nss-panel      { padding-bottom: .75rem; }
        .nss-tarif      { padding: .75rem 1rem; }
        .nss-bk-aside   { max-width: 22rem; }
        .nss-package-options { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .nss-package-card { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: .25rem; min-height: 7.5rem; }
        .nss-package-card-name, .nss-package-card-duration, .nss-package-card-price { max-width: 100%; }
        .nss-package-card-price { font-size: .8125rem; text-align: left; white-space: nowrap; }
        .nss-time-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .nss-time-fields .nss-label { min-height: 2.5em; } /* cadangkan 2 baris agar kedua input sejajar */
    }

    @media (min-width: 768px) {
        .nss-date-participant-grid { grid-template-columns: minmax(0, 1fr) minmax(13.5rem, .65fr); }
        .nss-attendee-panel { position: sticky; top: .25rem; }
    }

    /* Desktop: ringkasan sticky di samping form, dengan lebar yang tetap terbaca. */
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
    .nss-upload-box { margin-top: .5rem; border: 2px dashed var(--nss-line); border-radius: 10px; padding: 1.25rem 1rem; text-align: center; background: #f8fafa; transition: border-color .2s, background .2s; cursor: pointer; position: relative; }
    .nss-upload-box:hover    { border-color: var(--nss-primary); background: #f0f7f7; }
    .nss-upload-box.has-file { border-color: var(--nss-primary); background: #eef7f5; }
    .nss-upload-box.has-error { border-color: #b91c1c; }

    /* Input file asli disembunyikan (opacity:0). Klik kotak = klik input karena inset:0 */
    .nss-upload-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .nss-upload-icon  { font-size: 2rem; margin-bottom: .375rem; }
    .nss-upload-label { font-size: .875rem; font-weight: 600; color: var(--nss-ink); }
    .nss-upload-hint  { font-size: .75rem; color: var(--nss-muted); margin-top: .25rem; }
    .nss-ktm-error { margin-top: .5rem; color: #b91c1c; font-size: .875rem; }

    .nss-file-preview { display: flex; align-items: center; gap: .5rem; margin-top: .625rem; padding: .5rem .75rem; background: #fff; border: 1px solid var(--nss-line); border-radius: 8px; font-size: .8125rem; }
    .nss-file-preview-name  { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }
    .nss-file-preview-clear { flex: none; color: var(--nss-muted); cursor: pointer; }
    .nss-file-preview-clear:hover { color: #b91c1c; }

    .nss-ktm-info { display: flex; align-items: flex-start; gap: .5rem; padding: .625rem .875rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: .8125rem; color: #1e40af; margin-top: .75rem; }
    .nss-ktm-info svg { flex: none; margin-top: 1px; }
</style>

<div id="booking-wizard" x-data="bookingWizard()" data-blocked="{{ json_encode($blockedDates ?? []) }}" class="nss nss-bk">

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
        {{-- enctype="multipart/form-data" WAJIB agar file KTM terkirim ke server. --}}
        <form action="{{ route('booking.store') }}" method="POST" id="bookingForm"
            enctype="multipart/form-data"
            @submit="submitBooking($event)" class="nss-form-wrapper">
            @csrf

            <div class="nss-bk-grid">

                <!-- KONTEN FORM -->
                <div class="nss-bk-form-inner">
                    @if(session('error') || $errors->any())
                    <div role="alert" class="nss-alert">{{ session('error') ?? $errors->first() }}</div>
                    @endif

                    <div x-ref="panel" tabindex="-1" class="nss-panel">
                        <h1 class="nss-display nss-title" x-text="stepTitle()"></h1>
                        <p class="nss-lead" x-text="stepIntro()"></p>

                        <div class="nss-step-body">

                            <!-- ============ LANGKAH 1: TARIF ============ -->
                            <div x-show="step === 1"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">

                                <fieldset style="border:0;margin:0 0 1.5rem;padding:0;min-width:0">
                                    <legend class="nss-radio">Pilih jenis tempat</legend>
                                    <div class="nss-modes">
                                        <label>
                                            <input type="radio" name="booking_type" value="open_space" x-model="form.booking_type" @change="checkAvailabilityForSelectedDates()" class="nss-radio">
                                            <span class="nss-tarif nss-mode-card">
                                                <span class="nss-dot"></span>
                                                <span class="nss-mode-name">NEEDS Study Space</span>
                                                <span class="nss-mode-desc">Meja bersama untuk belajar, bayar per hari.</span>
                                                <span class="nss-mode-spec">Kapasitas {{ config('booking.open_space_capacity', 50) }} orang</span>
                                            </span>
                                        </label>
                                        <label>
                                            <input type="radio" name="booking_type" value="room" x-model="form.booking_type" @change="checkAvailabilityForSelectedDates()" class="nss-radio">
                                            <span class="nss-tarif nss-mode-card">
                                                <span class="nss-dot"></span>
                                                <span class="nss-mode-name">Venue lainnya</span>
                                                <span class="nss-mode-desc">Ruang meeting dan training, disewa per paket.</span>
                                                <span class="nss-mode-spec">{{ $rooms->count() }} ruangan tersedia</span>
                                            </span>
                                        </label>
                                    </div>
                                </fieldset>

                                <input type="hidden" name="room_id" :value="form.booking_type === 'room' ? form.room_id : ''">

                                <fieldset x-show="form.booking_type === 'open_space'" style="border:0;margin:0;padding:0;min-width:0">
                                    <legend class="nss-radio">Pilih tarif</legend>
                                    <div class="nss-tarifs">
                                        <label>
                                            <input type="radio" name="visitor_category" value="mahasiswa" x-model="form.category" :disabled="form.booking_type !== 'open_space'" class="nss-radio">
                                            <div class="nss-tarif">
                                                <span class="nss-dot"></span>
                                                <span class="nss-tarif-name">Mahasiswa</span>
                                                <span class="nss-display nss-num nss-tarif-price"><span class="nss-mark"><span class="rp">Rp</span>35.000</span><span class="per">/hari</span></span>
                                            </div>
                                        </label>
                                        <label>
                                            <input type="radio" name="visitor_category" value="umum" x-model="form.category" :disabled="form.booking_type !== 'open_space'" class="nss-radio">
                                            <div class="nss-tarif">
                                                <span class="nss-dot"></span>
                                                <span class="nss-tarif-name">Umum</span>
                                                <span class="nss-display nss-num nss-tarif-price"><span class="nss-mark"><span class="rp">Rp</span>55.000</span><span class="per">/hari</span></span>
                                            </div>
                                        </label>
                                    </div>
                                </fieldset>

                                <section x-show="form.booking_type === 'open_space'" aria-labelledby="areas-title">
                                    <h2 id="areas-title" class="nss-display nss-areas-head">Area di dalam Needs Study Space</h2>

                                    {{-- Galeri 12 foto area, slideshow otomatis 3 detik --}}
                                    <div class="nss-gal-frame"
                                        @touchstart.passive="touchX=$event.changedTouches[0].clientX"
                                        @touchend.passive="swipe($event.changedTouches[0].clientX)">

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

                                    <div class="nss-stack" aria-live="polite">
                                        <div class="is-active" aria-hidden="false">
                                            <p class="nss-small nss-muted">Kapasitas Open Space: {{ config('booking.open_space_capacity', 50) }} orang</p>
                                            <p style="margin-top:.25rem;max-width:40rem;color:#4b5a66">Ruang kerja bersama (open space) yang tenang, luas, dan fleksibel untuk kebutuhan belajarmu.</p>
                                        </div>
                                    </div>
                                </section>

                                <section x-show="form.booking_type === 'room'" x-cloak aria-labelledby="venue-title">
                                    <h2 id="venue-title" class="nss-display nss-areas-head">Pilih meeting room atau venue</h2>
                                    <div class="nss-venues">
                                        @forelse($rooms as $room)
                                            @php
                                                $minimumAttendees = $room->minimumAttendees();
                                                $capacityBelowMinimum = $room->capacity < $minimumAttendees;
                                            @endphp
                                            <button type="button" class="nss-venue"
                                                @click="selectRoom('{{ $room->id }}')"
                                                :disabled="@js($capacityBelowMinimum)"
                                                :aria-pressed="String(form.room_id) === '{{ $room->id }}'">
                                                @if($room->image_url)
                                                    <img src="{{ $room->image_url }}" alt="" class="nss-venue-img" loading="lazy">
                                                @endif
                                                <span class="nss-venue-body">
                                                    <span class="nss-venue-head">
                                                        <span class="nss-venue-name">{{ $room->name }}</span>
                                                        <span class="nss-dot" aria-hidden="true"></span>
                                                    </span>
                                                    <span class="nss-venue-type">{{ $room->type }}</span>
                                                    <span class="nss-venue-spec">
                                                        <span>Kapasitas <b>{{ $room->capacity }}</b> orang</span>
                                                        <span>Minimum <b>{{ $minimumAttendees }}</b> orang</span>
                                                    </span>
                                                    @if($capacityBelowMinimum)
                                                        <span class="nss-venue-warn">Belum bisa dipesan: kapasitas di bawah minimum pemesanan. Hubungi admin.</span>
                                                    @endif
                                                    @if($room->description)
                                                        <span class="nss-venue-desc">{{ $room->description }}</span>
                                                    @endif
                                                    @if($room->facilities)
                                                        <span class="nss-venue-fac">{{ implode(', ', $room->facilities) }}</span>
                                                    @endif
                                                </span>
                                            </button>
                                        @empty
                                            <p class="nss-note">Belum ada venue yang tersedia. Silakan hubungi admin.</p>
                                        @endforelse
                                    </div>
                                    @if($rooms->isNotEmpty())
                                        <p class="nss-note">Paket sewa dipilih di langkah berikutnya.</p>
                                    @endif
                                </section>
                            </div>

                            <!-- ============ LANGKAH 2: TANGGAL, JAM, DAN JUMLAH ORANG ============ -->
                            <div x-show="step === 2" x-cloak class="nss-step2"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">

                                <fieldset x-show="form.booking_type === 'room'" x-cloak class="nss-package-fieldset">
                                        <legend class="nss-label">Pilih paket sewa</legend>
                                        <div class="nss-package-options">
                                            @foreach($venuePackages as $packageKey => $package)
                                                <label class="cursor-pointer">
                                                    <input type="radio"
                                                        name="venue_package"
                                                        value="{{ $packageKey }}"
                                                        x-model="form.venue_package"
                                                        @change="normalizeVenueStartTimes(); checkAvailabilityForSelectedDates()"
                                                        :required="form.booking_type === 'room'"
                                                        :disabled="form.booking_type !== 'room'"
                                                        class="peer sr-only">
                                                    <span class="nss-package-card">
                                                        <span class="nss-package-card-name">{{ $package['label'] }}</span>
                                                        <span class="nss-package-card-duration">{{ $package['duration_hours'] }} jam</span>
                                                        <span class="nss-package-card-price">Rp{{ number_format($package['price_per_person'], 0, ',', '.') }} / orang</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="nss-note mt-2">Setiap paket sewa dikenakan Service Charge {{ $venueServiceChargePercent }}%.</p>
                                        @error('venue_package')
                                            <p class="nss-field-error">{{ $message }}</p>
                                        @enderror
                                </fieldset>

                                <div class="nss-date-participant-grid">
                                    <div class="nss-date-column">
                                        <div>
                                            <h2 class="nss-label">Pilih tanggal kunjungan</h2>
                                            <input type="hidden" name="date" :value="form.dates[0] || ''">
                                            <input type="hidden" name="duration" :value="form.dates.length">
                                            <template x-for="date in form.dates" :key="date">
                                                <input type="hidden" name="dates[]" :value="date">
                                            </template>

                                            <div class="nss-cal">
                                                <div class="nss-cal-head">
                                                    <button type="button" @click="prevMonth()" :disabled="!canPrev()" aria-label="Bulan sebelumnya">&lsaquo;</button>
                                                    <strong x-text="monthLabel()"></strong>
                                                    <button type="button" @click="nextMonth()" aria-label="Bulan berikutnya">&rsaquo;</button>
                                                </div>

                                                <div class="nss-cal-grid nss-cal-dow">
                                                    <template x-for="d in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']" :key="d">
                                                        <span x-text="d"></span>
                                                    </template>
                                                </div>

                                                <div class="nss-cal-grid">
                                                    <template x-for="(cell, i) in calendarCells()" :key="i">
                                                        <button type="button" class="nss-day"
                                                                :class="cell ? {
                                                                    'is-selected': form.dates.includes(cell.str),
                                                                    'is-past': cell.past,
                                                                    'is-blocked': cell.blocked
                                                                } : {}"
                                                                :disabled="!cell || cell.disabled"
                                                                :title="cell && cell.blocked ? 'Ditutup / penuh' : ''"
                                                                @click="cell && toggleDate(cell.str)"
                                                                x-text="cell ? cell.day : ''"></button>
                                                    </template>
                                                </div>

                                                <div class="nss-cal-legend">
                                                    <span><i></i>Ditutup / penuh</span>
                                                </div>
                                            </div>

                                            <p class="nss-note" aria-live="polite">
                                                <span x-text="form.dates.length"></span> tanggal dipilih
                                            </p>
                                        </div>

                                        <div class="nss-schedule-list">
                                            <template x-for="date in form.dates" :key="date">
                                                <fieldset class="nss-schedule-card">
                                                    <legend x-text="formatDateLong(date)"></legend>
                                                    <div class="nss-time-fields">
                                                        <div>
                                                            <label :for="'arrival-' + date" class="nss-label">Jam mulai</label>
                                                            <select :id="'arrival-' + date"
                                                                :name="'arrival_times[' + date + ']'"
                                                                x-model="form.schedules[date].arrival_time"
                                                                @change="if (form.booking_type === 'open_space' && form.schedules[date].departure_time && form.schedules[date].departure_time <= form.schedules[date].arrival_time) form.schedules[date].departure_time = ''; checkAvailability(date)"
                                                                required class="nss-field">
                                                                <option value="">Pilih jam</option>
                                                                <template x-for="time in (form.booking_type === 'room' ? venueStartTimeOptions() : arrivalTimes)" :key="time">
                                                                    <option :value="time" x-text="time"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div x-show="form.booking_type === 'open_space'">
                                                            <label :for="'departure-' + date" class="nss-label">Jam selesai</label>
                                                            <select :id="'departure-' + date"
                                                                :name="'departure_times[' + date + ']'"
                                                                x-model="form.schedules[date].departure_time"
                                                                @change="checkAvailability(date)"
                                                                :disabled="form.booking_type !== 'open_space'"
                                                                required class="nss-field">
                                                                <option value="">Pilih jam</option>
                                                                <template x-for="time in timeOptions" :key="time">
                                                                    <option :value="time" :disabled="form.schedules[date].arrival_time && time <= form.schedules[date].arrival_time" x-text="time"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div x-show="form.booking_type === 'room'" x-cloak>
                                                            <label :for="'venue-departure-' + date" class="nss-label">Jam selesai sesuai paket</label>
                                                            <input :id="'venue-departure-' + date"
                                                                type="text"
                                                                :value="venueDepartureTime(date)"
                                                                placeholder="--:--"
                                                                readonly
                                                                class="nss-field bg-gray-50">
                                                            <input type="hidden"
                                                                :name="'departure_times[' + date + ']'"
                                                                :value="venueDepartureTime(date)"
                                                                :disabled="form.booking_type !== 'room'">
                                                        </div>
                                                    </div>
                                                    <p x-show="form.booking_type === 'room' && availability[date]?.status === 'checking'"
                                                        role="status" aria-live="polite" class="nss-note">
                                                        Memeriksa ketersediaan jadwal...
                                                    </p>
                                                    <p x-show="form.booking_type === 'room' && availability[date]?.status === 'available'"
                                                        role="status" aria-live="polite" class="nss-note text-green-700">
                                                        Jadwal tersedia.
                                                    </p>
                                                    <div x-show="form.booking_type === 'room' && ['unavailable', 'error'].includes(availability[date]?.status)"
                                                        role="alert" class="nss-field-error">
                                                        <span x-text="availability[date]?.message"></span>
                                                        <button type="button" x-show="availability[date]?.status === 'error'"
                                                            @click="checkAvailability(date)" class="underline">
                                                            Coba periksa lagi
                                                        </button>
                                                    </div>
                                                </fieldset>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="nss-attendee-panel">
                                        <label for="visitor_count" class="nss-label">Jumlah orang</label>
                                        <div x-show="form.booking_type === 'room' && minimumAttendees() > visitorCapacity()" x-cloak class="nss-capacity-warning" role="alert">
                                            Venue ini belum dapat dipesan karena kapasitasnya belum memenuhi minimum <span x-text="minimumAttendees()"></span> orang. Silakan hubungi admin.
                                        </div>
                                        <input
                                            type="number"
                                            id="visitor_count"
                                            name="visitor_count"
                                            :min="minimumAttendees()"
                                            :max="visitorCapacity()"
                                            :disabled="form.booking_type === 'room' && minimumAttendees() > visitorCapacity()"
                                            step="1"
                                            x-model.number="form.visitor_count"
                                            @change="checkAvailabilityForSelectedDates()"
                                            required
                                            class="nss-field"
                                        >
                                        <p class="nss-note" x-show="form.booking_type !== 'room' || minimumAttendees() <= visitorCapacity()">
                                            Minimal <span x-text="minimumAttendees()"></span> dan maksimal
                                            <span x-text="visitorCapacity()"></span> orang untuk tempat yang dipilih.
                                        </p>
                                        @error('visitor_count')
                                            <p class="nss-field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
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

                                        if ($product->image_url) {
                                            $localImage = $product->image_url;
                                        } elseif ($loop->index === 3 || str_contains($productNameLower, 'lays') || str_contains($productNameLower, 'chip')) {
                                            $localImage = asset('images/produk/lays.png');
                                        } elseif (str_contains($productNameLower, 'butter') || str_contains($productNameLower, 'croissant')) {
                                            $localImage = asset('images/produk/Butter.png');
                                        } elseif (str_contains($productNameLower, 'coffee') || str_contains($productNameLower, 'latte')) {
                                            $localImage = asset('images/produk/coffelate.png');
                                        } elseif (str_contains($productNameLower, 'mineral') || str_contains($productNameLower, 'water') || str_contains($productNameLower, 'aqua')) {
                                            $localImage = asset('images/produk/mineral.png');
                                        } else {
                                            $localImage = asset('images/produk/coffelate.png');
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
                                <div class="mt-6">
                                    <label for="facility_notes" class="nss-label">Fasilitas tambahan (opsional)</label>
                                    <textarea
                                        id="facility_notes"
                                        name="facility_notes"
                                        x-model="form.facility_notes"
                                        maxlength="1000"
                                        rows="3"
                                        class="nss-field"
                                        placeholder="Tuliskan fasilitas tambahan yang Anda perlukan"
                                    >{{ old('facility_notes') }}</textarea>
                                    @error('facility_notes')
                                        <p class="nss-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- ============ LANGKAH 4: DATA PEMESAN ============ -->
                            <div x-show="step === 4" x-cloak class="nss-fields"
                                x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to">
                                <div>
                                    <label for="customer_name" class="nss-label">Nama lengkap</label>
                                    <input type="text" id="customer_name" name="customer_name" x-model="form.customer_name" required
                                        autocomplete="name" class="nss-field" placeholder="Nama sesuai identitas">
                                </div>
                                <div x-show="form.booking_type === 'room'" x-cloak>
                                    <label for="institution" class="nss-label">Instansi</label>
                                    <input type="text" id="institution" name="institution"
                                        x-model="form.institution"
                                        :required="form.booking_type === 'room'"
                                        maxlength="255"
                                        autocomplete="organization"
                                        class="nss-field"
                                        placeholder="Nama perusahaan, komunitas, atau instansi">
                                    <p class="nss-note">Wajib diisi untuk pemesanan venue.</p>
                                    @error('institution')
                                        <p class="nss-field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="customer_phone" class="nss-label">Nomor WhatsApp</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" x-model="form.customer_phone" required
                                        autocomplete="tel" inputmode="tel"
                                        pattern="^(\+62|62|0)8[0-9]{8,12}$" title="Contoh: 081234567890"
                                        class="nss-field" placeholder="08xxxxxxxxxx">
                                    <p class="nss-note">Gunakan nomor WhatsApp yang aktif.</p>
                                </div>
                                <div>
                                    <label for="customer_email" class="nss-label">Email</label>
                                    <input type="email" id="customer_email" name="customer_email"
                                        x-model="form.customer_email" required
                                        autocomplete="email" class="nss-field"
                                        placeholder="nama@email.com">
                                </div>

                                {{-- ═══ UPLOAD KTM (KHUSUS MAHASISWA) ═══ --}}
                                <div x-show="form.booking_type === 'open_space' && form.category === 'mahasiswa'" x-cloak
                                    x-transition:enter="nss-enter" x-transition:enter-start="nss-enter-from" x-transition:enter-end="nss-enter-to"
                                    x-data="{ ktmFileName: '' }">

                                    <p class="nss-label">
                                        Upload KTM <span style="color:#b91c1c" aria-label="wajib">*</span>
                                    </p>

                                    <div class="nss-upload-box" :class="{ 'has-file': ktmFileName, 'has-error': ktmFileError }">
                                        {{-- x-ref="ktmInput" dipakai tombol hapus untuk mereset input file --}}
                                        <input type="file"
                                            name="ktm_file"
                                            id="ktm_file"
                                            x-ref="ktmInput"
                                            class="nss-upload-input"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            :aria-invalid="ktmFileError ? 'true' : 'false'"
                                            aria-describedby="ktm-file-error"
                                            @change="ktmFileName = $event.target.files[0]?.name || ''; ktmFileError = ''"
                                            aria-label="Upload file KTM">

                                        <div x-show="!ktmFileName">
                                            <div class="nss-upload-icon">🪪</div>
                                            <p class="nss-upload-label">Klik untuk pilih file KTM</p>
                                            <p class="nss-upload-hint">Format: JPG, PNG, atau PDF · Maks. 2 MB</p>
                                        </div>

                                        <div x-show="ktmFileName" x-cloak>
                                            <div class="nss-upload-icon">✅</div>
                                            <p class="nss-upload-label">File dipilih</p>
                                        </div>
                                    </div>

                                    <p id="ktm-file-error" class="nss-ktm-error" x-show="ktmFileError" x-cloak role="alert" x-text="ktmFileError"></p>

                                    <div class="nss-file-preview" x-show="ktmFileName" x-cloak>
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="nss-file-preview-name" x-text="ktmFileName"></span>
                                        <button type="button" class="nss-file-preview-clear"
                                                aria-label="Hapus file"
                                                @click="ktmFileName = ''; $refs.ktmInput.value = ''; ktmFileError = ''">✕</button>
                                    </div>

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
                    <div class="nss-pass" :class="{ 'nss-pass--tariff': step === 1 }">
                        <div class="nss-pass-body">
                            <p class="nss-pass-title font-headline" x-text="form.booking_type === 'room' ? 'Pemesanan Venue' : ' Needs Study Space'" style="color:#b91c1c"></p>
                            <h2 class="nss-display nss-pass-name" x-text="form.booking_type === 'room' ? (selectedRoom()?.name || 'Pilih venue') : 'Study Space day pass'"></h2>

                            <dl class="nss-rows">
                                <div class="nss-row">
                                    <dt>Tarif</dt>
                                    <dd x-text="categoryName()"></dd>
                                </div>
                                <div class="nss-row" x-show="form.dates.length">
                                    <dt>Tanggal</dt>
                                    <dd class="nss-summary-dates">
                                        <template x-for="date in form.dates" :key="date">
                                            <span class="nss-summary-date">
                                                <span class="nss-summary-date-label" x-text="formatDateLong(date)"></span>
                                                <span class="nss-summary-date-time" x-text="form.schedules[date]?.arrival_time && displayedDepartureTime(date) ? form.schedules[date].arrival_time + '–' + displayedDepartureTime(date) : ''"></span>
                                            </span>
                                        </template>
                                    </dd>
                                </div>
                            </dl>

                            <div class="nss-divider"></div>

                            <dl class="nss-rows nss-pass-breakdown" x-show="step !== 1">
                                <div class="nss-row" x-show="form.booking_type === 'open_space'">
                                    <dt>
                                        Open Space
                                        (<span x-text="form.visitor_count || 0"></span> orang ×
                                        <span x-text="form.dates.length"></span> tanggal)
                                    </dt>
                                    <dd style="font-weight: 400;">Rp<span x-text="formatPrice(pricePerDay() * (form.visitor_count || 0) * form.dates.length)"></span></dd>
                                </div>
                                <div class="nss-row" x-show="form.booking_type === 'room'">
                                    <dt>
                                        <span x-text="selectedPackage()?.label || 'Paket venue'"></span>
                                        · <span x-text="form.visitor_count || 0"></span> orang ×
                                        <span x-text="form.dates.length"></span> tanggal
                                    </dt>
                                    <dd style="font-weight: 400;">Rp<span x-text="formatPrice(venueSubtotal())"></span></dd>
                                </div>
                                <div class="nss-row" x-show="form.booking_type === 'room'">
                                    <dt>Service Charge ({{ $venueServiceChargePercent }}%)</dt>
                                    <dd style="font-weight: 400;">Rp<span x-text="formatPrice(venueServiceCharge())"></span></dd>
                                </div>
                                <template x-for="item in cart()" :key="item.id">
                                    <div class="nss-row">
                                        <dt><span x-text="item.name"></span> &times; <span x-text="item.qty"></span></dt>
                                        <dd style="font-weight: 400;">Rp<span x-text="formatPrice(item.subtotal)"></span></dd>
                                    </div>
                                </template>
                            </dl>
                        </div>

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
@php
    $bookingRooms = $rooms->map(fn ($room) => [
        'id' => (string) $room->id,
        'name' => $room->name,
        'capacity' => (int) $room->capacity,
        'minimum_attendees' => $room->minimumAttendees(),
    ])->values();
@endphp
function bookingWizard() {
    const PRICES = { mahasiswa: 35000, umum: 55000 };
    const NAMES = { mahasiswa: 'Mahasiswa', umum: 'Umum' };
    const VENUES = @json($bookingRooms);
    const VENUE_PACKAGES = @json($venuePackages);
    const VENUE_SERVICE_CHARGE_PERCENT = {{ (float) $venueServiceChargePercent }};
    const BOOKING_CHECK_URL = @json(route('booking.check'));
    const OPEN_SPACE_CAPACITY = {{ (int) config('booking.open_space_capacity', 50) }};
    const TITLES = ['Pilih tempat', 'Pilih tanggal dan jadwal', 'Snack dan minuman', 'Data pemesan'];
    const TIME_OPTIONS = Array.from({ length: 29 }, (_, index) => {
        const minutes = 480 + (index * 30);
        return `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
    });
    const INTROS = [
        'Pilih Open Space atau venue privat yang ingin dipesan.',
        'Pilih tanggal, paket, dan jadwal penggunaan.',
        'Opsional. Boleh dilewati.',
        'Isi data untuk pesanan ini.'
    ];
    const HOME_URL = '{{ url('/') }}';
    const catalog = @json($catalog);
    const oldProducts = @json(old('products', []));
    const oldCategory = @json(old('visitor_category', request('tarif', 'mahasiswa')));
    const oldDuration = parseInt(@json(old('duration', 1)), 10);
    const oldDate = @json(old('date', ''));
    const oldRoomId = @json((string) old('room_id', ''));
    const oldVenuePackage = @json(old('venue_package', ''));
    const oldArrivalTimes = @json(old('arrival_times', []));
    const oldDepartureTimes = @json(old('departure_times', []));
    const legacyArrivalTime = @json(old('arrival_time', ''));
    const legacyDepartureTime = @json(old('departure_time', ''));
    const restored = @json((bool) old('visitor_category') || (bool) old('room_id'));
    @php
        $timeValidationFailed = $errors->hasAny([
            'date',
            'dates',
            'visitor_count',
            'venue_package',
            'arrival_time',
            'departure_time',
            'arrival_times',
            'departure_times',
        ]);
    @endphp
    const timeValidationFailed = @json($timeValidationFailed || (session()->has('error') && old('room_id')));

    return {
        step: timeValidationFailed ? 2 : (restored ? 4 : 1),
        isSubmitting: false,
        ktmFileError: '',
        bumped: false,
        area: 0,
        galTimer: null,   // timer slideshow galeri foto
        galCount: 12,     // total foto galeri
        touchX: 0,
        areaCount: 1, // Disesuaikan menjadi 1 untuk open space statis
        labels: ['Tarif', 'Tanggal', 'Tambahan', 'Pemesan'],
        timeOptions: TIME_OPTIONS,
        arrivalTimes: TIME_OPTIONS.slice(0, -1),

        // Kalender & tanggal ditutup
        blocked: [],
        availability: {},
        availabilityRequestIds: {},
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),

        form: {
            booking_type: oldRoomId ? 'room' : 'open_space',
            room_id: oldRoomId,
            venue_package: oldVenuePackage,
            category: PRICES[oldCategory] ? oldCategory : 'mahasiswa',
            dates: @json(old('dates', [])),
            schedules: {},
            visitor_count: @json(old('visitor_count', '')),
            customer_name: @json(old('customer_name', '')),
            institution: @json(old('institution', '')),
            customer_phone: @json(old('customer_phone', '')),
            customer_email: @json(old('customer_email', '')),
            facility_notes: @json(old('facility_notes', '')),
            products: {}
        },

        init() {
            // Normalisasi: ambil 10 karakter pertama (YYYY-MM-DD), jaga-jaga kalau formatnya datetime
            this.blocked = JSON.parse(document.getElementById('booking-wizard').dataset.blocked || '[]')
                .map(d => String(d).slice(0, 10));
            if (!Array.isArray(this.form.dates)) this.form.dates = [];
            if (this.form.dates.length === 0 && oldDate) {
                const startDate = new Date(oldDate + 'T00:00:00');
                const count = Number.isInteger(oldDuration) && oldDuration > 0 && oldDuration <= 30 ? oldDuration : 1;
                for (let offset = 0; offset < count; offset++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + offset);
                    this.form.dates.push(this.toStr(date));
                }
            }
            this.form.dates = [...new Set(this.form.dates)].sort();
            this.form.dates.forEach(date => {
                this.form.schedules[date] = {
                    arrival_time: oldArrivalTimes[date] || legacyArrivalTime,
                    departure_time: oldDepartureTimes[date] || legacyDepartureTime
                };
            });
            if (this.form.dates.length) {
                const [y, m] = this.form.dates[0].split('-').map(Number);
                this.viewYear = y; this.viewMonth = m - 1;
            }

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
            this.checkAvailabilityForSelectedDates();
        },

        moveArea(delta, focusTab = false) {
            if (!this.areaCount) return;
            this.area = (this.area + delta + this.areaCount) % this.areaCount;
            if (focusTab) this.$nextTick(() => document.getElementById('area-tab-' + this.area)?.focus());
        },
        // Swipe galeri: geser kiri/kanan pindah foto
        swipe(endX) {
            const dx = endX - this.touchX;
            if (Math.abs(dx) > 45) this.galJump((this.area + (dx < 0 ? 1 : -1) + this.galCount) % this.galCount);
        },
        // Klik dot: loncat ke foto tertentu, reset timer
        galJump(idx) {
            this.area = idx;
            clearInterval(this.galTimer);
            this.galTimer = setInterval(() => { this.area = (this.area + 1) % this.galCount; }, 3000);
        },

        stepTitle() { return TITLES[this.step - 1]; },
        stepIntro() {
            if (this.step === 2) {
                return this.form.booking_type === 'room'
                    ? 'Pilih paket sewa, tanggal, dan jam mulai untuk setiap tanggal.'
                    : 'Pilih tanggal dan jam penggunaan untuk setiap tanggal.';
            }

            return INTROS[this.step - 1];
        },

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
        submitBooking(event) {
            const ktmInput = event.target.elements.namedItem('ktm_file');
            if (this.form.booking_type === 'open_space' && this.form.category === 'mahasiswa' && ktmInput.files.length === 0) {
                event.preventDefault();
                this.ktmFileError = 'Silakan pilih file KTM sebelum melanjutkan pembayaran.';
                this.$nextTick(() => {
                    document.getElementById('ktm-file-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                return;
            }

            this.ktmFileError = '';
            this.isSubmitting = true;
        },

        // ── Helper tanggal (waktu lokal, bukan UTC) ─────────────────────
        pad(n) { return String(n).padStart(2, '0'); },
        toStr(d) { return d.getFullYear() + '-' + this.pad(d.getMonth() + 1) + '-' + this.pad(d.getDate()); },
        todayStr() { return this.toStr(new Date()); },

        // ── Kalender kustom ─────────────────────────────────────────────
        monthLabel() {
            return new Date(this.viewYear, this.viewMonth, 1)
                .toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        },
        canPrev() {
            const now = new Date();
            return this.viewYear > now.getFullYear() ||
                   (this.viewYear === now.getFullYear() && this.viewMonth > now.getMonth());
        },
        prevMonth() {
            if (!this.canPrev()) return;
            if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; } else this.viewMonth--;
        },
        nextMonth() {
            if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; } else this.viewMonth++;
        },
        calendarCells() {
            const first = new Date(this.viewYear, this.viewMonth, 1).getDay();
            const total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const today = this.todayStr();
            const cells = Array(first).fill(null);
            for (let day = 1; day <= total; day++) {
                const str = this.viewYear + '-' + this.pad(this.viewMonth + 1) + '-' + this.pad(day);
                const past = str < today;
                const blocked = this.blocked.includes(str);
                cells.push({ day, str, past, blocked, disabled: past || blocked });
            }
            return cells;
        },
        toggleDate(str) {
            if (str < this.todayStr() || this.blocked.includes(str)) return;
            if (this.form.dates.includes(str)) {
                this.form.dates = this.form.dates.filter(date => date !== str);
                delete this.form.schedules[str];
                this.availabilityRequestIds[str] = (this.availabilityRequestIds[str] || 0) + 1;
                delete this.availability[str];
                return;
            }
            if (this.form.dates.length >= 30) return;
            this.form.dates = [...this.form.dates, str].sort();
            this.form.schedules[str] = { arrival_time: '', departure_time: '' };
            this.checkAvailability(str);
        },

        checkAvailabilityForSelectedDates() {
            if (this.form.booking_type !== 'room') return;
            this.form.dates.forEach(date => this.checkAvailability(date));
        },
        async checkAvailability(date) {
            const requestId = (this.availabilityRequestIds[date] || 0) + 1;
            this.availabilityRequestIds[date] = requestId;

            const schedule = this.form.schedules[date];
            const departureTime = this.venueDepartureTime(date);
            const visitorCount = Number(this.form.visitor_count);
            if (this.form.booking_type !== 'room'
                || !this.selectedRoom()
                || !this.selectedPackage()
                || !schedule?.arrival_time
                || !departureTime
                || !Number.isInteger(visitorCount)
                || visitorCount < this.minimumAttendees()
                || visitorCount > this.visitorCapacity()) {
                this.availability[date] = { status: 'idle', message: '' };
                return false;
            }

            this.availability[date] = { status: 'checking', message: '' };

            try {
                const response = await fetch(BOOKING_CHECK_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('#bookingForm input[name="_token"]')?.value || ''
                    },
                    body: JSON.stringify({
                        room_id: Number(this.form.room_id),
                        venue_package: this.form.venue_package,
                        dates: [date],
                        arrival_times: { [date]: schedule.arrival_time },
                        departure_times: { [date]: departureTime },
                        visitor_count: visitorCount
                    })
                });
                const result = await response.json();
                if (!response.ok) {
                    const validationMessage = Object.values(result.errors || {})
                        .flat()
                        .find(message => typeof message === 'string');
                    throw new Error(validationMessage || result.message || 'Pemeriksaan ketersediaan gagal.');
                }
                if (requestId !== this.availabilityRequestIds[date]) return false;

                const isAvailable = result.available === true;
                this.availability[date] = {
                    status: isAvailable ? 'available' : 'unavailable',
                    message: result.message || (isAvailable ? 'Jadwal tersedia.' : 'Jadwal sudah terisi.')
                };

                return isAvailable;
            } catch (error) {
                if (requestId !== this.availabilityRequestIds[date]) return false;

                this.availability[date] = {
                    status: 'error',
                    message: error instanceof Error
                        ? `Tidak dapat memeriksa ketersediaan: ${error.message}`
                        : 'Tidak dapat memeriksa ketersediaan. Silakan coba lagi.'
                };

                return false;
            }
        },
        selectedRoom() {
            return VENUES.find(room => String(room.id) === String(this.form.room_id)) || null;
        },
        selectRoom(roomId) {
            const room = VENUES.find(item => String(item.id) === String(roomId));
            if (!room || room.capacity < room.minimum_attendees) return;

            this.form.room_id = String(roomId);
            if (this.form.visitor_count > this.visitorCapacity()) this.form.visitor_count = '';
            this.checkAvailabilityForSelectedDates();
        },
        selectedPackage() {
            return VENUE_PACKAGES[this.form.venue_package] || null;
        },
        minimumAttendees() {
            return this.form.booking_type === 'room'
                ? (this.selectedRoom()?.minimum_attendees || 1)
                : 1;
        },
        visitorCapacity() {
            return this.form.booking_type === 'room'
                ? (this.selectedRoom()?.capacity || 0)
                : OPEN_SPACE_CAPACITY;
        },
        firstBlockedSelectedDate() {
            return this.form.dates.find(date => this.blocked.includes(date)) || null;
        },

        canNext() {
            if (this.step === 1) {
                return this.form.booking_type === 'open_space'
                    ? !!this.form.category
                    : this.form.booking_type === 'room'
                        && !!this.selectedRoom()
                        && this.selectedRoom().capacity >= this.selectedRoom().minimum_attendees;
            }
            if (this.step === 2) {
                if (!this.form.dates.length || this.form.dates.length > 30) return false;
                if (this.form.booking_type === 'room' && !this.selectedPackage()) return false;
                if (this.form.booking_type === 'room' && this.minimumAttendees() > this.visitorCapacity()) return false;
                if (!Number.isInteger(this.form.visitor_count) || this.form.visitor_count < this.minimumAttendees() || this.form.visitor_count > this.visitorCapacity()) return false;
                if (!this.form.dates.every(date => {
                    const schedule = this.form.schedules[date];
                    return schedule
                        && /^([01]\d|2[0-3]):[0-5]\d$/.test(schedule.arrival_time)
                        && (this.form.booking_type === 'room'
                            ? !!this.venueDepartureTime(date)
                            : /^([01]\d|2[0-3]):[0-5]\d$/.test(schedule.departure_time)
                                && schedule.departure_time > schedule.arrival_time);
                })) return false;
                if (this.firstBlockedSelectedDate()) return false;
                if (this.form.booking_type === 'room'
                    && !this.form.dates.every(date => this.availability[date]?.status === 'available')) return false;
                return true;
            }
            return true;
        },
        hint() {
            if (this.step === 2) {
                if (!this.form.dates.length) return 'Pilih minimal satu tanggal untuk lanjut';
                if (this.form.booking_type === 'room' && !this.selectedPackage()) return 'Pilih paket sewa venue';
                if (this.form.booking_type === 'room' && this.minimumAttendees() > this.visitorCapacity()) {
                    return 'Venue ini belum dapat dipesan. Kapasitasnya belum memenuhi minimum pemesanan.';
                }
                if (!Number.isInteger(this.form.visitor_count) || this.form.visitor_count < this.minimumAttendees()) {
                    return `Minimum pemesanan untuk tempat ini ${this.minimumAttendees()} orang`;
                }
                if (this.form.visitor_count > this.visitorCapacity()) {
                    return `Maksimal pemesanan untuk tempat ini ${this.visitorCapacity()} orang`;
                }
                const incompleteSchedule = this.form.dates.find(date => {
                    const schedule = this.form.schedules[date];
                    return !schedule?.arrival_time || (this.form.booking_type === 'open_space'
                        ? !schedule?.departure_time
                        : !this.venueDepartureTime(date));
                });
                if (incompleteSchedule) return this.form.booking_type === 'room'
                    ? 'Pilih jam mulai yang sesuai dengan durasi paket untuk setiap tanggal'
                    : 'Pilih jam mulai dan selesai untuk setiap tanggal';
                const bad = this.firstBlockedSelectedDate();
                if (bad) return 'Tanggal ' + this.formatDate(bad) + ' sudah ditutup. Hapus tanggal itu atau pilih tanggal lain.';
            }
            return '';
        },
        nextLabel() {
            if (this.step === 3) return this.cartCount() === 0 ? 'Lewati' : 'Lanjutkan';
            return 'Lanjutkan';
        },

        pricePerDay() { return PRICES[this.form.category] || PRICES.mahasiswa; },
        packagePricePerPerson() { return this.selectedPackage()?.price_per_person || 0; },
        venueSubtotal() {
            return this.packagePricePerPerson() * (this.form.visitor_count || 0) * this.form.dates.length;
        },
        venueServiceCharge() {
            return Math.round(this.venueSubtotal() * VENUE_SERVICE_CHARGE_PERCENT / 100);
        },
        venueStartTimeOptions() {
            const durationHours = this.selectedPackage()?.duration_hours || 0;
            return this.arrivalTimes.filter(time => {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes + durationHours * 60 <= 22 * 60;
            });
        },
        venueDepartureTime(date) {
            const startTime = this.form.schedules[date]?.arrival_time;
            const durationHours = this.selectedPackage()?.duration_hours;
            if (!startTime || !durationHours) return '';

            const [hours, minutes] = startTime.split(':').map(Number);
            const endMinutes = hours * 60 + minutes + durationHours * 60;
            if (endMinutes > 22 * 60) return '';

            return `${String(Math.floor(endMinutes / 60)).padStart(2, '0')}:${String(endMinutes % 60).padStart(2, '0')}`;
        },
        displayedDepartureTime(date) {
            return this.form.booking_type === 'room'
                ? this.venueDepartureTime(date)
                : (this.form.schedules[date]?.departure_time || '');
        },
        normalizeVenueStartTimes() {
            this.form.dates.forEach(date => {
                if (!this.venueStartTimeOptions().includes(this.form.schedules[date]?.arrival_time)) {
                    this.form.schedules[date].arrival_time = '';
                }
            });
        },
        categoryName() {
            return this.form.booking_type === 'room'
                ? (this.selectedPackage()?.label || 'Pilih paket')
                : (NAMES[this.form.category] || '');
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
            const basePrice = this.form.booking_type === 'room'
                ? this.venueSubtotal() + this.venueServiceCharge()
                : this.pricePerDay() * (this.form.visitor_count || 0) * this.form.dates.length;

            return basePrice
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
        },
        formatDateLong(value) {
            if (!value) return '-';
            return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });
        }
    }
}
</script>
@endsection