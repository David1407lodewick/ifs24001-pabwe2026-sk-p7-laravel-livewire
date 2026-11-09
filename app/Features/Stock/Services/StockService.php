<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class StockService
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Get paginated stocks for the specified user.
     */
    public function getStocksForUser(int $userId, ?string $status = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->stockRepository->forUser($userId, $status, $search, $perPage);
    }

    /**
     * Create a new stock item for the user.
     *
     * @param array<string, mixed> $data
     */
    public function createStock(int $userId, array $data): Stock
    {
        $quantity = isset($data['quantity']) ? max(0, (int) $data['quantity']) : 0;
        $minStock = isset($data['min_stock']) ? max(0, (int) $data['min_stock']) : 5;
        $status = $data['status'] ?? StockStatus::fromQuantity($quantity, $minStock)->value;

        return $this->stockRepository->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'pcs',
            'price' => $data['price'] ?? null,
            'min_stock' => $minStock,
            'status' => $status,
            'cover_image_path' => $data['cover_image_path'] ?? null,
        ]);
    }

    /**
     * Update an existing stock item.
     *
     * @param array<string, mixed> $data
     */
    public function updateStock(Stock $stock, array $data): bool
    {
        $quantity = array_key_exists('quantity', $data) ? max(0, (int) $data['quantity']) : $stock->quantity;
        $minStock = array_key_exists('min_stock', $data) ? max(0, (int) $data['min_stock']) : $stock->min_stock;
        $status = $data['status'] ?? StockStatus::fromQuantity($quantity, $minStock)->value;

        return $this->stockRepository->update($stock, [
            'name' => $data['name'] ?? $stock->name,
            'sku' => array_key_exists('sku', $data) ? $data['sku'] : $stock->sku,
            'description' => array_key_exists('description', $data) ? $data['description'] : $stock->description,
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? $stock->unit,
            'price' => array_key_exists('price', $data) ? $data['price'] : $stock->price,
            'min_stock' => $minStock,
            'status' => $status,
            'cover_image_path' => array_key_exists('cover_image_path', $data) ? $data['cover_image_path'] : $stock->cover_image_path,
        ]);
    }

    /**
     * Adjust stock quantity by delta (+ or -) and synchronize status with min_stock.
     */
    public function adjustQuantity(Stock $stock, int $delta): bool
    {
        $newQuantity = max(0, $stock->quantity + $delta);
        $newStatus = StockStatus::fromQuantity($newQuantity, $stock->min_stock)->value;

        return $this->stockRepository->update($stock, [
            'quantity' => $newQuantity,
            'status' => $newStatus,
        ]);
    }

    /**
     * Update or upload a cover photo for the stock.
     */
    public function updateCoverPhoto(Stock $stock, mixed $file): string
    {
        if ($stock->cover_image_path && Storage::disk('public')->exists($stock->cover_image_path)) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        $path = $file->store('stock-covers', 'public');
        $this->updateStock($stock, ['cover_image_path' => $path]);

        return $path;
    }

    /**
     * Delete the cover photo of the stock.
     */
    public function deleteCoverPhoto(Stock $stock): bool
    {
        if ($stock->cover_image_path && Storage::disk('public')->exists($stock->cover_image_path)) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        return $this->updateStock($stock, ['cover_image_path' => null]);
    }

    /**
     * Delete a stock item and its cover photo if exists.
     */
    public function deleteStock(Stock $stock): bool
    {
        if ($stock->cover_image_path && Storage::disk('public')->exists($stock->cover_image_path)) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        return $this->stockRepository->delete($stock);
    }
}

