<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizePool extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'prize_id',
        'total_quantity',
        'awarded_quantity',
        'weekly_target',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
            'awarded_quantity' => 'integer',
            'weekly_target' => 'integer',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function getRemainingAttribute(): int
    {
        return $this->total_quantity - $this->awarded_quantity;
    }
}
