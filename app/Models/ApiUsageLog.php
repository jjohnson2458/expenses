<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ApiUsageLog extends Model
{
    protected $table = 'api_usage_log';

    protected $fillable = [
        'user_id',
        'feature',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'estimated_cost_usd',
        'response_time_ms',
        'success',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'success' => 'boolean',
            'estimated_cost_usd' => 'decimal:6',
        ];
    }

    // Per-million-token pricing by model
    const MODEL_PRICING = [
        'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
        'claude-sonnet-4' => ['input' => 3.00, 'output' => 15.00],
        'claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00],
        'claude-haiku-4-5' => ['input' => 0.80, 'output' => 4.00],
        'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
        'claude-opus-4' => ['input' => 15.00, 'output' => 75.00],
    ];

    const DEFAULT_PRICING = ['input' => 3.00, 'output' => 15.00];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::MODEL_PRICING[$model] ?? self::DEFAULT_PRICING;

        return ($inputTokens / 1_000_000 * $pricing['input'])
             + ($outputTokens / 1_000_000 * $pricing['output']);
    }

    public static function logUsage(array $data): self
    {
        $inputTokens = $data['input_tokens'] ?? 0;
        $outputTokens = $data['output_tokens'] ?? 0;
        $model = $data['model'] ?? 'unknown';

        $data['total_tokens'] = $inputTokens + $outputTokens;
        $data['estimated_cost_usd'] = self::calculateCost($model, $inputTokens, $outputTokens);
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        return self::create($data);
    }
}
