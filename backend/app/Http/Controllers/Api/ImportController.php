<?php

namespace App\Http\Controllers\Api;

use App\Exports\PersonnelTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PersonnelImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function personnelTemplate(): Response
    {
        return Excel::download(new PersonnelTemplateExport, 'personel-import-sablonu.xlsx');
    }

    public function personnelImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [], ['file' => 'dosya']);

        $import = new PersonnelImport;
        Excel::import($import, $request->file('file'));

        return response()->json([
            'imported' => $import->imported,
            'errors' => $import->errors,
            'message' => $import->imported > 0
                ? "{$import->imported} personel başarıyla içe aktarıldı."
                : 'Hiçbir personel içe aktarılamadı.',
        ]);
    }
}
