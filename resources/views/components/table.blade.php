@php
    $id = $attributes->get('ax-for', 'table-' . uniqid());
    $headers = $attributes->get('headers', []);
    $data = $attributes->get('data', []);
    $searchable = $attributes->get('searchable', false);
    $sortable = $attributes->get('sortable', false);
    $pagination = $attributes->get('pagination', true);
    $perPage = $attributes->get('per-page', 10);
    $emptyMessage = $attributes->get('empty-message', 'Žádná data k zobrazení');
    $showActions = $attributes->get('show-actions', true);
    $loading = $attributes->get('loading', false);
    $title = $attributes->get('title', 'Tabulka dat');
    $icon = $attributes->get('icon', 'fas fa-table');

    // Moderní CSS třídy podobné modulům
    $containerClasses = $attributes->get('class', 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden');
    $headerClasses = $attributes->get('header-class', 'bg-gray-50 px-6 py-4 border-b border-gray-200');
    $tableClasses = $attributes->get('table-class', 'min-w-full divide-y divide-gray-200');
    $thClasses = $attributes->get('th-class', 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors');
    $tdClasses = $attributes->get('td-class', 'px-6 py-4 whitespace-nowrap text-sm text-gray-900');
    $rowClasses = $attributes->get('row-class', 'hover:bg-gray-50 transition-colors');
@endphp

<div id="table-container-{{ $id }}" class="{{ $containerClasses }}{{ $loading ? ' opacity-50 pointer-events-none' : '' }}">
    <!-- Enhanced Header -->
    <div class="{{ $headerClasses }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="{{ $icon }} text-white text-sm"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-list mr-1"></i>{{ count($data) }} záznamů
                        </span>
                    </div>
                    <div class="flex items-center space-x-4 mt-1">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>Zobrazeno {{ count($data) }} z {{ count($data) }} záznamů
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($searchable)
                <button onclick="toggleTableSearch('{{ $id }}')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-search mr-2"></i>Vyhledat
                </button>
                @endif
                @if($sortable)
                <button onclick="resetTableSort('{{ $id }}')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-sort mr-2"></i>Resetovat řazení
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Panel -->
    @if($searchable)
    <div id="search-panel-{{ $id }}" class="bg-gray-50 px-6 py-4 border-b border-gray-200 hidden">
        <div class="mb-3">
            <input type="text"
                   id="search-{{ $id }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Vyhledat v tabulce..."
                   data-table-id="{{ $id }}">
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <table id="{{ $id }}" class="{{ $tableClasses }}">
            @if(!empty($headers))
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $index => $header)
                    <th class="{{ $thClasses }} {{ $sortable ? 'sortable' : '' }}"
                        data-column="{{ $index }}">
                        {{ $header }}
                        @if($sortable)
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        @endif
                    </th>
                    @endforeach
                    @if($showActions)
                    <th class="{{ $thClasses }} w-32">Akce</th>
                    @endif
                </tr>
            </thead>
            @endif

            <tbody class="bg-white divide-y divide-gray-200">
                @if(empty($data))
                <tr>
                    <td colspan="{{ count($headers) + ($showActions ? 1 : 0) }}" class="text-center py-12 text-gray-500">
                        {{ $emptyMessage }}
                    </td>
                </tr>
                @else
                    @foreach($data as $rowIndex => $row)
                    <tr class="{{ $rowClasses }}">
                        @foreach($row as $cell)
                        <td class="{{ $tdClasses }}">{{ $cell }}</td>
                        @endforeach
                        @if($showActions)
                        <td class="{{ $tdClasses }}">
                            <div class="flex items-center space-x-2">
                                <button class="px-3 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors" onclick="editRow({{ $rowIndex }})">
                                    Upravit
                                </button>
                                <button class="px-3 py-1 text-xs font-medium rounded-md bg-red-100 text-red-800 hover:bg-red-200 transition-colors" onclick="deleteRow({{ $rowIndex }})">
                                    Smazat
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($pagination && !empty($data))
    <div class="bg-white px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-700">
                <span>Zobrazeno {{ count($data) }} z {{ count($data) }} záznamů</span>
            </div>
            @if(ceil(count($data) / $perPage) > 1)
            <div class="flex items-center space-x-2">
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors" onclick="tablePagination_{{ $id }}(1)">První</button>
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors" onclick="tablePagination_{{ $id }}(-1)">Předchozí</button>
                <span class="px-3 py-1 text-sm">Stránka 1 z {{ ceil(count($data) / $perPage) }}</span>
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors" onclick="tablePagination_{{ $id }}(1)">Další</button>
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors" onclick="tablePagination_{{ $id }}({{ ceil(count($data) / $perPage) }})">Poslední</button>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableId = '{{ $id }}';
    const table = document.getElementById(tableId);

    @if($searchable)
    // Search functionality
    const searchInput = document.getElementById('search-' + tableId);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function(row) {
                const cells = row.querySelectorAll('td');
                let found = false;

                cells.forEach(function(cell) {
                    if (cell.textContent.toLowerCase().includes(searchValue)) {
                        found = true;
                    }
                });

                row.style.display = found ? '' : 'none';
            });
        });
    }
    @endif

    @if($sortable)
    // Sorting functionality
    const sortableHeaders = table.querySelectorAll('th.sortable');
    sortableHeaders.forEach(function(th) {
        th.addEventListener('click', function() {
            const column = this.dataset.column;
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const isAsc = this.classList.contains('sort-asc');

            rows.sort(function(a, b) {
                const aText = a.cells[column].textContent.trim();
                const bText = b.cells[column].textContent.trim();

                if (isAsc) {
                    return aText.localeCompare(bText);
                } else {
                    return bText.localeCompare(aText);
                }
            });

            rows.forEach(function(row) {
                tbody.appendChild(row);
            });

            // Update sort indicators
            table.querySelectorAll('th').forEach(function(th) {
                th.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
        });
    });
    @endif
});

// Utility functions
function toggleTableSearch(tableId) {
    const searchPanel = document.getElementById('search-panel-' + tableId);
    searchPanel.classList.toggle('hidden');
}

function resetTableSort(tableId) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Reset to original order
    rows.forEach(function(row, index) {
        tbody.appendChild(row);
    });

    table.querySelectorAll('th').forEach(function(th) {
        th.classList.remove('sort-asc', 'sort-desc');
    });
}

function tablePagination_{{ $id }}(page) {
    console.log('Pagination to page:', page);
    // Implement your pagination logic here
}

function editRow(rowIndex) {
    console.log('Edit row:', rowIndex);
    // Implement your edit logic here
}

function deleteRow(rowIndex) {
    if (confirm('Opravdu chcete smazat tento záznam?')) {
        console.log('Delete row:', rowIndex);
        // Implement your delete logic here
    }
}
</script>
