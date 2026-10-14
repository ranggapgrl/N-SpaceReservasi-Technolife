@extends('admin.layouts.app')

@section('page_title', 'Tambah Produk')

@section('content')

    <div class="max-w-2xl">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center text-n-gray text-sm hover:text-n-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>

        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 md:p-8">
            <h2 class="text-lg font-semibold mb-6">Informasi Produk</h2>

            <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('name') border-red-400 @enderror"
                        placeholder="Contoh: Kopi Arabika">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition resize-none @error('description') border-red-400 @enderror"
                            placeholder="Deskripsi singkat produk...">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('price') border-red-400 @enderror"
                        placeholder="25000">
                    @error('price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Image URL --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">URL Gambar <span class="text-n-gray text-xs">(opsional)</span></label>
                    <input type="url" name="image" value="{{ old('image') }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('image') border-red-400 @enderror"
                        placeholder="https://...">
                    @error('image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Is Active --}}
                <div class="flex items-center space-x-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="w-4 h-4 accent-n-primary rounded">
                    <label for="is_active" class="text-sm font-medium text-n-charcoal cursor-pointer">Produk Aktif</label>
                </div>

                {{-- Submit --}}
                <div class="flex items-center space-x-3 pt-2">
                    <button type="submit"
                            class="bg-n-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-n-primary/90 transition-colors">
                        Simpan Produk
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                    class="px-6 py-2.5 rounded-xl text-sm font-medium border border-n-border text-n-gray hover:bg-n-bg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
