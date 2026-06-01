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

                <!-- Графики -->
                <div class="d-flex align-items-center gap-2 mb-3 mt-4 flex-wrap">
                    <label class="form-label mb-0 fw-semibold small">Показатель:</label>
                    <select class="form-select form-select-sm w-auto" id="select_blood">
                        <option value="wbc" selected>WBC (Лейкоциты)</option>
                        <option value="rbc">RBC (Эритроциты)</option>
                        <option value="hgb">HGB (Гемоглобин)</option>
                        <option value="hct">HCT (Гематокрит)</option>
                        <option value="plt">PLT (Тромбоциты)</option>
                        <option value="neu%">NEU%</option>
                        <option value="lym%">LYM%</option>
                        <option value="mono%">MONO%</option>
                        <option value="eos%">EOS%</option>
                    </select>
                    <label class="form-label mb-0 fw-semibold small ms-2">Норма:</label>
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_min_blood" placeholder="От">
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_max_blood" placeholder="До">
                </div>
                <div class="card p-2 chart-card">
                    <canvas id="chart_blood" height="120"></canvas>
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
                
                <!-- Графики -->
                <div class="d-flex align-items-center gap-2 mb-3 mt-4 flex-wrap">
                    <label class="form-label mb-0 fw-semibold small">Показатель:</label>
                    <select class="form-select form-select-sm w-auto" id="select_biochem">
                        <option value="glu_глюкоза" selected>Глюкоза</option>
                        <option value="cre_креатинин">Креатинин</option>
                        <option value="chol_холестерин">Холестерин</option>
                        <option value="urea_мочевина">Мочевина</option>
                        <option value="alt_алт">АЛТ</option>
                        <option value="ast_аст">АСТ</option>
                        <option value="tbil_билируб.об">Билирубин</option>
                        <option value="tg_триглицериды">Триглицериды</option>
                    </select>
                    <label class="form-label mb-0 fw-semibold small ms-2">Норма:</label>
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_min_biochem" placeholder="От">
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_max_biochem" placeholder="До">
                </div>
                <div class="card p-2 chart-card">
                    <canvas id="chart_biochem" height="120"></canvas>
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

                <!-- Графики -->
                <div class="d-flex align-items-center gap-2 mb-3 mt-4 flex-wrap">
                    <label class="form-label mb-0 fw-semibold small">Показатель:</label>
                    <select class="form-select form-select-sm w-auto" id="select_coag">
                        <option value="пв,_сек">ПВ (сек)</option>
                        <option value="мно" selected>МНО</option>
                        <option value="фибриноген">Фибриноген</option>
                        <option value="ачтв">АЧТВ</option>
                        <option value="пти">ПТИ</option>
                    </select>
                    <label class="form-label mb-0 fw-semibold small ms-2">Норма:</label>
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_min_coag" placeholder="От">
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_max_coag" placeholder="До">
                </div>
                <div class="card p-2 chart-card">
                    <canvas id="chart_coag" height="120"></canvas>
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

                <!-- Графики -->
                <div class="d-flex align-items-center gap-2 mb-3 mt-4 flex-wrap">
                    <label class="form-label mb-0 fw-semibold small">Показатель:</label>
                    <select class="form-select form-select-sm w-auto" id="select_hormone">
                        <option value="ттг" selected>ТТГ</option>
                        <option value="т4_св.">Т4 свободный</option>
                    </select>
                    <label class="form-label mb-0 fw-semibold small ms-2">Норма:</label>
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_min_hormone" placeholder="От">
                    <input type="number" step="any" class="form-control form-control-sm w-auto" id="norm_max_hormone" placeholder="До">
                </div>
                <div class="card p-2 chart-card">
                    <canvas id="chart_hormone" height="120"></canvas>
                </div>

            @else
                <p class="text-muted">Нет данных гормональных анализов.</p>
            @endif
        </div>
    </div>
</div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rawData = Object.values(@json($records)) || [];
    const activeCharts = {};
    
    // Пол пациента
    const patientGender = (@json($patient['gender']) || '').toUpperCase();
    
    // Динамическая норма гемоглобина в зависимости от пола
    const hgbNorm = (patientGender.includes('М') || patientGender.includes('M')) 
        ? [130, 170] // Мужчины
        : [120, 150]; // Женщины

    // Настройки параметров
    const PARAMS = {
        wbc: { label: 'WBC', color: '#3b82f6', norm: [4.0, 9.0] },
        hgb: { label: 'HGB', color: '#ef4444', norm: hgbNorm }, // динамическая
        plt: { label: 'PLT', color: '#10b981', norm: [150, 400] },
        'glu_глюкоза': { label: 'Глюкоза', color: '#f59e0b', norm: [3.9, 5.5] },
        'cre_креатинин': { label: 'Креатинин', color: '#8b5cf6', norm: [null, 120] },
        'chol_холестерин': { label: 'Холестерин', color: '#ec4899', norm: [null, 5.2] },
        мно: { label: 'МНО', color: '#64748b', norm: [0.8, 1.2] },
        ттг: { label: 'ТТГ', color: '#84cc16', norm: [0.4, 4.0] },
        rbc: { label: 'RBC', color: '#f97316', norm: null },
        hct: { label: 'HCT', color: '#06b6d4', norm: null },
        'neu%': { label: 'NEU%', color: '#8b5cf6', norm: null },
        'lym%': { label: 'LYM%', color: '#ec4899', norm: null },
        'mono%': { label: 'MONO%', color: '#f43f5e', norm: null },
        'eos%': { label: 'EOS%', color: '#0ea5e9', norm: null },
        'alt_алт': { label: 'АЛТ', color: '#06b6d4', norm: null },
        'ast_аст': { label: 'АСТ', color: '#8b5cf6', norm: null },
        'urea_мочевина': { label: 'Мочевина', color: '#14b8a6', norm: null },
        'tbil_билируб.об': { label: 'Билирубин', color: '#f97316', norm: null },
        'tg_триглицериды': { label: 'Триглицериды', color: '#a855f7', norm: null },
        пти: { label: 'ПТИ', color: '#84cc16', norm: null },
        'пв,_сек': { label: 'ПВ', color: '#0ea5e9', norm: null },
        фибриноген: { label: 'Фибриноген', color: '#a855f7', norm: null },
        ачтв: { label: 'АЧТВ', color: '#64748b', norm: null },
        'т4_св.': { label: 'Т4 св.', color: '#e11d48', norm: null }
    };

    function getThemeColors() {
        const style = getComputedStyle(document.documentElement);
        return {
            grid: style.getPropertyValue('--chart-grid').trim() || '#e0e0e0',
            text: style.getPropertyValue('--chart-text').trim() || '#333333'
        };
    }

    // Цвета зоны нормы 
    function getNormColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            fill: isDark ? 'rgba(16, 185, 129, 0.25)' : 'rgba(16, 185, 129, 0.18)',
            border: isDark ? 'rgba(16, 185, 129, 0.85)' : 'rgba(16, 185, 129, 0.7)'
        };
    }

    function renderTabChart(tabKey, paramKey) {
        const canvasId = `chart_${tabKey}`;
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const config = PARAMS[paramKey];
        if (!config) return;

        const filtered = rawData.filter(r => r[paramKey] !== undefined && r[paramKey] !== '' && !isNaN(parseFloat(r[paramKey])));
        const card = canvas.parentElement;
        let msgEl = card.querySelector('.chart-empty-msg');
        
        if (filtered.length === 0) {
            if (!msgEl) {
                msgEl = document.createElement('p');
                msgEl.className = 'text-muted text-center small p-3 chart-empty-msg';
                msgEl.textContent = 'Нет данных для выбранного показателя';
                card.appendChild(msgEl);
            }
            canvas.style.display = 'none';
            msgEl.style.display = 'block';
            if (activeCharts[tabKey]) { activeCharts[tabKey].destroy(); delete activeCharts[tabKey]; }
            return;
        }
        canvas.style.display = 'block';
        if (msgEl) msgEl.style.display = 'none';

        const labels = filtered.map((r, i) => {
            const d = r['дата_пробы'];
            return (d && d.trim() !== '') ? d : `Проба ${i+1}`;
        });
        const values = filtered.map(r => parseFloat(r[paramKey]));

        const maxVal = Math.max(...values);
        const minVal = Math.min(...values);
        const range = maxVal - minVal || 1;
        const padding = range * 0.2;
        const yMin = Math.max(0, minVal - padding);
        const yMax = maxVal + padding;

        const minInput = document.getElementById(`norm_min_${tabKey}`);
        const maxInput = document.getElementById(`norm_max_${tabKey}`);
        let normMin = config.norm ? config.norm[0] : null;
        let normMax = config.norm ? config.norm[1] : null;
        if (minInput && minInput.value.trim() !== '') normMin = parseFloat(minInput.value);
        if (maxInput && maxInput.value.trim() !== '') normMax = parseFloat(maxInput.value);

        const colors = getThemeColors();
        const normColors = getNormColors();
        const datasets = [];

        // 🟩 Зона нормы
        if (normMin !== null || normMax !== null) {
            const drawMax = normMax !== null ? Math.min(normMax, yMax) : yMax;
            const drawMin = normMin !== null ? Math.max(normMin, yMin) : yMin;
            
            datasets.push({
                label: `Норма: ${normMin !== null ? normMin : '—'}–${normMax !== null ? normMax : '∞'}`,
                data: labels.map(() => drawMax),
                borderColor: normColors.border,
                borderDash: [5, 5],
                pointRadius: 0,
                order: 2
            });
            datasets.push({
                label: '',
                data: labels.map(() => drawMin),
                borderColor: normColors.border,
                borderDash: [5, 5],
                backgroundColor: normColors.fill,
                fill: '-1',
                pointRadius: 0,
                order: 2
            });
        }

        datasets.push({
            label: config.label,
            data: values,
            borderColor: config.color,
            backgroundColor: config.color + '30',
            tension: 0.3,
            pointRadius: 4,
            borderWidth: 2,
            order: 1
        });

        if (activeCharts[tabKey]) activeCharts[tabKey].destroy();

        activeCharts[tabKey] = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: colors.text, filter: item => item.text !== '' && item.text !== undefined } } },
                scales: {
                    x: { ticks: { color: colors.text, maxTicksLimit: 8 }, grid: { color: colors.grid } },
                    y: { ticks: { color: colors.text }, grid: { color: colors.grid }, min: yMin, max: yMax }
                }
            }
        });
    }

    const TABS = { blood: 'blood', biochem: 'biochem', coag: 'coag', hormone: 'hormone' };
    Object.keys(TABS).forEach(tabKey => {
        const selectEl = document.getElementById(`select_${tabKey}`);
        const minIn = document.getElementById(`norm_min_${tabKey}`);
        const maxIn = document.getElementById(`norm_max_${tabKey}`);

        if (selectEl) {
            renderTabChart(tabKey, selectEl.value);
            selectEl.addEventListener('change', (e) => renderTabChart(tabKey, e.target.value));
        }
        if (minIn) minIn.addEventListener('input', () => renderTabChart(tabKey, selectEl?.value));
        if (maxIn) maxIn.addEventListener('input', () => renderTabChart(tabKey, selectEl?.value));
    });

    window.updateChartsTheme = function() {
        const colors = getThemeColors();
        const normColors = getNormColors();
        
        Object.values(activeCharts).forEach(chart => {
            if (!chart) return;
            chart.options.scales.x.ticks.color = colors.text;
            chart.options.scales.x.grid.color = colors.grid;
            chart.options.scales.y.ticks.color = colors.text;
            chart.options.scales.y.grid.color = colors.grid;
            chart.options.plugins.legend.labels.color = colors.text;
            
            const normDatasets = chart.data.datasets.filter(d => d.borderDash);
            normDatasets.forEach(d => {
                d.borderColor = normColors.border;
                if (d.backgroundColor) d.backgroundColor = normColors.fill;
            });
            chart.update();
        });
    };
});
</script>