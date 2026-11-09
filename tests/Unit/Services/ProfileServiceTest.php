<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfileService();
        Storage::fake('public');
    }

    public function test_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'old_user',
            'email' => 'old@test.com',
        ]);

        $result = $this->service->updateProfile($user, [
            'name' => 'New Name',
            'username' => 'new_user',
            'email' => 'new@test.com',
        ]);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new_user', $user->username);
        $this->assertEquals('new@test.com', $user->email);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        $result = $this->service->updatePassword($user, 'new_secret_123');
        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue(Hash::check('new_secret_123', $user->password));
    }

    public function test_update_profile_photo_with_valid_image(): void
    {
        $user = User::factory()->create();

        // Create a 100x50 dummy image with GD
        $img = imagecreatetruecolor(100, 50);
        imagefilledrectangle($img, 0, 0, 99, 49, imagecolorallocate($img, 255, 0, 0));
        ob_start();
        imagepng($img);
        $binary = ob_get_clean();
        imagedestroy($img);

        $dataUri = 'data:image/png;base64,'.base64_encode($binary);

        $savedPath = $this->service->updateProfilePhoto($user, $dataUri);

        $this->assertNotEmpty($savedPath);
        $user->refresh();
        $this->assertEquals($savedPath, $user->profile_photo_path);
        Storage::disk('public')->assertExists($savedPath);

        // Delete photo
        $deleted = $this->service->deleteProfilePhoto($user);
        $this->assertTrue($deleted);
        $user->refresh();
        $this->assertNull($user->profile_photo_path);
        Storage::disk('public')->assertMissing($savedPath);
    }

    public function test_update_profile_photo_with_invalid_image_throws_exception(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Format gambar tidak valid atau tidak didukung.');
        $this->service->updateProfilePhoto($user, 'not_a_valid_image_string');
    }
}

