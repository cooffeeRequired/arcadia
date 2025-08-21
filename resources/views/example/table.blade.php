@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-8">Příklad moderní tabulky</h1>

    <!-- Programově vytvořená tabulka -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Programově vytvořená tabulka</h2>
        {!! $tableHTML !!}
    </div>

    <!-- Tabulka přes HTML atributy -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Tabulka přes HTML atributy</h2>
        <x-table
            ax-for="simple-table"
            :headers="['ID', 'Jméno', 'Email', 'Telefon', 'Stav']"
            :data="$tableData"
            searchable="true"
            sortable="true"
            pagination="true"
            :per-page="5"
            title="Zákazníci"
            icon="fas fa-users"
            empty-message="Žádní zákazníci nebyli nalezeni">
        </x-table>
    </div>
</div>

<script>
function editCustomer(id) {
    console.log('Edit customer:', id);
    // Implementace editace
}

function deleteCustomer(id) {
    if (confirm('Opravdu chcete smazat tohoto zákazníka?')) {
        console.log('Delete customer:', id);
        // Implementace smazání
    }
}
</script>
@endsection
