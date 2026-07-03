<?php

namespace App\Http\Controllers\Api;

use App\Exports\PersonnelExport;
use App\Exports\TimesheetsExport;
use App\Exports\TripsExport;
use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Timesheet;
use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Liste ekranlarının Excel / PDF çıktıları.
 * Aktif filtreler query string ile gelir; listede ne görünüyorsa o indirilir.
 */
class ExportController extends Controller
{
    public function personnelExcel(Request $request): Response
    {
        return Excel::download(new PersonnelExport($request->query()), 'personel-listesi.xlsx');
    }

    public function personnelPdf(Request $request): Response
    {
        $personnel = Personnel::with(['department', 'position'])
            ->filter($request->query())
            ->orderBy('full_name')
            ->get();

        return Pdf::loadView('exports.personnel', ['personnel' => $personnel])
            ->setPaper('a4')
            ->download('personel-listesi.pdf');
    }

    public function tripsExcel(Request $request): Response
    {
        return Excel::download(new TripsExport($request->query()), 'sefer-listesi.xlsx');
    }

    public function tripsPdf(Request $request): Response
    {
        $trips = Trip::with('rates.position')
            ->filter($request->query())
            ->orderBy('name')
            ->get();

        return Pdf::loadView('exports.trips', ['trips' => $trips])
            ->setPaper('a4')
            ->download('sefer-listesi.pdf');
    }

    public function timesheetsExcel(Request $request): Response
    {
        return Excel::download(new TimesheetsExport($request->query()), 'puantaj-listesi.xlsx');
    }

    public function timesheetsPdf(Request $request): Response
    {
        $timesheets = Timesheet::with(['personnel.department', 'position'])
            ->withSum('entries', 'trip_count')
            ->filter($request->query())
            ->latest()
            ->get();

        return Pdf::loadView('exports.timesheets', ['timesheets' => $timesheets])
            ->setPaper('a4', 'landscape')
            ->download('puantaj-listesi.pdf');
    }
}
