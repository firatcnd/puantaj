@extends('exports.layout')

@section('title', 'Personel Listesi')
@section('count', $personnel->count())

@section('table')
    <table>
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>Sicil No</th>
                <th>Departman</th>
                <th>Pozisyon</th>
                <th>İşe Giriş</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($personnel as $person)
                <tr>
                    <td>{{ $person->full_name }}</td>
                    <td>{{ $person->registration_no }}</td>
                    <td>{{ $person->department->name }}</td>
                    <td>{{ $person->position->name }}</td>
                    <td>{{ $person->hire_date->format('d.m.Y') }}</td>
                    <td>{{ $person->is_active ? 'Aktif' : 'Pasif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
