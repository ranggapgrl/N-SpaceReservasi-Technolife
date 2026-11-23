@php
    $imageUrl = $room?->image_url;
@endphp

<section
    x-data="{
        previewUrl: @js($imageUrl),
        selectedFileName: '',
        previewSelectedImage(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (this.previewUrl?.startsWith('blob:')) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = URL.createObjectURL(file);
            this.selectedFileName = file.name;
        }
    }"
    class="max-w-xl"
>
    <label for="image" class="mb-2 block text-sm font-medium text-n-charcoal">Foto Venue</label>

    <div class="mb-3 flex aspect-[16/9] items-center justify-center overflow-hidden rounded-xl border border-n-border bg-n-bg">
        <img x-show="previewUrl" x-cloak :src="previewUrl" alt="Pratinjau foto venue" class="h-full w-full object-cover">
        <span x-show="!previewUrl" class="px-3 text-center text-sm text-n-gray">Pratinjau foto venue</span>
    </div>

    <input
        id="image"
        type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        @change="previewSelectedImage($event)"
        class="block w-full cursor-pointer rounded-xl border border-n-border bg-white text-sm text-n-charcoal file:mr-3 file:border-0 file:bg-n-bg file:px-3 file:py-2.5 file:font-semibold file:text-n-primary @error('image') border-red-400 @enderror"
    >
    <p x-show="selectedFileName" x-cloak class="mt-2 break-all text-xs text-n-charcoal" x-text="selectedFileName"></p>
    <p class="mt-2 text-xs text-n-gray">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
    @if($room)
        <p class="mt-1 text-xs text-n-gray">Kosongkan jika foto saat ini ingin dipertahankan.</p>
    @endif
    @error('image')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</section>
