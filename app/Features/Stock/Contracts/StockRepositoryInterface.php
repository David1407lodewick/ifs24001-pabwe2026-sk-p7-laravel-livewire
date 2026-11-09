<?php

namespace App\Features\Stock\Contracts;

use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockRepositoryInterface
{
    /**
     * Get paginated stocks for a given user with optional status and search filters.
     */
    public function forUser(int $userId, ?string $status = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all stocks for a given user without pagination.
     *
     * @return Collection<int, Stock>
     */
    public function allForUser(int $userId): Collection;

    /**
     * Find a stock by id.
     */
    public function find(int $id): ?Stock;

    /**
     * Find a stock by id or throw an exception.
     */
    public function findOrFail(int $id): Stock;

    /**
     * Create a new stock.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Stock;

    /**
     * Update an existing stock.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(Stock $stock, array $attributes): bool;

    /**
     * Delete an existing stock.
     */
    public function delete(Stock $stock): bool;

    /**
     * Insert multiple stocks at once.
     *
     * @param array<int, array<string, mixed>> $records
     */
    public function insertMany(array $records): bool;
}

