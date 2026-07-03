<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    protected $fillable = [
        'timesheet_id',
        'trip_id',
        'duty_date',
        'trip_count',
        'unit_rate',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'duty_date' => 'date:Y-m-d',
            'trip_count' => 'integer',
            'unit_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
