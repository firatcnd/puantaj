<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use RecordsActivity, SoftDeletes;

    /** Sistemdeki yetkilendirilebilir sayfa anahtarları. */
    public const PAGES = ['dashboard', 'personel', 'seferler', 'puantajlar'];

    protected $fillable = ['name', 'permissions', 'is_admin'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function canAccessPage(string $page): bool
    {
        return $this->is_admin || in_array($page, $this->permissions ?? [], true);
    }
}
