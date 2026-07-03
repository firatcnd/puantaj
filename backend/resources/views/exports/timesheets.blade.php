@extends('exports.layout')

@section('title', 'Puantaj Listesi')
@section('count', $timesheets->count())

@section('table')
    <table>
        <thead>
            <tr>
                <th>Personel</th>
                <th>Departman</th>
                <th>Pozisyon</th>
                <th>Dönem</th>
                <th class="text-end">Çalışma</th>
                <th class="text-end">İzin</th>
                <th class="text-end">Rapor</th>
                <th class="text-end">R.Tatil</th>
                <th class="text-end">H.Tatili</th>
                <th class="text-end">F.Mesai</th>
                <th class="text-end">E.Mesai</th>
                <th class="text-end">Sefer</th>
                <th class="text-end">Toplam Tutar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($timesheets as $timesheet)
                <tr>
                    <td>{{ $timesheet->personnel->full_name }} ({{ $timesheet->personnel->registration_no }})</td>
                    <td>{{ $timesheet->personnel->department->name }}</td>
                    <td>{{ $timesheet->position->name }}</td>
                    <td>{{ \App\Support\Months::name($timesheet->month) }} {{ $timesheet->year }}</td>
                    <td class="text-end">{{ $timesheet->work_days }}</td>
                    <td class="text-end">{{ $timesheet->leave_days }}</td>
                    <td class="text-end">{{ $timesheet->sick_days }}</td>
                    <td class="text-end">{{ $timesheet->public_holiday_days }}</td>
                    <td class="text-end">{{ $timesheet->weekend_days }}</td>
                    <td class="text-end">{{ (float) $timesheet->overtime_hours }}</td>
                    <td class="text-end">{{ (float) $timesheet->undertime_hours }}</td>
                    <td class="text-end">{{ (int) $timesheet->entries_sum_trip_count }}</td>
                    <td class="text-end">{{ number_format($timesheet->total_amount, 2, ',', '.') }} TL</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
