<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrawPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'country_id',
        'weekly_winners_target',
        'draw_executed',
        'draw_executed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'draw_executed' => 'boolean',
            'draw_executed_at' => 'datetime',
            'weekly_winners_target' => 'integer',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(Prize::class, 'draw_period_prize')
            ->withPivot(['max_quantity', 'awarded_quantity'])
            ->withTimestamps();
    }
}
