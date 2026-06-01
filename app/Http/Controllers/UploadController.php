<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends Controller
{
    // Страница загрузчика
    public function index()
    {
        return view('upload');
    }

    // Обработка загруженного файла
    public function store(Request $request)
    {
        // Валидация
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:102400',
        ], [
            'csv_file.max' => 'Размер файла превышает лимит (100 МБ).',
            'csv_file.mimes' => 'Разрешены только CSV или TXT.',
        ]);

        if ($request->file('csv_file')->getError() !== UPLOAD_ERR_OK) {
            return back()->withErrors(['csv_file' => 'Ошибка загрузки файла. Проверьте php.ini (post_max_size).'])->withInput();
        }

        // Сохраняем файл в папку temp
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        $uploadPath = $tempDir . '/uploaded_data.csv';
        $request->file('csv_file')->move($tempDir, 'uploaded_data.csv');

        // Открываем файл
        $handle = fopen($uploadPath, 'r');
        if (!$handle) {
            return back()->withErrors(['csv_file' => 'Не удалось открыть файл после загрузки.']);
        }

        // Автоопределение разделителя
        $firstLine = fgets($handle);
        $delimiters = [',', ';', "\t"];
        $delimiter = ',';
        $maxFields = 0;
        foreach ($delimiters as $d) {
            $fields = str_getcsv($firstLine, $d);
            if (count($fields) > $maxFields) {
                $maxFields = count($fields);
                $delimiter = $d;
            }
        }
        rewind($handle);

        // Читаем заголовок
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Файл пуст или не содержит заголовков.']);
        }

        // Читаем данные
        $data = [];          // Для превью (только первые 5 строк)
        $stats = [];         // Промежуточные суммы
        $rowIndex = 0;
        $previewLimit = 5;   // Храним в памяти для таблицы превью

        while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            $assocRow = array_combine($header, $row);

            // Сохраняем для превью
            if ($rowIndex < $previewLimit) {
                $data[] = $assocRow;
            }

            // Считаем статистику для всех строк
            foreach ($header as $col) {
                $val = $assocRow[$col] ?? null;
                if (is_numeric($val) && trim($val) !== '') {
                    $num = (float)$val;
                    if (!isset($stats[$col])) {
                        $stats[$col] = ['count' => 0, 'sum' => 0, 'min' => INF, 'max' => -INF, 'sumSq' => 0];
                    }
                    $stats[$col]['count']++;
                    $stats[$col]['sum'] += $num;
                    $stats[$col]['min'] = min($stats[$col]['min'], $num);
                    $stats[$col]['max'] = max($stats[$col]['max'], $num);
                    $stats[$col]['sumSq'] += $num * $num;
                }
            }
            $rowIndex++;
        }
        fclose($handle);

        // Финальный расчёт статистики
        $describe = [];
        foreach ($stats as $col => $s) {
            if ($s['count'] > 0) {
                $mean = $s['sum'] / $s['count'];
                $variance = ($s['count'] > 1) ? ($s['sumSq'] - ($s['sum'] ** 2) / $s['count']) / ($s['count'] - 1) : 0;
                $describe[$col] = [
                    'count' => $s['count'],
                    'mean' => round($mean, 2),
                    'std' => round(sqrt(max(0, $variance)), 2),
                    'min' => round($s['min'], 2),
                    'max' => round($s['max'], 2)
                ];
            }
        }

        // Возвращаем данные на страницу
        $total_rows = $rowIndex;
        return view('upload', compact('header', 'data', 'describe', 'total_rows', 'delimiter'));
    }
}