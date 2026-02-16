<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPharmacyRequest extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_SENT = 'sent';
    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';
    public const STATUS_NO_RESPONSE = 'no_response';
    public const STATUS_EXPIRED = 'expired';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'pharmacy_id',
        'score',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'score' => 'float',
        'responded_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
