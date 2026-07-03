<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use RecordsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'departure_point',
        'arrival_point',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TripPositionRate::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    /** Verilen pozisyon için tanımlı mesai ücretini döner; tanımlı değilse null. */
    public function rateForPosition(int $positionId): ?TripPositionRate
    {
        return $this->rates->firstWhere('position_id', $positionId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $sub) => $sub
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('departure_point', 'like', "%{$term}%")
                ->orWhere('arrival_point', 'like', "%{$term}%")
        ));
    }

    /** Liste ve export'ların ortak kullandığı filtre seti. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['search'] ?? null)
            ->when(
                isset($filters['is_active']) && $filters['is_active'] !== '',
                fn (Builder $q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL))
            );
    }
}
