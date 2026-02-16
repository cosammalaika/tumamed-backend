<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'town',
        'address',
        'latitude',
        'longitude',
        'rating_avg',
        'is_active',
        'is_verified',
        'is_open',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_open' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'rating_avg' => 'float',
    ];

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hospital_pharmacies')
            ->withPivot(['priority', 'is_active'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class);
    }

    public function currentRequests(): HasMany
    {
        return $this->hasMany(MedicineRequest::class, 'current_pharmacy_id');
    }
}
