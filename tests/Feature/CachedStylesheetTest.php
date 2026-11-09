<?php

namespace Tests\Feature;

use Tests\TestCase;

class CachedStylesheetTest extends TestCase
{
    public function test_root_page_inlines_stylesheet_to_avoid_a_render_blocking_request(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<style>', false)
            ->assertSee('.min-h-screen', false)
            ->assertDontSee('assets/css/app-', false)
            ->assertDontSee('livewire.min.js', false);
    }

    public function test_public_auth_pages_inline_stylesheet_without_requesting_external_css(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<style>', false)
            ->assertSee('.min-h-screen', false)
            ->assertDontSee('assets/css/app-', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('<style>', false)
            ->assertSee('.min-h-screen', false)
            ->assertDontSee('assets/css/app-', false);
    }

    public function test_hashed_stylesheet_is_served_with_a_year_of_immutable_caching(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $filename = basename($manifest['resources/css/app.css']['file']);

        $response = $this->get(route('assets.css', ['filename' => $filename]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8');

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
    }

    public function test_only_existing_hashed_app_stylesheets_are_served(): void
    {
        $this->get(route('assets.css', ['filename' => 'app-not-a-real-hash.css']))
            ->assertNotFound();
    }
}
