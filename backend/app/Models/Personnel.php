<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use SoftDeletes;

    protected $table = 'personnel';

    protected $fillable = [
        'full_name',
        'registration_no',
        'department_id',
        'position_id',
        'hire_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $sub) => $sub
                ->where('full_name', 'like', "%{$term}%")
                ->orWhere('registration_no', 'like', "%{$term}%")
        ));
    }
}
