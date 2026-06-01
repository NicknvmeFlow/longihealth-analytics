@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Загрузка и анализ CSV</h2>

    <!-- Форма загрузки -->
    <div class="card p-4 mb-4 shadow-sm">
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csv_file" class="form-label fw-semibold">Выберите файл:</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,.txt" required>
                <div class="form-text text-muted">
                    Поддерживаются разделители <code>,</code>, <code>;</code> или <code>Tab</code>. 
                    Максимум <strong>100 МБ</strong>. Предпросмотр показывает первые 5 записей.
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Загрузить и проанализировать</button>
        </form>

        <!-- Ошибки валидации -->
        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <strong>Ошибка загрузки:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Результаты (показываем только после успешной загрузки) -->
    @if(isset($describe))
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3 text-center border-start border-4 border-primary shadow-sm">
                    <div class="text-muted small">Всего строк (обработано)</div>
                    <div class="fs-4 fw-bold">{{ $total_rows }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center border-start border-4 border-success shadow-sm">
                    <div class="text-muted small">Числовых колонок</div>
                    <div class="fs-4 fw-bold">{{ count($describe) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center border-start border-4 border-info shadow-sm">
                    <div class="text-muted small">Разделитель</div>
                    <div class="fs-4 fw-bold">Авто ({{ $delimiter === "\t" ? 'Tab' : ($delimiter ?? '-') }})</div>
                </div>
            </div>
        </div>

        <!-- Таблица статистики (describe) -->
        <h4 class="mb-3"> Базовая статистика (describe)</h4>
        <div class="table-responsive border rounded mb-4 shadow-sm">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Параметр</th>
                        @foreach(array_keys($describe) as $col)
                            <th class="text-end">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(['count' => 'Количество', 'mean' => 'Среднее', 'std' => 'Ср.кв.отклон', 'min' => 'Минимум', 'max' => 'Максимум'] as $stat => $label)
                    <tr>
                        <td class="fw-semibold">{{ $label }}</td>
                        @foreach($describe as $col => $vals)
                            <td class="text-end">{{ $vals[$stat] ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Предпросмотр данных (первые 5 строк) -->
        <h4 class="mb-3"> Предпросмотр (первые 5 строк)</h4>
        <div class="table-responsive border rounded shadow-sm">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach($header as $col)
                            <th class="fw-bold text-center">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data, 0, 5) as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td class="text-center">{{ $cell ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2 text-end">Показано 5 из {{ $total_rows }} записей.</p>
    @endif
</div>
@endsection