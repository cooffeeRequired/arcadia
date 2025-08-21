@extends('layouts.app')

@section('title', @i18('customers') . ' - Arcadia CRM')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <!-- Enhanced Header -->
    {!! $headerHTML ?? '' !!}

    <!-- Moderní tabulka zákazníků -->
    {!! $tableHTML !!}
</div>

<script>
// Funkce pro akce v moderní tabulce
function deleteCustomer(id) {
    console.log(id)

    // if (confirm('Opravdu chcete smazat tohoto zákazníka?')) {
    //     window.location.href = `/customers/${id}/delete`;
    // }
}

// Funkce pro editaci zákazníka
function editCustomer(id) {
    window.location.href = `/customers/${id}/edit`;
}

// Funkce pro zobrazení detailu zákazníka
function viewCustomer(id) {
    window.location.href = `/customers/${id}`;
}
</script>
@endsection
