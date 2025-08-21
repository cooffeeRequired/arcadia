@extends('layouts.app')

@section('title', 'E-maily - Arcadia CRM')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <!-- HeaderUI komponent -->
    {!! $headerHTML ?? '' !!}

    <!-- TableUI komponent -->
    {!! $tableHTML ?? '' !!}
</div>
@endsection
