<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Enums\StockStatus;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiStockGeneratorService implements AiClientInterface
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $model;

    public function __construct(
        ?string $endpoint = null,
        ?string $apiKey = null,
        ?string $model = null,
    ) {
        $resolvedEndpoint = (string) ($endpoint ?? config('openai.endpoint', 'https://api.openai.com/v1'));
        $resolvedKey = (string) ($apiKey ?? config('openai.key', ''));

        // Toleransi jika nilai OPENAI_ENDPOINT dan OPENAI_KEY tertukar di .env
        if ((str_starts_with($resolvedKey, 'http://') || str_starts_with($resolvedKey, 'https://')) &&
            (!str_starts_with($resolvedEndpoint, 'http://') && !str_starts_with($resolvedEndpoint, 'https://'))) {
            $temp = $resolvedEndpoint;
            $resolvedEndpoint = $resolvedKey;
            $resolvedKey = $temp;
        }

        // Pastikan scheme http:// atau https:// selalu ada
        if (!empty($resolvedEndpoint) && !str_starts_with($resolvedEndpoint, 'http://') && !str_starts_with($resolvedEndpoint, 'https://')) {
            $resolvedEndpoint = 'https://' . $resolvedEndpoint;
        }

        $this->endpoint = rtrim($resolvedEndpoint, '/');
        $this->apiKey = $resolvedKey;
        $this->model = $model ?? (string) config('openai.model', 'gpt-4o-mini');
    }

    /**
     * Generate multiple structured stock items based on a user prompt.
     *
     * @return array<int, GeneratedStockItem>
     *
     * @throws RuntimeException
     */
    public function generateStocks(string $prompt, int $count = 3): array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            throw new RuntimeException('Prompt tidak boleh kosong.');
        }

        $count = max(1, $count);

        $systemPrompt = <<<SYS
You are an expert warehouse and inventory management assistant. Your task is to generate exactly {$count} realistic, structured stock inventory items based on the user's request.

You MUST respond strictly with a valid JSON object matching this schema:
{
  "stocks": [
    {
      "name": "Clear concise product/item name (max 100 chars)",
      "sku": "SKU code e.g. SKU-ELC-001",
      "description": "Brief product specification or inventory notes",
      "quantity": 15,
      "unit": "pcs",
      "price": 150000,
      "min_stock": 5
    }
  ]
}

Do not include any conversational text, introductory words, or explanations outside the JSON.
SYS;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$this->endpoint}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            $errorMessage = $response->json('error.message') ?? "Request failed with status code {$response->status()}";
            throw new RuntimeException("OpenAI API Error: {$errorMessage}");
        }

        $rawContent = (string) $response->json('choices.0.message.content');
        return $this->parseContent($rawContent);
    }

    /**
     * Parse raw string content into an array of GeneratedStockItem.
     *
     * @return array<int, GeneratedStockItem>
     */
    public function parseContent(string $rawContent): array
    {
        $cleanContent = trim($rawContent);

        // Remove Markdown code fences if present
        if (str_starts_with($cleanContent, '```')) {
            $cleanContent = preg_replace('/^```(?:json)?\s*/i', '', $cleanContent);
            $cleanContent = preg_replace('/\s*```$/', '', $cleanContent);
            $cleanContent = trim((string) $cleanContent);
        }

        $decoded = json_decode($cleanContent, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('AI mengembalikan format respon yang tidak valid.');
        }

        // Support both {"stocks": [...]} and [...]
        $items = isset($decoded['stocks']) && is_array($decoded['stocks'])
            ? $decoded['stocks']
            : (array_is_list($decoded) ? $decoded : []);

        if (empty($items)) {
            throw new RuntimeException('Tidak ada item stok yang berhasil di-generate dari AI.');
        }

        $results = [];
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['name'])) {
                continue;
            }

            $name = trim((string) $item['name']);
            $sku = isset($item['sku']) && trim((string) $item['sku']) !== ''
                ? trim((string) $item['sku'])
                : null;
            $description = isset($item['description']) && trim((string) $item['description']) !== ''
                ? trim((string) $item['description'])
                : null;
            $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                ? max(0, (int) $item['quantity'])
                : 10;
            $unit = isset($item['unit']) && trim((string) $item['unit']) !== ''
                ? trim((string) $item['unit'])
                : 'pcs';
            $price = isset($item['price']) && is_numeric($item['price'])
                ? round((float) $item['price'], 2)
                : null;
            $minStock = isset($item['min_stock']) && is_numeric($item['min_stock'])
                ? max(0, (int) $item['min_stock'])
                : 5;

            $status = StockStatus::fromQuantity($quantity, $minStock)->value;

            $results[] = new GeneratedStockItem(
                name: $name,
                sku: $sku,
                description: $description,
                quantity: $quantity,
                unit: $unit,
                price: $price,
                minStock: $minStock,
                status: $status,
            );
        }

        if (empty($results)) {
            throw new RuntimeException('Format item stok yang dikembalikan AI tidak sesuai spesifikasi.');
        }

        return $results;
    }
}

