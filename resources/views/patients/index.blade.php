@extends('layouts.app')

@section('title', 'Пациенты')

@section('content')
<h2 class="mb-4">Пациенты</h2>

<div class="row mb-3">
    <div class="col-md-8">
        <input type="text" id="searchInput" class="form-control" placeholder="Поиск пациента...">
    </div>
    <div class="col-md-4">
        <button class="btn btn-danger w-100">Поиск</button>
    </div>
</div>

<table class="table table-hover align-middle" id="patientsTable">
    <thead class="table-light">
        <tr>
            <th>Пациент</th>
            <th>Дата рождения</th>
            <th>Пол</th>
            <th>ID пациента</th>
            <th width="100">Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($patients as $p)
        <tr>
            <td>{{ $p['name'] }}</td>
            <td>{{ $p['birth_date'] }}</td>
            <td><span class="badge bg-secondary">{{ $p['gender'] }}</span></td>
            <td><small>{{ $p['patient_id'] }}</small></td>
            <td>
                <a href="{{ route('patients.show', $p['id']) }}" class="btn btn-sm btn-outline-danger">
                    Открыть
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection