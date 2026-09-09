<?php

namespace Khurrambaig\HRManagement\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'year_start', 'year_end', 'total_days', 'used_days'];

    protected $casts = [
        'year_start' => 'date',
        'year_end' => 'date',
        'total_days' => 'decimal:2',
        'used_days' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    protected function remainingDays(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->total_days - (float) $this->used_days);
    }
}