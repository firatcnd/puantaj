<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timesheet extends Model
{
    use SoftDeletes;

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
}
