<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_without_loading_livewire(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('Masuk ke Akun Anda')
            ->assertSee('Username atau Email')
            ->assertSee('id="login-username"', false)
            ->assertSee('id="login-password"', false)
            ->assertSee('id="login-submit"', false)
            ->assertDontSee('livewire.min.js');

        $this->assertStringNotContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control')
        );
    }

    public function test_login_requires_username_and_password(): void
    {
        $this->from('/login')
            ->post(route('login.authenticate'), [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login', 'password']);

        $this->assertGuest();
    }

    public function test_invalid_credentials_return_to_login_with_an_error(): void
    {
        User::factory()->create([
            'username' => 'existing_user',
            'email' => 'user@test.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->from('/login')
            ->post(route('login.authenticate'), [
                'login' => 'user@test.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_member_can_login_with_email_and_remember_me(): void
    {
        User::factory()->member()->create([
            'username' => 'member1',
            'email' => 'member@test.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login.authenticate'), [
            'login' => 'member@test.com',
            'password' => 'password123',
            'remember' => '1',
        ])->assertRedirect(route('stocks.index'));

        $this->assertAuthenticated();
    }

    public function test_member_can_login_with_username(): void
    {
        User::factory()->member()->create([
            'username' => 'member_cool',
            'email' => 'cool@test.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login.authenticate'), [
            'login' => 'member_cool',
            'password' => 'password123',
        ])->assertRedirect(route('stocks.index'));

        $this->assertAuthenticated();
    }

    public function test_admin_can_login_and_is_redirected_to_user_management(): void
    {
        User::factory()->admin()->create([
            'username' => 'superadmin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login.authenticate'), [
            'login' => 'superadmin',
            'password' => 'password123',
        ])->assertRedirect(route('admin.users'));

        $this->assertAuthenticated();
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
