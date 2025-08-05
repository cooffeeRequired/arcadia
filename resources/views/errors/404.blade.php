@extends('layouts.app')

@section('title', 'Stránka nenalezena')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="text-6xl font-bold text-gray-300 mb-4">404</div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-2">Stránka nenalezena</h1>
        <p class="text-gray-600 mb-8">Požadovaná stránka nebyla nalezena.</p>
        <a href="/" class="btn-primary">
            Zpět na hlavní stránku
        </a>
    </div>
</div>
@endsection 