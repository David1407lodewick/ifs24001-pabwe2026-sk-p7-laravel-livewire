<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Repositories\StockRepository;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_service_bindings(): void
    {
        $repo = app(StockRepositoryInterface::class);
        $this->assertInstanceOf(StockRepository::class, $repo);

        $ai = app(AiClientInterface::class);
        $this->assertInstanceOf(OpenAiStockGeneratorService::class, $ai);
    }

    public function test_force_https_configuration(): void
    {
        config(['app.force_https' => true]);

        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        $generated = URL::to('/test');
        $this->assertStringStartsWith('https://', $generated);

        // Reset
        config(['app.force_https' => false]);
    }

    public function test_rate_limiters(): void
    {
        $request = Request::create('/test', 'GET');

        $limiter1000 = RateLimiter::limiter('1000-per-second');
        $this->assertNotNull($limiter1000);
        $result = $limiter1000($request);
        $this->assertEquals(1000, $result->maxAttempts);

        $limiterGlobal = RateLimiter::limiter('global');
        $this->assertNotNull($limiterGlobal);
        $this->assertEquals(1000, $limiterGlobal($request)->maxAttempts);

        $limiterApi = RateLimiter::limiter('api');
        $this->assertNotNull($limiterApi);
        $this->assertEquals(1000, $limiterApi($request)->maxAttempts);
    }
}
