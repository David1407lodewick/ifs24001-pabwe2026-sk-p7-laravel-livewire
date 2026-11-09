<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_user_with_required_role(): void
    {
        $middleware = new EnsureRole();
        $user = User::factory()->admin()->create();

        $request = Request::create('/admin/users', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        }, 'admin');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_user_with_different_role(): void
    {
        $middleware = new EnsureRole();
        $user = User::factory()->member()->create();

        $request = Request::create('/admin/users', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => new Response('OK', 200), 'admin');
    }

    public function test_blocks_unauthenticated_user(): void
    {
        $middleware = new EnsureRole();
        $request = Request::create('/admin/users', 'GET');

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => new Response('OK', 200), 'admin');
    }
}

