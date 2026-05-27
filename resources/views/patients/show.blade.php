@extends('layouts.app')

@section('title', $patient['name'])

@section('content')
<div class="row">
    <div class="col-lg-3">
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ $patient['name'] }}</h5>
                <p class="text-muted">
                    Дата рождения: {{ $patient['birth_date'] }}<br>
                    Пол: {{ $patient['gender'] }}
                </p>
            </div>
        </div>

        <h6>Ключевые показатели</h6>
        <div class="list-group">
            @foreach(['Chol', 'TG', 'HDL', 'LDL', 'Cr', 'BUN'] as $col)
                <a href="#" class="list-group-item list-group-item-action">{{ $col }}</a>
            @endforeach
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card mb-4">
            <div class="card-body">
                <h5>Динамика показателей</h5>
                <canvas id="trendChart" height="130"></canvas>
            </div>
        </div>

        <h5>История анализов</h5>
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Chol</th>
                    <th>TG</th>
                    <th>Cr</th>
                    <th>BUN</th>
                    <th>Diagnosis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patientRecords as $row)
                <tr>
                    <td>Анализ от {{ now()->subDays(rand(30,500))->format('d.m.Y') }}</td>
                    <td>{{ $row['Chol'] ?? '-' }}</td>
                    <td>{{ $row['TG'] ?? '-' }}</td>
                    <td>{{ $row['Cr'] ?? '-' }}</td>
                    <td>{{ $row['BUN'] ?? '-' }}</td>
                    <td><span class="badge {{ $row['Diagnosis'] == 1 ? 'bg-danger' : 'bg-success' }}">
                        {{ $row['Diagnosis'] }}
                    </span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($patientRecords->pluck('Age')->values()),
            datasets: [{
                label: 'Креатинин (Cr)',
                data: @json($patientRecords->pluck('Cr')->values()),
                borderColor: '#9f1e2e',
                tension: 0.3
            }]
        },
        options: { responsive: true }
    });
</script>
@endsection