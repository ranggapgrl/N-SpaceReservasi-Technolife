@extends('admin.layouts.app')

@section('page_title', 'Edit Ruangan')

@section('content')

    <div style="--color-n-primary: #B1002C; --color-n-primary-dark: #9F0028; --color-n-bg: #FBF9F8; --color-n-charcoal: #1B1C1C; --color-n-gray: #5C3F3F; --color-n-border: #EAEAEA; font-family: Inter, sans-serif;">
    <div class="max-w-2xl">
        <a href="{{ route('admin.rooms.index') }}" class="inline-flex items-center text-n-gray text-sm hover:text-n-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>

        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 md:p-8">
            <h2 class="text-lg font-semibold mb-6">Edit: {{ $room->name }}</h2>

            <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
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
                    <input type="text" name="type" value="{{ old('type', $room->type) }}"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('type') border-red-400 @enderror">
                    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="4"
                            class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition resize-none @error('description') border-red-400 @enderror">{{ old('description', $room->description) }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Capacity --}}
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-1.5">Kapasitas (orang) <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1"
                        class="w-full border border-n-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-n-primary/30 focus:border-n-primary transition @error('capacity') border-red-400 @enderror">
                    @error('capacity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <p class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-n-charcoal">
                    Minimum order otomatis: Meeting Room 5 orang, Training Room 20 orang.
                    Tarif venue:
                    @foreach(config('booking.venue_packages') as $package)
                        {{ $package['label'] }} {{ $package['duration_hours'] }} jam Rp{{ number_format($package['price_per_person'], 0, ',', '.') }}/orang{{ $loop->last ? '' : ',' }}
                    @endforeach
                    + Service Charge {{ config('booking.venue_service_charge_percent') }}%.
                </p>

                {{-- Gambar lama tetap terlihat dan tidak berubah jika file baru tidak dipilih. --}}
                @include('admin.rooms._image_fields', ['room' => $room])

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
                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center bg-n-primary px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-n-primary/90 sm:w-auto">
                        Perbarui Ruangan
                    </button>
                    <a href="{{ route('admin.rooms.index') }}"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-n-border px-6 py-2.5 text-center text-sm font-medium text-n-gray transition-colors hover:bg-n-bg sm:w-auto">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection
