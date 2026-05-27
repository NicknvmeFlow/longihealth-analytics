<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    private function getData()
    {
        $path = storage_path('app/data/dataset_cleaned.csv');
        if (!file_exists($path)) return collect();

        $file = fopen($path, 'r');
        
        // Читаем заголовки и удаляем BOM
        $rawHeaders = fgetcsv($file);
        $headers = array_map(function($h) {
            // Удаляем BOM (символ \xEF\xBB\xBF или \u{FEFF})
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
            $h = trim($h);
            return $h;
        }, $rawHeaders);
        
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
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
    
    if ($records->isEmpty()) abort(404);
    
    $first = $records->first();
    $patient = [
        'name'       => $first['фио'],
        'birth_date' => $first['дата_рождения'] ?? '?',
        'gender'     => $first['пол'],
        'patient_id' => $id
    ];
    
    return view('patients.show', compact('patient', 'records')); // передаём records
    }
}