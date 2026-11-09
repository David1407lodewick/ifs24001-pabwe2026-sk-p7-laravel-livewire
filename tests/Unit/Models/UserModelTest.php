<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_helpers(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMember());

        $this->assertTrue($member->isMember());
        $this->assertFalse($member->isAdmin());
    }

    public function test_user_stocks_relationship(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->stocks);
        $this->assertInstanceOf(Stock::class, $user->stocks->first());
    }

    public function test_user_casts(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => 'secret123',
        ]);
        $this->assertEquals('testuser', $user->username);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotEquals('secret123', $user->password);
    }

    public function test_profile_photo_url_helper(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $userWithoutPhoto = User::factory()->create(['profile_photo_path' => null]);
        $this->assertStringContainsString('ui-avatars.com', $userWithoutPhoto->profilePhotoUrl());

        \Illuminate\Support\Facades\Storage::disk('public')->put('profile-photos/avatar.png', 'content');
        $userWithPhoto = User::factory()->create(['profile_photo_path' => 'profile-photos/avatar.png']);
        $this->assertStringContainsString('avatar.png', $userWithPhoto->profilePhotoUrl());
    }
}
