@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>{{ $patient['name'] }}</h2>
            <p><strong>Дата рождения:</strong> {{ $patient['birth_date'] }} &nbsp;|&nbsp; 
               <strong>Пол:</strong> {{ $patient['gender'] }}</p>
        </div>
    </div>

    <!-- Вкладки -->
    <ul class="nav nav-tabs" id="labTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" id="blood-tab" data-bs-toggle="tab" data-bs-target="#blood">Общий анализ крови</button></li>
        <li class="nav-item"><button class="nav-link" id="biochem-tab" data-bs-toggle="tab" data-bs-target="#biochem">Биохимия</button></li>
        <li class="nav-item"><button class="nav-link" id="coag-tab" data-bs-toggle="tab" data-bs-target="#coag">Коагулограмма</button></li>
        <li class="nav-item"><button class="nav-link" id="hormone-tab" data-bs-toggle="tab" data-bs-target="#hormone">Гормоны</button></li>
    </ul>

    <div class="tab-content mt-3">
        <!-- === Вкладка: Общий анализ крови === -->
        <div class="tab-pane fade show active" id="blood">
            @php
                $bloodRecords = $records->filter(function($rec) {
                    return isset($rec['wbc']) && $rec['wbc'] !== '' && $rec['wbc'] !== 'unknown';
                });
            @endphp
            @if($bloodRecords->count())
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Дата пробы</th><th>WBC</th><th>RBC</th><th>HGB</th><th>HCT</th><th>PLT</th><th>NEU%</th><th>LYM%</th><th>MONO%</th><th>EOS%</th></tr></thead>
                        <tbody>
                            @foreach($bloodRecords as $rec)
                            <tr>
                                <td>{{ $rec['дата_пробы'] ?? '—' }}</td>
                                <td>{{ $rec['wbc'] ?? '—' }}</td>
                                <td>{{ $rec['rbc'] ?? '—' }}</td>
                                <td>{{ $rec['hgb'] ?? '—' }}</td>
                                <td>{{ $rec['hct'] ?? '—' }}</td>
                                <td>{{ $rec['plt'] ?? '—' }}</td>
                                <td>{{ $rec['neu%'] ?? '—' }}</td>
                                <td>{{ $rec['lym%'] ?? '—' }}</td>
                                <td>{{ $rec['mono%'] ?? '—' }}</td>
                                <td>{{ $rec['eos%'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Блок оценки общего анализа крови (по последнему измерению) -->
                @php
                    $lastBlood = $bloodRecords->last();
                    $gender = $patient['gender'];
                    // Референсы для HGB
                    $hgbNorm = ($gender == 'М') ? [130, 170] : [120, 150];
                    $hgb = isset($lastBlood['hgb']) ? (float)$lastBlood['hgb'] : null;
                    $wbc = isset($lastBlood['wbc']) ? (float)$lastBlood['wbc'] : null;
                    $plt = isset($lastBlood['plt']) ? (float)$lastBlood['plt'] : null;

                    function getColorClass($value, $normMin, $normMax, $warnMin=null, $warnMax=null) {
                        if ($value === null) return 'secondary';
                        if ($value >= $normMin && $value <= $normMax) return 'success';
                        if ($warnMin !== null && $warnMax !== null && $value >= $warnMin && $value <= $warnMax) return 'warning';
                        return 'danger';
                    }
                    function getStatusText($value, $normMin, $normMax) {
                        if ($value === null) return 'нет данных';
                        if ($value < $normMin) return 'понижен';
                        if ($value > $normMax) return 'повышен';
                        return 'норма';
                    }
                @endphp
                <div class="alert alert-light border mt-3">
                    <h6>Оценка последних показателей крови (дата: {{ $lastBlood['дата_пробы'] ?? 'неизвестна' }})</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @if($hgb)
                            <div class="badge bg-{{ getColorClass($hgb, $hgbNorm[0], $hgbNorm[1]) }} p-2">Гемоглобин: {{ $hgb }} ({{ getStatusText($hgb, $hgbNorm[0], $hgbNorm[1]) }})</div>
                        @endif
                        @if($wbc)
                            <div class="badge bg-{{ getColorClass($wbc, 4.0, 9.0, 9.1, 11.0) }} p-2">Лейкоциты: {{ $wbc }} ({{ getStatusText($wbc, 4.0, 9.0) }})</div>
                        @endif
                        @if($plt)
                            <div class="badge bg-{{ getColorClass($plt, 150, 400) }} p-2">Тромбоциты: {{ $plt }} ({{ getStatusText($plt, 150, 400) }})</div>
                        @endif
                    </div>
                    <small class="text-muted">Норма: HGB {{ $hgbNorm[0] }}-{{ $hgbNorm[1] }} г/л, WBC 4.0-9.0, PLT 150-400</small>
                </div>
            @else
                <p class="text-muted">Нет данных общего анализа крови.</p>
            @endif
        </div>

        <!-- === Вкладка: Биохимия === -->
        <div class="tab-pane fade" id="biochem">
            @php
                $biochemRecords = $records->filter(function($rec) {
                    return (isset($rec['glu_глюкоза']) && $rec['glu_глюкоза'] !== '' && $rec['glu_глюкоза'] !== 'unknown') ||
                           (isset($rec['cre_креатинин']) && $rec['cre_креатинин'] !== '' && $rec['cre_креатинин'] !== 'unknown');
                });
            @endphp
            @if($biochemRecords->count())
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Дата пробы</th><th>Глюкоза</th><th>Креатинин</th><th>Холестерин</th><th>Мочевина</th><th>АЛТ</th><th>АСТ</th><th>Билирубин</th><th>Триглицериды</th></tr></thead>
                        <tbody>
                            @foreach($biochemRecords as $rec)
                            <tr>
                                <td>{{ $rec['дата_пробы'] ?? '—' }}</td>
                                <td>{{ $rec['glu_глюкоза'] ?? '—' }}</td>
                                <td>{{ $rec['cre_креатинин'] ?? '—' }}</td>
                                <td>{{ $rec['chol_холестерин'] ?? '—' }}</td>
                                <td>{{ $rec['urea_мочевина'] ?? '—' }}</td>
                                <td>{{ $rec['alt_алт'] ?? '—' }}</td>
                                <td>{{ $rec['ast_аст'] ?? '—' }}</td>
                                <td>{{ $rec['tbil_билируб.об'] ?? '—' }}</td>
                                <td>{{ $rec['tg_триглицериды'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @php
                    $lastBio = $biochemRecords->last();
                    $glu = isset($lastBio['glu_глюкоза']) ? (float)$lastBio['glu_глюкоза'] : null;
                    $cre = isset($lastBio['cre_креатинин']) ? (float)$lastBio['cre_креатинин'] : null;
                    $chol = isset($lastBio['chol_холестерин']) ? (float)$lastBio['chol_холестерин'] : null;
                    // Нормы: глюкоза 3.9-5.5, креатинин (общий) < 120, холестерин <5.2
                @endphp
                <div class="alert alert-light border mt-3">
                    <h6>Оценка биохимических показателей (последние)</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @if($glu)
                            <div class="badge bg-{{ getColorClass($glu, 3.9, 5.5, 5.6, 6.9) }} p-2">Глюкоза: {{ $glu }} ({{ getStatusText($glu, 3.9, 5.5) }})</div>
                        @endif
                        @if($cre)
                            <div class="badge bg-{{ getColorClass($cre, 0, 120, 120, 150) }} p-2">Креатинин: {{ $cre }} ({{ $cre > 120 ? 'повышен' : ($cre < 50 ? 'понижен' : 'норма') }})</div>
                        @endif
                        @if($chol)
                            <div class="badge bg-{{ getColorClass($chol, 0, 5.2, 5.2, 6.2) }} p-2">Холестерин: {{ $chol }} ({{ $chol > 5.2 ? 'повышен' : 'норма' }})</div>
                        @endif
                    </div>
                    <small class="text-muted">Норма: глюкоза 3.9-5.5 ммоль/л, креатинин <120 мкмоль/л, холестерин <5.2 ммоль/л</small>
                </div>

                <!-- График глюкозы -->
                <div class="mt-4">
                    <h5>Динамика глюкозы</h5>
                    <canvas id="glucoseChart" height="80"></canvas>
                </div>
            @else
                <p class="text-muted">Нет данных биохимических анализов.</p>
            @endif
        </div>

        <!-- === Вкладка: Коагулограмма === -->
        <div class="tab-pane fade" id="coag">
            @php
                $coagRecords = $records->filter(function($rec) {
                    return (isset($rec['мно']) && $rec['мно'] !== '' && $rec['мно'] !== 'unknown');
                });
            @endphp
            @if($coagRecords->count())
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Дата пробы</th><th>ПВ (сек)</th><th>МНО</th><th>Фибриноген</th><th>АЧТВ</th><th>ПТИ</th></tr></thead>
                        <tbody>
                            @foreach($coagRecords as $rec)
                            <tr>
                                <td>{{ $rec['дата_пробы'] ?? '—' }}</td>
                                <td>{{ $rec['пв,_сек'] ?? '—' }}</td>
                                <td>{{ $rec['мно'] ?? '—' }}</td>
                                <td>{{ $rec['фибриноген'] ?? '—' }}</td>
                                <td>{{ $rec['ачтв'] ?? '—' }}</td>
                                <td>{{ $rec['пти'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php
                    $lastCoag = $coagRecords->last();
                    $inr = isset($lastCoag['мно']) ? (float)$lastCoag['мно'] : null;
                @endphp
                <div class="alert alert-light border mt-3">
                    <h6>Оценка коагулограммы (последняя)</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @if($inr)
                            <div class="badge bg-{{ getColorClass($inr, 0.8, 1.2) }} p-2">МНО: {{ $inr }} ({{ $inr > 1.2 ? 'повышен' : ($inr < 0.8 ? 'понижен' : 'норма') }})</div>
                        @endif
                    </div>
                    <small class="text-muted">Норма МНО: 0.8–1.2</small>
                </div>
            @else
                <p class="text-muted">Нет данных коагулограммы.</p>
            @endif
        </div>

        <!-- === Вкладка: Гормоны === -->
        <div class="tab-pane fade" id="hormone">
            @php
                $hormoneRecords = $records->filter(function($rec) {
                    return (isset($rec['ттг']) && $rec['ттг'] !== '' && $rec['ттг'] !== 'unknown');
                });
            @endphp
            @if($hormoneRecords->count())
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Дата пробы</th><th>ТТГ (мкМЕ/мл)</th><th>Т4 свободный</th></tr></thead>
                        <tbody>
                            @foreach($hormoneRecords as $rec)
                            <tr>
                                <td>{{ $rec['дата_пробы'] ?? '—' }}</td>
                                <td>{{ $rec['ттг'] ?? '—' }}</td>
                                <td>{{ $rec['т4_св.'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php
                    $lastHormone = $hormoneRecords->last();
                    $tsh = isset($lastHormone['ттг']) ? (float)$lastHormone['ттг'] : null;
                @endphp
                <div class="alert alert-light border mt-3">
                    <h6>Оценка гормонов (последняя)</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @if($tsh)
                            <div class="badge bg-{{ getColorClass($tsh, 0.4, 4.0) }} p-2">ТТГ: {{ $tsh }} ({{ $tsh > 4.0 ? 'повышен' : ($tsh < 0.4 ? 'понижен' : 'норма') }})</div>
                        @endif
                    </div>
                    <small class="text-muted">Норма ТТГ: 0.4–4.0 мкМЕ/мл</small>
                </div>
            @else
                <p class="text-muted">Нет данных гормональных анализов.</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Данные для графика глюкозы
    const glucoseData = @json($records->filter(function($rec) {
        return !empty($rec['дата_пробы']) && !empty($rec['glu_глюкоза']) && is_numeric($rec['glu_глюкоза']);
    })->map(function($rec) {
        return ['date' => $rec['дата_пробы'], 'glu' => (float)$rec['glu_глюкоза']];
    })->values());

    if (glucoseData.length > 0) {
        const labels = glucoseData.map(item => item.date);
        const values = glucoseData.map(item => item.glu);
        new Chart(document.getElementById('glucoseChart'), {
            type: 'line',
            data: { labels: labels, datasets: [{ label: 'Глюкоза (ммоль/л)', data: values, borderColor: '#dc3545', tension: 0.3 }] },
            options: { responsive: true }
        });
    } else {
        const chartCanvas = document.getElementById('glucoseChart');
        if (chartCanvas) chartCanvas.outerHTML = '<p class="text-muted">Нет данных для графика глюкозы</p>';
    }
</script>
@endsection