@php
    // Zkontrolujeme, zda jsou nastaveny vlastní breadcrumbs
    use Core\Facades\Container;use Dba\Connection;$customBreadcrumbs = get_breadcrumbs();

    if ($customBreadcrumbs) {
        $breadcrumbs = $customBreadcrumbs;
        clear_breadcrumbs(); // Vymažeme je po použití
    } else {
        // Získáme aktuální URL a rozložíme ji na části
        $currentPath = request()->getUri();
        $pathSegments = array_filter(explode('/', $currentPath));

        // Vytvoříme breadcrumbs
        $breadcrumbs = [];
        $currentUrl = '';

        foreach ($pathSegments as $segment) {
            $currentUrl .= '/' . $segment;

            // Automaticky generujeme název z URI segmentu
            $name = str_replace(['-', '_'], ' ', $segment);
            $name = ucwords($name);

            // Pokud je segment číslo, zkusíme načíst skutečné jméno z databáze
            if (is_numeric($segment)) {
                $previousSegment = $pathSegments[array_search($segment, $pathSegments) - 1] ?? null;

                if ($previousSegment) {
                    $name = getEntityName($previousSegment, $segment);
                } else {
                    $name = $segment; // Zachováme číslo pokud nemůžeme určit typ
                }
            }

            $breadcrumbs[] = [
                'name' => $name,
                'url' => $currentUrl,
                'active' => $currentUrl === $currentPath
            ];
        }

        // Pokud jsme na hlavní stránce, přidáme Dashboard
        if (empty($breadcrumbs)) {
            $breadcrumbs[] = [
                'name' => i18('dashboard'),
                'url' => '/',
                'active' => true
            ];
        }
    }

    // Funkce pro načtení jména entity z databáze
    function getEntityName($entityType, $id) {
        try {
            $db = Container::get('doctrine.connection', Doctrine\DBAL\Connection::class);

            switch ($entityType) {
                case 'customers':
                    $result = $db->executeQuery("SELECT name FROM customers WHERE id = :id", ['id' => $id])->fetchAssociative();
                    return $result ? $result['name'] : "Zákazník #$id";

                case 'contacts':
                    $result = $db->executeQuery("SELECT CONCAT(first_name, ' ', last_name) as name FROM contacts WHERE id = :id", ['id' => $id])->fetchAssociative();
                    return $result ? $result['name'] : "Kontakt #$id";

                case 'deals':
                    $result = $db->executeQuery("SELECT title FROM deals WHERE id = :id", ['id' => $id])->fetchAssociative();
                    return $result ? $result['title'] : "Obchod #$id";

                case 'projects':
                    $result = $db->executeQuery("SELECT name FROM projects WHERE id = :id", ['id' => $id])->fetchAssociative();
                    return $result ? $result['name'] : "Projekt #$id";

                case 'invoices':
                    $result = $db->executeQuery("SELECT invoice_number FROM invoices WHERE id = :id", ['id' => $id])->fetchAssociative();
                    return $result ? "Faktura " . $result['invoice_number'] : "Faktura #$id";

                default:
                    return ucfirst("#$id");
            }
        } catch (Exception $e) {
            return ucfirst("#$id");
        }
    }
@endphp

@if(count($breadcrumbs) > 0)
<div class="mb-6 mx-[2rem] py-[2rem] !mb-0">
    <nav class="flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
        @foreach($breadcrumbs as $index => $breadcrumb)
            @if($index > 0)
                <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif

            <div class="flex items-center">
                @if($index === 0)
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                @endif

                @if($breadcrumb['active'])
                    <span class="text-gray-900 font-semibold px-3 py-1.5 bg-gray-100 rounded-lg">
                        {{ $breadcrumb['name'] }}
                    </span>
                @else
                    <a href="{{ $breadcrumb['url'] }}"
                       class="text-gray-600 hover:text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-all duration-200 font-medium">
                        {{ $breadcrumb['name'] }}
                    </a>
                @endif
            </div>
        @endforeach
    </nav>
</div>
@endif
