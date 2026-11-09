<?php

namespace Tests\Unit\Enums;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function test_stock_status_labels(): void
    {
        $this->assertEquals('Available', StockStatus::AVAILABLE->label());
        $this->assertEquals('Low Stock', StockStatus::LOW_STOCK->label());
        $this->assertEquals('Out of Stock', StockStatus::OUT_OF_STOCK->label());
    }

    public function test_stock_status_badge_classes(): void
    {
        $this->assertStringContainsString('emerald', StockStatus::AVAILABLE->badgeClass());
        $this->assertStringContainsString('amber', StockStatus::LOW_STOCK->badgeClass());
        $this->assertStringContainsString('rose', StockStatus::OUT_OF_STOCK->badgeClass());
    }

    public function test_from_quantity(): void
    {
        $this->assertEquals(StockStatus::OUT_OF_STOCK, StockStatus::fromQuantity(0, 5));
        $this->assertEquals(StockStatus::LOW_STOCK, StockStatus::fromQuantity(5, 5));
        $this->assertEquals(StockStatus::LOW_STOCK, StockStatus::fromQuantity(1, 5));
        $this->assertEquals(StockStatus::AVAILABLE, StockStatus::fromQuantity(6, 5));
    }
}

