<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_unauthenticated_request(): void
    {
        $middleware = new EnsureStockOwnership();
        $request = Request::create('/stocks', 'GET');

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => new Response('OK'));
    }

    public function test_route_user_parameter_ownership(): void
    {
        $middleware = new EnsureStockOwnership();
        $member1 = User::factory()->member()->create();
        $member2 = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        // Member accessing own
        $req1 = Request::create('/test', 'GET');
        $req1->setUserResolver(fn () => $member1);
        $route = new \Illuminate\Routing\Route('GET', '/test/{user}', []);
        $route->bind($req1);
        $route->setParameter('user', $member1);
        $req1->setRouteResolver(fn () => $route);

        $res1 = $middleware->handle($req1, fn () => new Response('OK'));
        $this->assertEquals(200, $res1->getStatusCode());

        // Member accessing other user -> 403
        $req2 = Request::create('/test', 'GET');
        $req2->setUserResolver(fn () => $member1);
        $route2 = new \Illuminate\Routing\Route('GET', '/test/{user}', []);
        $route2->bind($req2);
        $route2->setParameter('user', $member2);
        $req2->setRouteResolver(fn () => $route2);

        try {
            $middleware->handle($req2, fn () => new Response('OK'));
            $this->fail('Expected HttpException 403');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Admin accessing other user -> allowed
        $req3 = Request::create('/test', 'GET');
        $req3->setUserResolver(fn () => $admin);
        $route3 = new \Illuminate\Routing\Route('GET', '/test/{user}', []);
        $route3->bind($req3);
        $route3->setParameter('user', $member2);
        $req3->setRouteResolver(fn () => $route3);

        $res3 = $middleware->handle($req3, fn () => new Response('OK'));
        $this->assertEquals(200, $res3->getStatusCode());
    }

    public function test_route_stock_parameter_ownership(): void
    {
        $middleware = new EnsureStockOwnership();
        $member1 = User::factory()->member()->create();
        $member2 = User::factory()->member()->create();
        $admin = User::factory()->admin()->create();

        $stock1 = Stock::factory()->create(['user_id' => $member1->id]);

        // Member1 accessing own stock GET
        $req1 = Request::create('/test', 'GET');
        $req1->setUserResolver(fn () => $member1);
        $route1 = new \Illuminate\Routing\Route('GET', '/test/{stock}', []);
        $route1->bind($req1);
        $route1->setParameter('stock', $stock1);
        $req1->setRouteResolver(fn () => $route1);

        $res1 = $middleware->handle($req1, fn () => new Response('OK'));
        $this->assertEquals(200, $res1->getStatusCode());

        // Member2 accessing Member1's stock GET -> 403
        $req2 = Request::create('/test', 'GET');
        $req2->setUserResolver(fn () => $member2);
        $route2 = new \Illuminate\Routing\Route('GET', '/test/{stock}', []);
        $route2->bind($req2);
        $route2->setParameter('stock', $stock1);
        $req2->setRouteResolver(fn () => $route2);

        try {
            $middleware->handle($req2, fn () => new Response('OK'));
            $this->fail('Expected HttpException 403');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Admin accessing Member1's stock GET -> allowed
        $req3 = Request::create('/test', 'GET');
        $req3->setUserResolver(fn () => $admin);
        $route3 = new \Illuminate\Routing\Route('GET', '/test/{stock}', []);
        $route3->bind($req3);
        $route3->setParameter('stock', $stock1);
        $req3->setRouteResolver(fn () => $route3);

        $res3 = $middleware->handle($req3, fn () => new Response('OK'));
        $this->assertEquals(200, $res3->getStatusCode());

        // Admin attempting to MODIFY Member1's stock (PUT/DELETE) -> 403
        $req4 = Request::create('/test', 'DELETE');
        $req4->setUserResolver(fn () => $admin);
        $route4 = new \Illuminate\Routing\Route('DELETE', '/test/{stock}', []);
        $route4->bind($req4);
        $route4->setParameter('stock', $stock1);
        $req4->setRouteResolver(fn () => $route4);

        try {
            $middleware->handle($req4, fn () => new Response('OK'));
            $this->fail('Expected HttpException 403');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}

