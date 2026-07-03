<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Modellerin oluşturma/güncelleme/silme işlemlerini activity log'a yazar.
 * Kullanan model isteğe bağlı olarak $activityLogAttributes tanımlayabilir;
 * tanımlamazsa tüm doldurulabilir alanlar loglanır.
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            // Hassas alanlar hiçbir zaman loglanmaz
            ->logExcept(['password', 'remember_token'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName(class_basename($this));
    }
}
