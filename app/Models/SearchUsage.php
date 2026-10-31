<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchUsage extends Model
{
    protected $table = 'search_usages';

    protected $fillable = [
        'record_id',
        'user_id',
        'session_id',
        'idempotency_key',
        'ip_address',
        'user_agent',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Search::class, 'record_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
