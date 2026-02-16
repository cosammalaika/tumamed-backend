<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MedicineRequest extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SENT = 'SENT';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_DECLINED = 'DECLINED';
    public const STATUS_FORWARDED = 'FORWARDED';
    public const STATUS_DELIVERING = 'DELIVERING';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_EXPIRED = 'EXPIRED';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $appends = [
        'prescription_image_url',
    ];

    protected $fillable = [
        'patient_id',
        'hospital_id',
        'current_pharmacy_id',
        'status',
        'request_text',
        'prescription_image_path',
        'accepted_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function currentPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'current_pharmacy_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class)->orderBy('attempt_no');
    }

    public function events(): HasMany
    {
        return $this->hasMany(RequestEvent::class)->orderBy('created_at');
    }

    public function getPrescriptionImageUrlAttribute(): ?string
    {
        if (! $this->prescription_image_path) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->prescription_image_path);
    }
}
