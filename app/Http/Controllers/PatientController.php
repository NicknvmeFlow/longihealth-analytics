<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    private function loadData()
    {
        $path = storage_path('app/data/cleaned_diabetes_data.csv');
        if (!file_exists($path)) {
            return collect();
        }

        $csv = array_map('str_getcsv', file($path));
        $header = array_shift($csv);

        $data = [];
        foreach ($csv as $row) {
            $data[] = array_combine($header, $row);
        }

        return collect($data);
    }

    public function index()
    {
        $allPatients = $this->loadData();

        // Группируем по пациентам (берём уникальные по Age + Gender + BMI для примера)
        $patients = $allPatients->groupBy(function($item) {
            return $item['Age'] . '-' . $item['Gender'] . '-' . $item['BMI'];
        })->map(function($group) {
            return [
                'id' => $group->first()['Age'] . rand(100,999),
                'name' => 'Пациент ' . $group->first()['Age'] . ' лет',
                'birth_date' => ($group->first()['Age'] > 50 ? '19' : '20') . rand(50,99) . '.0' . rand(1,9) . '.' . rand(10,28),
                'gender' => $group->first()['Gender'],
                'patient_id' => substr(md5($group->first()['Age']), 0, 16),
                'diagnosis' => $group->first()['Diagnosis'] ?? 0,
                'records_count' => $group->count()
            ];
        })->values()->take(15); // Ограничиваем для скорости

        return view('patients.index', compact('patients'));
    }

    public function show($id)
    {
        $allData = $this->loadData();
        
        // Берём все записи для примера (в реальности нужно фильтровать по пациенту)
        $patientRecords = $allData->take(12); 

        $patient = [
            'name' => 'Иванова Елена Владимировна',
            'birth_date' => '21.04.1950',
            'gender' => 'Женский',
            'patient_id' => 'iv19850421'
        ];

        return view('patients.show', compact('patient', 'patientRecords'));
    }
}