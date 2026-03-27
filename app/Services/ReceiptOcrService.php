<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptOcrService
{
    public function processReceipt(string $receiptPath): ?array
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            Log::warning('ReceiptOCR: No Anthropic API key configured');
            return null;
        }

        $fullPath = Storage::disk('public')->path($receiptPath);

        if (!file_exists($fullPath)) {
            Log::error('ReceiptOCR: File not found', ['path' => $fullPath]);
            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // PDF not supported for vision — only images
        if ($extension === 'pdf') {
            Log::info('ReceiptOCR: PDF receipts not yet supported for OCR');
            return null;
        }

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        $mediaType = $mimeTypes[$extension] ?? null;
        if (!$mediaType) {
            return null;
        }

        $imageData = base64_encode(file_get_contents($fullPath));

        // Get categories for context
        $categories = Category::active()->ordered()->pluck('name')->toArray();
        $categoryList = implode(', ', $categories);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mediaType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => "Extract the following from this receipt image. Return ONLY a JSON object with these fields:\n" .
                                    "- vendor: string (store/business name)\n" .
                                    "- amount: number (total amount paid)\n" .
                                    "- date: string (YYYY-MM-DD format)\n" .
                                    "- tax: number or null (sales tax amount if visible)\n" .
                                    "- description: string (brief description of purchase, 5-10 words)\n" .
                                    "- category: string (best match from: {$categoryList})\n" .
                                    "- payment_method: string or null (cash, visa, mastercard, etc.)\n" .
                                    "- line_items: array of {name: string, amount: number} or null\n\n" .
                                    "If any field cannot be determined, use null. Return ONLY valid JSON, no other text.",
                            ],
                        ],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('ReceiptOCR: API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('content.0.text', '');

            // Extract JSON from response (handle markdown code blocks)
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                $parsed = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeResult($parsed, $categories);
                }
            }

            Log::warning('ReceiptOCR: Could not parse API response', ['content' => $content]);
            return null;

        } catch (\Exception $e) {
            Log::error('ReceiptOCR: Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeResult(array $parsed, array $categories): array
    {
        // Match category name to ID
        $categoryId = null;
        if (!empty($parsed['category'])) {
            $category = Category::active()
                ->where('name', 'like', '%' . $parsed['category'] . '%')
                ->first();
            $categoryId = $category?->id;
        }

        return [
            'vendor' => $parsed['vendor'] ?? null,
            'amount' => is_numeric($parsed['amount'] ?? null) ? round((float) $parsed['amount'], 2) : null,
            'date' => $parsed['date'] ?? null,
            'tax' => is_numeric($parsed['tax'] ?? null) ? round((float) $parsed['tax'], 2) : null,
            'description' => $parsed['description'] ?? null,
            'category_id' => $categoryId,
            'category_name' => $parsed['category'] ?? null,
            'payment_method' => $parsed['payment_method'] ?? null,
            'line_items' => $parsed['line_items'] ?? null,
            'raw_ocr' => $parsed,
        ];
    }
}
