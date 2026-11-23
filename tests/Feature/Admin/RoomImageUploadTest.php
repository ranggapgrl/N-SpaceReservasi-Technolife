<?php

namespace Tests\Feature\Admin;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_room_with_one_uploaded_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $image = UploadedFile::fake()->image('venue.jpg');

        $response = $this->post(route('admin.rooms.store'), array_merge(
            $this->validRoomData(),
            ['image' => $image]
        ));

        $room = Room::firstOrFail();
        $imagePath = 'rooms/'.$image->hashName();
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertSame($imagePath, $room->getRawOriginal('image'));
        $this->assertNull($room->getRawOriginal('image_2'));
        $this->assertNull($room->getRawOriginal('image_3'));
        $this->assertStringEndsWith('/storage/'.$imagePath, $room->image_url);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_room_create_and_edit_forms_use_file_inputs(): void
    {
        $this->actingAs(User::factory()->create());

        $createResponse = $this->get(route('admin.rooms.create'));
        $createResponse
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertDontSee('name="image_2"', false)
            ->assertDontSee('name="image_3"', false);
        $this->assertSame(1, substr_count($createResponse->getContent(), 'type="file"'));

        $room = Room::create($this->validRoomData());

        $editResponse = $this->get(route('admin.rooms.edit', $room));
        $editResponse
            ->assertOk()
            ->assertSee('Kosongkan jika foto saat ini ingin dipertahankan.');
        $this->assertSame(1, substr_count($editResponse->getContent(), 'type="file"'));
    }

    public function test_updating_without_new_images_keeps_existing_images_and_remote_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/old.jpg', 'existing image');
        $this->actingAs(User::factory()->create());

        $room = Room::create(array_merge($this->validRoomData(), [
            'image' => 'rooms/old.jpg',
            'image_2' => 'https://example.com/room.jpg',
        ]));

        $response = $this->put(route('admin.rooms.update', $room), $this->validRoomData());

        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertSame('rooms/old.jpg', $room->fresh()->getRawOriginal('image'));
        $this->assertSame('https://example.com/room.jpg', $room->fresh()->getRawOriginal('image_2'));
        Storage::disk('public')->assertExists('rooms/old.jpg');
    }

    public function test_replacing_one_image_removes_only_the_previous_uploaded_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/old-main.jpg', 'old main');
        Storage::disk('public')->put('rooms/second.jpg', 'second image');
        $this->actingAs(User::factory()->create());

        $room = Room::create(array_merge($this->validRoomData(), [
            'image' => 'rooms/old-main.jpg',
            'image_2' => 'rooms/second.jpg',
            'image_3' => 'https://example.com/room.jpg',
        ]));
        $newImage = UploadedFile::fake()->image('new-main.jpg');

        $response = $this->put(route('admin.rooms.update', $room), array_merge(
            $this->validRoomData(),
            ['image' => $newImage]
        ));

        $newImagePath = 'rooms/'.$newImage->hashName();
        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertSame($newImagePath, $room->fresh()->getRawOriginal('image'));
        $this->assertSame('rooms/second.jpg', $room->fresh()->getRawOriginal('image_2'));
        $this->assertSame('https://example.com/room.jpg', $room->fresh()->getRawOriginal('image_3'));
        Storage::disk('public')->assertMissing('rooms/old-main.jpg');
        Storage::disk('public')->assertExists($newImagePath);
        Storage::disk('public')->assertExists('rooms/second.jpg');
    }

    public function test_invalid_room_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->from(route('admin.rooms.create'))
            ->post(route('admin.rooms.store'), array_merge(
                $this->validRoomData(),
                ['image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain')]
            ));

        $response->assertRedirect(route('admin.rooms.create'))
            ->assertSessionHasErrors('image');
        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_deleting_a_room_removes_its_uploaded_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/room.jpg', 'room image');
        Storage::disk('public')->put('rooms/legacy-second.jpg', 'legacy image');
        Storage::disk('public')->put('rooms/legacy-third.jpg', 'legacy image');
        $this->actingAs(User::factory()->create());

        $room = Room::create(array_merge($this->validRoomData(), [
            'image' => 'rooms/room.jpg',
            'image_2' => 'rooms/legacy-second.jpg',
            'image_3' => 'rooms/legacy-third.jpg',
        ]));

        $response = $this->delete(route('admin.rooms.destroy', $room));

        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        Storage::disk('public')->assertMissing('rooms/room.jpg');
        Storage::disk('public')->assertMissing('rooms/legacy-second.jpg');
        Storage::disk('public')->assertMissing('rooms/legacy-third.jpg');
    }

    /**
     * @return array{name: string, type: string, description: string, capacity: int, facilities: string, status: string, price_per_session: int}
     */
    private function validRoomData(): array
    {
        return [
            'name' => 'Meeting Room',
            'type' => 'Meeting Room',
            'description' => 'Ruang meeting',
            'capacity' => 8,
            'facilities' => 'WiFi',
            'status' => 'active',
            'price_per_session' => 25000,
        ];
    }
}
