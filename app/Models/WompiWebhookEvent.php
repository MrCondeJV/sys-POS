<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WompiWebhookEvent extends Model
{
    public $timestamps = false;

    protected $table = 'wompi_webhook_events';

    protected $fillable = [
        'event_id',
        'event_type',
        'wompi_transaction_id',
        'reference',
        'transaction_status',
        'signature_valid',
        'processed_at',
        'processing_result',
        'ip_address',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'payload' => 'array',
    ];
}
