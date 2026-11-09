<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders_without_loading_livewire(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat Akun Member')
            ->assertSee('Username')
            ->assertDontSee('livewire.min.js');
    }

    public function test_registration_validates_required_and_well_formed_fields(): void
    {
        $this->from('/register')
            ->post(route('register.store'), [
                'name' => '',
                'username' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'username', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_duplicate_email_and_username(): void
    {
        User::factory()->create([
            'username' => 'existing_user',
            'email' => 'duplicate@test.com',
        ]);

        $this->from('/register')
            ->post(route('register.store'), [
                'name' => 'John Doe',
                'username' => 'existing_user',
                'email' => 'duplicate@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['username', 'email']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_valid_registration_creates_and_authenticates_a_member(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Member',
            'username' => 'new_member',
            'email' => 'newmember@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('stocks.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'new_member',
            'email' => 'newmember@test.com',
            'role' => 'member',
        ]);
    }
}
