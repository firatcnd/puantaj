@extends('exports.layout')

@section('title', 'Sefer Listesi')
@section('count', $trips->count())

@section('table')
    <table>
        <thead>
            <tr>
                <th>Sefer Adı</th>
                <th>Kod</th>
                <th>Güzergâh</th>
                <th>Mesai Ücretleri</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trips as $trip)
                <tr>
                    <td>{{ $trip->name }}</td>
                    <td class="badge">{{ $trip->code }}</td>
                    <td>{{ $trip->departure_point }} → {{ $trip->arrival_point }}</td>
                    <td>
                        @foreach ($trip->rates as $rate)
                            {{ $rate->position->name }}: {{ number_format($rate->rate, 2, ',', '.') }} TL{{ $loop->last ? '' : ' | ' }}
                        @endforeach
                    </td>
                    <td>{{ $trip->is_active ? 'Aktif' : 'Pasif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
