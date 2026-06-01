<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    private function getData()
    {
        // Проверяем, загружал ли пользователь свой файл
        $uploadedPath = storage_path('app/temp/uploaded_data.csv');
        $defaultPath  = storage_path('app/data/dataset_cleaned.csv');
        
        $path = file_exists($uploadedPath) ? $uploadedPath : $defaultPath;
        
        if (!file_exists($path)) return collect();

        $file = fopen($path, 'r');

        // Автоопределение разделителя
        $firstLine = fgets($file);
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
        rewind($file);

        // Читаем заголовки и удаляем BOM
        $rawHeaders = fgetcsv($file, 0, $delimiter);
        $headers = array_map(function($h) {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
            return trim($h);
        }, $rawHeaders);
        
        $data = [];
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if (count($row) == count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        fclose($file);
        
        return collect($data);
    }

    public function index()
    {
        $all = $this->getData();
        
        $patients = $all->groupBy('patient_id')->map(function($records) {
            $first = $records->first();
            return [
                'id'            => $first['patient_id'] ?? '?',
                'name'          => $first['фио'] ?? 'Без имени',
                'gender'        => $first['пол'] ?? '?',
                'birth_date'    => $first['дата_рождения'] ?? '?',
                'records_count' => $records->count(),
                'last_date'     => $records->max('дата_пробы') ?? '—'
            ];
        })->values();

        return view('patients.index', compact('patients'));
    }

    public function show($id)
    {
        $all = $this->getData();
        $records = $all->where('patient_id', $id);
        
        if ($records->isEmpty()) abort(404, 'Данные для пациента не найдены');
        
        $first = $records->first();
        $patient = [
            'name'       => $first['фио'],
            'birth_date' => $first['дата_рождения'] ?? '?',
            'gender'     => $first['пол'],
            'patient_id' => $id
        ];
        
        return view('patients.show', compact('patient', 'records'));
    }
}