<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->get('/profile')
            ->assertRedirect(route('login'));
    }

    public function test_profile_form_inputs_have_labels_and_livewire_script_is_deferred(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('for="profile-name"', false)
            ->assertSee('id="profile-name"', false)
            ->assertSee('for="profile-username"', false)
            ->assertSee('id="profile-username"', false)
            ->assertSee('for="profile-email"', false)
            ->assertSee('id="profile-email"', false)
            ->assertSee('defer="true"', false);
    }

    public function test_profile_mounts_with_authenticated_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Setiawan',
            'username' => 'budiset',
            'email' => 'budi@test.com',
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSet('name', 'Budi Setiawan')
            ->assertSet('username', 'budiset')
            ->assertSet('email', 'budi@test.com');
    }

    public function test_update_profile_information_validation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create([
            'username' => 'taken_username',
            'email' => 'taken@test.com',
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', '')
            ->set('username', 'taken_username')
            ->set('email', 'taken@test.com')
            ->call('updateProfileInformation')
            ->assertHasErrors(['name', 'username', 'email']);
    }

    public function test_update_profile_information_success(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Updated Name')
            ->set('username', 'updated_username')
            ->set('email', 'updated@test.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated_username', $user->username);
        $this->assertEquals('updated@test.com', $user->email);
    }

    public function test_update_password_validation_and_mismatch(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('original_pass'),
        ]);
        $this->actingAs($user);

        // Validation required
        Livewire::test(Profile::class)
            ->set('current_password', '')
            ->set('password', '')
            ->call('updatePassword')
            ->assertHasErrors(['current_password', 'password']);

        // Current password wrong
        Livewire::test(Profile::class)
            ->set('current_password', 'wrong_pass')
            ->set('password', 'new_secret_123')
            ->set('password_confirmation', 'new_secret_123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_update_password_success(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('original_pass'),
        ]);
        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'original_pass')
            ->set('password', 'new_secret_123')
            ->set('password_confirmation', 'new_secret_123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('current_password', '')
            ->assertSet('password', '');

        $this->assertTrue(Hash::check('new_secret_123', $user->fresh()->password));
    }

    public function test_photo_upload_and_crop_saving(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dummyFile = UploadedFile::fake()->image('avatar.png', 200, 200);

        // Test updatedPhoto triggering crop modal
        $component = Livewire::test(Profile::class)
            ->set('photo', $dummyFile)
            ->assertSet('showCropModal', true);

        // Test close crop modal
        $component->call('closeCropModal')
            ->assertSet('showCropModal', false);
        $this->assertNull($component->get('photo'));

        // Test saving with fallback to uploaded file
        $component->set('photo', $dummyFile)
            ->call('saveCroppedPhoto')
            ->assertSet('showCropModal', false);

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);

        // Test saving with empty photo error
        Livewire::test(Profile::class)
            ->set('croppedPhoto', '')
            ->set('photo', null)
            ->call('saveCroppedPhoto')
            ->assertHasErrors(['photo']);

        // Test delete photo
        Livewire::test(Profile::class)
            ->call('deletePhoto');

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }

    public function test_confirm_and_cancel_delete_profile_photo(): void
    {
        $user = User::factory()->create(['profile_photo_path' => 'photos/test.jpg']);
        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSet('confirmingPhotoDeletion', false)
            ->call('confirmDeletePhoto')
            ->assertSet('confirmingPhotoDeletion', true)
            ->call('cancelDeletePhoto')
            ->assertSet('confirmingPhotoDeletion', false)
            ->call('confirmDeletePhoto')
            ->call('deletePhoto')
            ->assertSet('confirmingPhotoDeletion', false);

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }
}
