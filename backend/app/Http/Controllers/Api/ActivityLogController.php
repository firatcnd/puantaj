<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Support\Months;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/** Sistemdeki değişiklik kayıtları (activity log) — yalnızca admin erişir. */
class ActivityLogController extends Controller
{
    private const SUBJECT_LABELS = [
        'Personnel' => 'Personel',
        'Trip' => 'Sefer',
        'Timesheet' => 'Puantaj',
        'Role' => 'Rol',
        'User' => 'Kullanıcı',
        'Department' => 'Departman',
        'Position' => 'Pozisyon',
    ];

    private const EVENT_LABELS = [
        'created' => 'Oluşturuldu',
        'updated' => 'Güncellendi',
        'deleted' => 'Silindi',
    ];

    /** Alan anahtarı => okunabilir Türkçe etiket. */
    private const FIELD_LABELS = [
        'full_name' => 'Ad Soyad',
        'registration_no' => 'Sicil No',
        'department_id' => 'Departman',
        'position_id' => 'Pozisyon',
        'hire_date' => 'İşe Giriş Tarihi',
        'is_active' => 'Durum',
        'name' => 'Ad',
        'code' => 'Kod',
        'departure_point' => 'Kalkış',
        'arrival_point' => 'Varış',
        'year' => 'Yıl',
        'month' => 'Ay',
        'work_days' => 'Çalışma Günü',
        'leave_days' => 'İzin Günü',
        'sick_days' => 'Rapor Günü',
        'public_holiday_days' => 'Resmi Tatil',
        'weekend_days' => 'Hafta Tatili',
        'overtime_hours' => 'Fazla Mesai',
        'undertime_hours' => 'Eksik Mesai',
        'description' => 'Açıklama',
        'total_amount' => 'Toplam Tutar',
        'permissions' => 'Sayfa İzinleri',
        'is_admin' => 'Yönetici',
        'role_id' => 'Rol',
        'email' => 'E-posta',
        'name_field' => 'Ad',
    ];

    private array $departments;
    private array $positions;
    private array $roles;
    private array $pageLabels;

    public function index(Request $request): JsonResponse
    {
        // FK id -> isim çözümü için lookup tabloları (silinmişler dahil)
        $this->departments = Department::withTrashed()->pluck('name', 'id')->all();
        $this->positions = Position::withTrashed()->pluck('name', 'id')->all();
        $this->roles = Role::withTrashed()->pluck('name', 'id')->all();
        $this->pageLabels = [
            'dashboard' => 'Dashboard',
            'personel' => 'Personel',
            'seferler' => 'Seferler',
            'puantajlar' => 'Puantajlar',
        ];

        $logs = Activity::with('causer')
            ->when($request->query('log_name'), fn ($q, $name) => $q->where('log_name', $name))
            ->when($request->query('event'), fn ($q, $event) => $q->where('event', $event))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        $logs->getCollection()->transform(fn (Activity $log) => [
            'id' => $log->id,
            'subject_type' => self::SUBJECT_LABELS[$log->log_name] ?? $log->log_name,
            'event' => self::EVENT_LABELS[$log->event] ?? $log->event,
            'event_key' => $log->event,
            'causer' => $log->causer?->name ?? 'Sistem',
            'changes' => $this->readableChanges($log),
            'created_at' => $log->created_at->format('d.m.Y H:i:s'),
        ]);

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ],
            'filters' => [
                'subjects' => self::SUBJECT_LABELS,
                'events' => self::EVENT_LABELS,
            ],
        ]);
    }

    /**
     * Ham değişiklikleri okunabilir listeye çevirir:
     * [['label' => 'Departman', 'old' => 'İnsan Kaynakları', 'new' => 'Operasyon'], ...]
     * Oluşturma/silmede yalnızca yeni/eski değer dolu olur.
     */
    private function readableChanges(Activity $log): array
    {
        $changes = $log->attribute_changes?->toArray() ?? [];
        $new = $changes['attributes'] ?? [];
        $old = $changes['old'] ?? [];

        $keys = array_keys($new ?: $old);
        $result = [];

        foreach ($keys as $key) {
            $result[] = [
                'label' => self::FIELD_LABELS[$key] ?? $key,
                'old' => array_key_exists($key, $old) ? $this->formatValue($key, $old[$key]) : null,
                'new' => array_key_exists($key, $new) ? $this->formatValue($key, $new[$key]) : null,
                'has_old' => array_key_exists($key, $old),
            ];
        }

        return $result;
    }

    /** Alan tipine göre değeri insan-okur biçime çevirir. */
    private function formatValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'department_id' => $this->departments[$value] ?? "#{$value}",
            'position_id' => $this->positions[$value] ?? "#{$value}",
            'role_id' => $this->roles[$value] ?? "#{$value}",
            'is_active' => $value ? 'Aktif' : 'Pasif',
            'is_admin' => $value ? 'Evet' : 'Hayır',
            'month' => Months::name((int) $value),
            'permissions' => $this->formatPermissions($value),
            'total_amount' => number_format((float) $value, 2, ',', '.') . ' TL',
            default => is_array($value) ? implode(', ', $value) : (string) $value,
        };
    }

    private function formatPermissions(mixed $value): string
    {
        $pages = is_array($value) ? $value : [];

        return collect($pages)
            ->map(fn ($page) => $this->pageLabels[$page] ?? $page)
            ->join(', ') ?: '—';
    }
}
