@php
    $isEditing = $product !== null;
    $productImageUrl = $product?->image_url;
@endphp

<style>
    .product-form-page h2 {
        font-family: Poppins, sans-serif;
    }
</style>

{{-- Warna brand diterapkan lokal agar halaman produk tidak mengubah design system global. --}}
<div class="product-form-page mx-auto max-w-5xl pb-8"
     style="--color-n-primary: #B1002C; --color-n-primary-dark: #9F0028; --color-n-bg: #FBF9F8; --color-n-charcoal: #1B1C1C; --color-n-gray: #5C3F3F; --color-n-border: #EAEAEA; font-family: Inter, sans-serif;">
    <a href="{{ route('admin.products.index') }}"
       class="mb-6 inline-flex min-h-10 items-center gap-2 rounded-lg text-sm font-medium text-n-gray transition-colors hover:text-n-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke daftar produk
    </a>

    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-n-primary">Katalog produk</p>
        <h2 class="mt-1 text-2xl font-semibold text-n-charcoal">{{ $isEditing ? 'Perbarui produk' : 'Tambah produk' }}</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-n-gray">
            Isi informasi yang akan dilihat pelanggan. Gambar bersifat opsional dan bisa diganti kapan saja.
        </p>
    </div>

    <form action="{{ $isEditing ? route('admin.products.update', $product) : route('admin.products.store') }}"
          method="POST"
          enctype="multipart/form-data"
          x-data="{
              previewUrl: @js($productImageUrl),
              selectedFileName: '',
              previewSelectedImage(event) {
                  const file = event.target.files?.[0];
                  if (!file) return;
                  if (this.previewUrl?.startsWith('blob:')) URL.revokeObjectURL(this.previewUrl);
                  this.previewUrl = URL.createObjectURL(file);
                  this.selectedFileName = file.name;
              }
          }"
          class="overflow-hidden rounded-xl border border-n-border bg-white">
        @csrf
        @if($isEditing)
            @method('PUT')
        @endif

        <div class="grid gap-8 p-5 sm:p-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <section aria-labelledby="product-details-heading" class="space-y-5">
                <div>
                    <h3 id="product-details-heading" class="text-base font-semibold text-n-charcoal">Informasi produk</h3>
                    <p class="mt-1 text-sm text-n-gray">Nama, deskripsi, dan harga yang tampil di katalog.</p>
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-n-charcoal">
                        Nama produk <span class="text-n-primary" aria-hidden="true">*</span>
                    </label>
                    <input id="name" type="text" name="name" required maxlength="255"
                           value="{{ old('name', $product?->name) }}"
                           autocomplete="off"
                           class="min-h-11 w-full rounded-lg border border-n-border bg-white px-3.5 text-sm text-n-charcoal outline-none transition focus:border-n-primary focus:ring-2 focus:ring-n-primary/15 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-n-charcoal">Deskripsi</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full resize-y rounded-lg border border-n-border bg-white px-3.5 py-3 text-sm leading-6 text-n-charcoal outline-none transition focus:border-n-primary focus:ring-2 focus:ring-n-primary/15 @error('description') border-red-500 @enderror"
                              placeholder="Contoh: Kopi susu dengan rasa lembut, disajikan dingin.">{{ old('description', $product?->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="max-w-sm">
                    <label for="price" class="mb-2 block text-sm font-semibold text-n-charcoal">
                        Harga <span class="text-n-primary" aria-hidden="true">*</span>
                    </label>
                    <div class="flex min-h-11 overflow-hidden rounded-lg border border-n-border bg-white focus-within:border-n-primary focus-within:ring-2 focus-within:ring-n-primary/15 @error('price') border-red-500 @enderror">
                        <span class="flex items-center border-r border-n-border bg-n-bg px-3.5 text-sm font-medium text-n-gray">Rp</span>
                        <input id="price" type="number" name="price" required min="0" step="0.01"
                               value="{{ old('price', $product?->price) }}"
                               class="w-full min-w-0 border-0 bg-transparent px-3.5 text-sm text-n-charcoal outline-none"
                               placeholder="25000">
                    </div>
                    @error('price')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-n-border pt-5">
                    <input type="hidden" name="is_active" value="0">
                    <label for="is_active" class="flex cursor-pointer items-start gap-3">
                        <input id="is_active" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 rounded border-n-border accent-[#B1002C] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
                        <span>
                            <span class="block text-sm font-semibold text-n-charcoal">Tampilkan sebagai produk aktif</span>
                            <span class="mt-1 block text-sm leading-5 text-n-gray">Produk nonaktif tidak muncul di pilihan pelanggan.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section aria-labelledby="product-image-heading" class="min-w-0 lg:border-l lg:border-n-border lg:pl-8">
                <h3 id="product-image-heading" class="text-base font-semibold text-n-charcoal">Gambar produk</h3>
                <p class="mt-1 text-sm leading-5 text-n-gray">Pilih foto agar pelanggan lebih mudah mengenali produk.</p>

                <div class="mt-4 flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg border border-n-border bg-n-bg">
                    <img x-show="previewUrl" x-cloak :src="previewUrl" alt="Pratinjau gambar produk"
                         class="h-full w-full object-contain p-2">
                    <div x-show="!previewUrl" class="flex flex-col items-center px-5 text-center text-n-gray">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5l4.5-4.5 3 3L15 10.5l6 6M4.5 3h15A1.5 1.5 0 0121 4.5v15a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 19.5v-15A1.5 1.5 0 014.5 3z"/>
                        </svg>
                        <span class="mt-3 text-sm">Pratinjau gambar akan muncul di sini</span>
                    </div>
                </div>

                <label for="image" class="mb-2 mt-4 block text-sm font-semibold text-n-charcoal">Pilih file gambar</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                       @change="previewSelectedImage($event)"
                       class="block min-h-11 w-full cursor-pointer rounded-lg border border-n-border bg-white text-sm text-n-charcoal file:mr-3 file:min-h-11 file:border-0 file:bg-n-bg file:px-3 file:font-semibold file:text-n-primary hover:file:bg-[#F4E7EA] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary @error('image') border-red-500 @enderror">
                <p x-show="selectedFileName" x-cloak class="mt-2 break-all text-sm font-medium text-n-charcoal" x-text="selectedFileName"></p>
                <p class="mt-2 text-xs leading-5 text-n-gray">JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</p>
                @if($isEditing)
                    <p class="mt-1 text-xs leading-5 text-n-gray">Biarkan kosong untuk tetap menggunakan gambar saat ini.</p>
                @endif
                @error('image')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </section>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-n-border bg-n-bg/60 px-5 py-4 sm:flex-row sm:justify-end sm:px-8">
            <a href="{{ route('admin.products.index') }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg border border-n-border bg-white px-5 text-sm font-semibold text-n-gray transition-colors hover:border-n-primary hover:text-n-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-n-primary px-5 text-sm font-semibold text-white transition-colors hover:bg-n-primary-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
                {{ $isEditing ? 'Simpan perubahan' : 'Simpan produk' }}
            </button>
        </div>
    </form>
</div>
