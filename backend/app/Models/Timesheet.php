<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timesheet extends Model
{
    use RecordsActivity, SoftDeletes;

    protected $fillable = [
        'personnel_id',
        'position_id',
        'year',
        'month',
        'work_days',
        'leave_days',
        'sick_days',
        'public_holiday_days',
        'weekend_days',
        'overtime_hours',
        'undertime_hours',
        'description',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'overtime_hours' => 'decimal:2',
            'undertime_hours' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    /** Liste ve export'ların ortak kullandığı filtre seti. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['personnel_id'] ?? null, fn (Builder $q, $id) => $q->where('personnel_id', $id))
            ->when($filters['position_id'] ?? null, fn (Builder $q, $id) => $q->where('position_id', $id))
            ->when($filters['department_id'] ?? null, fn (Builder $q, $id) => $q->whereHas(
                'personnel', fn (Builder $sub) => $sub->where('department_id', $id)
            ))
            ->when($filters['year'] ?? null, fn (Builder $q, $year) => $q->where('year', $year))
            ->when($filters['month'] ?? null, fn (Builder $q, $month) => $q->where('month', $month))
            ->when($filters['search'] ?? null, fn (Builder $q, $term) => $q->whereHas(
                'personnel', fn (Builder $sub) => $sub->where('full_name', 'like', "%{$term}%")
                    ->orWhere('registration_no', 'like', "%{$term}%")
            ));
    }
}
