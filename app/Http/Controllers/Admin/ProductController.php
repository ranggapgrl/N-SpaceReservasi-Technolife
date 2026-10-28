<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        // Validasi jenis dan ukuran gambar dilakukan di server, bukan hanya lewat atribut input.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Checkbox yang tidak dicentang tidak dikirim browser, jadi status diubah menjadi boolean eksplisit.
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            // Simpan file ke storage publik dan simpan path-nya di kolom image.
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                // Jangan membuat produk dengan gambar yang gagal disimpan.
                return back()->withInput()->withErrors([
                    'image' => 'Gambar gagal disimpan. Silakan coba lagi.',
                ]);
            }

            $validated['image'] = $imagePath;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Checkbox yang tidak dicentang tidak dikirim browser, jadi status diubah menjadi boolean eksplisit.
        $validated['is_active'] = $request->boolean('is_active');

        // Simpan path lama agar file tersebut bisa dibersihkan setelah perubahan produk berhasil.
        $previousImage = $product->getRawOriginal('image');

        if ($request->hasFile('image')) {
            // File baru hanya menggantikan gambar lama setelah berhasil tersimpan.
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()->withInput()->withErrors([
                    'image' => 'Gambar gagal disimpan. Gambar lama tetap digunakan.',
                ]);
            }

            $validated['image'] = $imagePath;
        }

        // Jika tidak ada file baru, image tidak ada di $validated dan gambar sebelumnya tetap dipakai.
        $product->update($validated);

        if (isset($validated['image']) && $previousImage !== $validated['image']) {
            // Hapus file lama hanya setelah database menunjuk ke gambar baru.
            $this->deleteStoredImage($previousImage);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Ambil path sebelum record produk dihapus dari database.
        $image = $product->getRawOriginal('image');
        $product->delete();
        $this->deleteStoredImage($image);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Hapus hanya file yang dikelola disk publik; URL gambar lama tidak dimiliki aplikasi.
     */
    private function deleteStoredImage(?string $image): void
    {
        // URL eksternal dan path absolut bukan file yang disimpan pada disk public Laravel.
        if (! $image || filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, '/')) {
            return;
        }

        Storage::disk('public')->delete($image);
    }
}
