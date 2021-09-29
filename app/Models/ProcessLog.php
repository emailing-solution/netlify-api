<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class ProcessLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'retry_at' => 'datetime'
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function latest_process(): HasOne
    {
        return $this->hasOne(Process::class)->latestOfMany();
    }

    public static function add(string $headers, string $body, int $limit, int $left, Carbon $retry_at, int $process_id, string $code)
    {
        return self::create([
            'status_code' => $code,
            'headers' => $headers,
            'body' => $body,
            'total_limit' => $limit,
            'total_left' => $left,
            'retry_at' => $retry_at,
            'process_id' => $process_id,
        ]);
    }
}
