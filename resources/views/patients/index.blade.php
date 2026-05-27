@extends('layouts.app')

@section('title', 'Пациенты')

@section('content')
<h2 class="mb-4">Пациенты</h2>

<input type="text" id="searchInput" class="form-control mb-3" placeholder="Поиск по ФИО...">

<table class="table table-hover" id="patientsTable">
    <thead class="table-light">
        <tr>
            <th>ФИО</th>
            <th>Пол</th>
            <th>Дата рождения</th>
            <th>Анализов</th>
            <th>Последний анализ</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($patients as $p)
        <tr>
            <td>{{ $p['name'] }}</td>
            <td><span class="badge bg-secondary">{{ $p['gender'] }}</span></td>
            <td>{{ $p['birth_date'] }}</td>
            <td><strong>{{ $p['records_count'] }}</strong></td>
            <td>{{ $p['last_analysis'] ?? '—' }}</td>
            <td>
                <a href="{{ route('patients.show', $p['id']) }}" class="btn btn-sm btn-danger">Открыть</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelectorAll('#patientsTable tbody tr');
        rows.forEach(row => {
            let text = row.cells[0].textContent.toUpperCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
@endsection