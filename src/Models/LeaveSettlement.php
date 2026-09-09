<?php

namespace Khurrambaig\HRManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSettlement extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'title', 'total_days', 'probation_cap', 'is_active', 'notes'];

    protected $casts = [
        'total_days' => 'decimal:2',
        'probation_cap' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function forYear(int $year): ?self
    {
        return static::query()->active()->where('year', $year)->first()
            ?? static::query()->active()->latest('year')->first()
            ?? null;
    }
}