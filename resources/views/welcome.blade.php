@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="text-center py-5">
    <h1 class="display-4 fw-bold text-danger mb-3">LongiHealth Analytics</h1>
    <p class="lead mb-4">Платформа для анализа временных трендов и траекторий лабораторных показателей крови</p>
    <a href="{{ route('patients.index') }}" class="btn btn-danger btn-lg px-5">
        Перейти к пациентам
    </a>
</div>
@endsection