<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $image = UploadedFile::fake()->image('kopi.jpg');

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Kopi',
            'price' => 18000,
            'is_active' => '1',
            'image' => $image,
        ]);

        $product = Product::firstOrFail();
        $imagePath = 'products/'.$image->hashName();

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame($imagePath, $product->getRawOriginal('image'));
        $this->assertStringEndsWith('/storage/'.$imagePath, $product->image_url);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_create_and_edit_forms_render_a_file_upload_input(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('type="file"', false)
            ->assertSee('enctype="multipart/form-data"', false);

        $product = Product::create([
            'name' => 'Kopi',
            'price' => 18000,
            'is_active' => true,
        ]);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('type="file"', false)
            ->assertSee('Biarkan kosong untuk tetap menggunakan gambar saat ini.');
    }

    public function test_updating_without_a_new_image_keeps_the_existing_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old.jpg', 'existing image');
        $this->actingAs(User::factory()->create());

        $product = Product::create([
            'name' => 'Kopi',
            'price' => 18000,
            'image' => 'products/old.jpg',
            'is_active' => true,
        ]);

        $response = $this->put(route('admin.products.update', $product), [
            'name' => 'Kopi Susu',
            'price' => 20000,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame('products/old.jpg', $product->fresh()->getRawOriginal('image'));
        Storage::disk('public')->assertExists('products/old.jpg');
    }

    public function test_replacing_an_image_removes_the_previous_uploaded_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old.jpg', 'existing image');
        $this->actingAs(User::factory()->create());

        $product = Product::create([
            'name' => 'Kopi',
            'price' => 18000,
            'image' => 'products/old.jpg',
            'is_active' => true,
        ]);
        $image = UploadedFile::fake()->image('new.jpg');

        $response = $this->put(route('admin.products.update', $product), [
            'name' => 'Kopi',
            'price' => 18000,
            'is_active' => '1',
            'image' => $image,
        ]);

        $newImagePath = 'products/'.$image->hashName();

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame($newImagePath, $product->fresh()->getRawOriginal('image'));
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($newImagePath);
    }

    public function test_deleting_a_product_removes_its_uploaded_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old.jpg', 'existing image');
        $this->actingAs(User::factory()->create());

        $product = Product::create([
            'name' => 'Kopi',
            'price' => 18000,
            'image' => 'products/old.jpg',
            'is_active' => true,
        ]);

        $response = $this->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('products/old.jpg');
    }

    public function test_invalid_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Kopi',
                'price' => 18000,
                'is_active' => '1',
                'image' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
            ]);

        $response->assertRedirect(route('admin.products.create'));
        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_legacy_remote_image_urls_remain_available(): void
    {
        $imageUrl = 'https://example.com/products/kopi.jpg';
        $product = new Product(['image' => $imageUrl]);

        $this->assertSame($imageUrl, $product->image_url);
    }
}
