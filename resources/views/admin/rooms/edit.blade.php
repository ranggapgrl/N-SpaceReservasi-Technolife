@extends('admin.layouts.app')

@section('page_title', 'Edit Ruangan')

@section('content')

    <div class="max-w-2xl">
        <a href="{{ route('admin.rooms.index') }}" class="inline-flex items-center text-n-gray text-sm hover:text-n-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>

        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 md:p-8">
            <h2 class="text-lg font-semibold mb-6">Edit: {{ $room->name }}</h2>

            <form action="{{ route('admin.rooms.update', $room) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Nama Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $room->name) }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Tipe <span class="text-red-500">*</span></label>
                    <input class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('type') border-red-400 @enderror">
                    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="4"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition resize-none @error('description') border-red-400 @enderror">{{ old('description', $room->description) }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Capacity & Price --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-n-charcoal mb-1.5">Kapasitas (orang) <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('capacity') border-red-400 @enderror">
                        @error('capacity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-n-charcoal mb-1.5">Harga / Jam (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price_per_hour" value="{{ old('price_per_hour', $room->price_per_hour) }}" min="0"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('price_per_hour') border-red-400 @enderror">
                        @error('price_per_hour') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Images --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">URL Gambar Utama</label>
                    <input type="url" name="image" value="{{ old('image', $room->image) }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('image') border-red-400 @enderror"
                        placeholder="https://...">
                    @error('image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">URL Gambar 2 <span class="text-n-gray text-xs">(opsional)</span></label>
                    <input type="url" name="image_2" value="{{ old('image_2', $room->image_2) }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('image_2') border-red-400 @enderror"
                        placeholder="https://...">
                    @error('image_2') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">URL Gambar 3 <span class="text-n-gray text-xs">(opsional)</span></label>
                    <input type="url" name="image_3" value="{{ old('image_3', $room->image_3) }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('image_3') border-red-400 @enderror"
                        placeholder="https://...">
                    @error('image_3') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Facilities --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Fasilitas <span class="text-n-gray text-xs">(satu per baris)</span></label>
                    <textarea name="facilities" rows="5"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition resize-none font-mono @error('facilities') border-red-400 @enderror"
                            >{{ old('facilities', is_array($room->facilities) ? implode("\n", $room->facilities) : $room->facilities) }}</textarea>
                    @error('facilities') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition bg-white @error('status') border-red-400 @enderror">
                        <option value="active"       {{ old('status', $room->status) === 'active'      ? 'selected' : '' }}>Aktif</option>
                        <option value="maintenance"  {{ old('status', $room->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="inactive"     {{ old('status', $room->status) === 'inactive'    ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Submit --}}
                <div class="flex items-center space-x-3 pt-2">
                    <button type="submit"
                            class="bg-n-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-n-primary/90 transition-colors">
                        Perbarui Ruangan
                    </button>
                    <a href="{{ route('admin.rooms.index') }}"
                    class="px-6 py-2.5 rounded-xl text-sm font-medium border border-n-border text-n-gray hover:bg-n-bg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
