<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebhookLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'success',
    ];

    protected $casts = [
        'payload' => 'array',
        'success' => 'boolean',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
