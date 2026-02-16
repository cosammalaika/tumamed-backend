<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestEvent extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'medicine_request_id',
        'type',
        'details',
        'from_pharmacy_id',
        'to_pharmacy_id',
        'actor_user_id',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MedicineRequest::class, 'medicine_request_id');
    }

    public function fromPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'from_pharmacy_id');
    }

    public function toPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'to_pharmacy_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
