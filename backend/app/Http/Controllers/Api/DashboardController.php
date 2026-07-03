<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();

        // En çok görev yapılan sefer (soft delete edilmiş puantajlar hariç)
        $topTrip = $this->entriesWithLiveTimesheets()
            ->select('timesheet_entries.trip_id', DB::raw('SUM(trip_count) as total_trips'))
            ->groupBy('timesheet_entries.trip_id')
            ->orderByDesc('total_trips')
            ->first();

        $topTripModel = $topTrip ? Trip::find($topTrip->trip_id) : null;

        // En çok sefere çıkan personel
        $topPersonnel = $this->entriesWithLiveTimesheets()
            ->select('timesheets.personnel_id', DB::raw('SUM(trip_count) as total_trips'))
            ->groupBy('timesheets.personnel_id')
            ->orderByDesc('total_trips')
            ->first();

        $topPersonnelModel = $topPersonnel
            ? Personnel::with('position')->find($topPersonnel->personnel_id)
            : null;

        // Aylık toplam mesai tutarı ve puantaj sayısı (grafik verisi)
        $monthlyTotals = Timesheet::query()
            ->select('year', 'month', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->limit(12)
            ->get();

        // En çok görev yapılan 5 sefer (grafik verisi)
        $tripDistribution = $this->entriesWithLiveTimesheets()
            ->select('timesheet_entries.trip_id', DB::raw('SUM(trip_count) as total_trips'))
            ->groupBy('timesheet_entries.trip_id')
            ->orderByDesc('total_trips')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'trip' => Trip::withTrashed()->find($row->trip_id)?->name,
                'total_trips' => (int) $row->total_trips,
            ]);

        return response()->json([
            'total_personnel' => Personnel::count(),
            'total_trips' => Trip::count(),
            'timesheets_this_month' => Timesheet::where('year', $now->year)
                ->where('month', $now->month)
                ->count(),
            'total_overtime_amount' => (float) Timesheet::sum('total_amount'),
            'top_trip' => $topTripModel ? [
                'name' => $topTripModel->name,
                'code' => $topTripModel->code,
                'total_trips' => (int) $topTrip->total_trips,
            ] : null,
            'top_personnel' => $topPersonnelModel ? [
                'full_name' => $topPersonnelModel->full_name,
                'position' => $topPersonnelModel->position?->name,
                'total_trips' => (int) $topPersonnel->total_trips,
            ] : null,
            'monthly_totals' => $monthlyTotals->map(fn ($row) => [
                'label' => sprintf('%02d/%d', $row->month, $row->year),
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ]),
            'trip_distribution' => $tripDistribution,
        ]);
    }

    /** Silinmemiş puantajlara ait sefer satırları için ortak sorgu başlangıcı. */
    private function entriesWithLiveTimesheets()
    {
        return TimesheetEntry::query()
            ->join('timesheets', 'timesheets.id', '=', 'timesheet_entries.timesheet_id')
            ->whereNull('timesheets.deleted_at');
    }
}
