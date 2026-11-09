<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedStockItem;

interface AiClientInterface
{
    /**
     * Generate multiple structured stock items based on a prompt.
     *
     * @return array<int, GeneratedStockItem>
     */
    public function generateStocks(string $prompt, int $count = 3): array;
}
