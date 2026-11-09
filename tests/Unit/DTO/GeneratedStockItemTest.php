<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_dto_properties_and_to_array(): void
    {
        $dto = new GeneratedStockItem(
            name: 'Laptop ASUS',
            sku: 'BRG-1001',
            description: 'Laptop kerja RAM 16GB',
            quantity: 12,
            unit: 'unit',
            price: 12500000.0,
            minStock: 5,
            status: 'available'
        );

        $this->assertEquals('Laptop ASUS', $dto->name);
        $this->assertEquals('BRG-1001', $dto->sku);
        $this->assertEquals('Laptop kerja RAM 16GB', $dto->description);
        $this->assertEquals(12, $dto->quantity);
        $this->assertEquals('unit', $dto->unit);
        $this->assertEquals(12500000.0, $dto->price);
        $this->assertEquals(5, $dto->minStock);
        $this->assertEquals('available', $dto->status);

        $array = $dto->toArray();
        $this->assertEquals([
            'name' => 'Laptop ASUS',
            'sku' => 'BRG-1001',
            'description' => 'Laptop kerja RAM 16GB',
            'quantity' => 12,
            'unit' => 'unit',
            'price' => 12500000.0,
            'min_stock' => 5,
            'status' => 'available',
        ], $array);
    }
}

