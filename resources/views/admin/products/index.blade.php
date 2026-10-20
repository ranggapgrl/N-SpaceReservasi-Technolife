@extends('admin.layouts.app')

@section('page_title', 'Manajemen Produk')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <p class="text-n-gray text-sm">Total {{ $products->count() }} produk</p>
        <a href="{{ route('admin.products.create') }}"
        class="inline-flex items-center text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors hover:opacity-90" style="background-color: #b1002c;">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Produk
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-n-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-n-bg text-n-gray">
                    <tr>
                        <th class="px-6 py-4 font-medium">Nama Produk</th>
                        <th class="px-6 py-4 font-medium">Deskripsi</th>
                        <th class="px-6 py-4 font-medium">Harga</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-n-border text-n-charcoal">
                    @forelse($products as $product)
                    <tr class="hover:bg-n-bg/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                        class="w-10 h-10 rounded-lg object-cover border border-n-border flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-n-bg border border-n-border flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-n-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-medium">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-n-gray max-w-xs truncate">{{ $product->description ?? '-' }}</td>
                        <td class="px-6 py-4 font-medium">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_active)
                                <span class="bg-red-50 border border-red-200 text-[#b1002c] px-3 py-1 rounded-full text-xs font-semibold">Aktif</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-n-bg border border-n-border text-n-charcoal rounded-lg text-xs font-medium hover:border-[#b1002c] hover:text-[#b1002c] transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-n-gray">
                            Belum ada produk. <a href="{{ route('admin.products.create') }}" class="text-n-primary font-medium hover:underline">Tambah sekarang</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection