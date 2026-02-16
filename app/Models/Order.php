<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_AWAITING_RESPONSES = 'awaiting_responses';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_UNAVAILABLE_NEARBY = 'unavailable_nearby';
    public const STATUS_EXPANDED_SEARCH = 'expanded_search';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'hospital_id',
        'user_lat',
        'user_lng',
        'is_self_patient',
        'patient_name',
        'patient_phone',
        'status',
        'search_radius_km',
        'matched_pharmacy_id',
    ];

    protected $casts = [
        'user_lat' => 'float',
        'user_lng' => 'float',
        'is_self_patient' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function matchedPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'matched_pharmacy_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(OrderPrescription::class);
    }

    public function pharmacyRequests(): HasMany
    {
        return $this->hasMany(OrderPharmacyRequest::class);
    }
}
