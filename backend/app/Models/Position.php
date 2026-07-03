<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }

    public function tripRates(): HasMany
    {
        return $this->hasMany(TripPositionRate::class);
    }
}
