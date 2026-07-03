<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use RecordsActivity, SoftDeletes;

    protected $fillable = ['name'];

    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }
}
