<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prizePools(): HasMany
    {
        return $this->hasMany(PrizePool::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function drawPeriods(): BelongsToMany
    {
        return $this->belongsToMany(DrawPeriod::class, 'draw_period_prize')
            ->withPivot(['max_quantity', 'awarded_quantity'])
            ->withTimestamps();
    }
}
