<div class="flex flex-col w-80 bg-white border-r border-gray-200 h-full">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-cube text-white"></i>
            </div>
            <span class="ml-2 text-lg font-semibold text-gray-900">Arcadia CRM</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        @php
            use Core\Helpers\SidebarHelper;
            $menuItems = SidebarHelper::getSidebarMenu();
            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        @endphp

        <!-- Dynamické moduly -->
        @foreach($menuItems as $item)
            <div>
                @if(isset($item['submenu']) && count($item['submenu']) > 0)
                    <!-- Dropdown menu item -->
                    <button onclick="toggleDropdown('dropdown-{{ $loop->index }}')"
                            class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ $item['active'] ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center">
                            <i class="fas fa-{{ $item['icon'] }} mr-3 {{ $item['active'] ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            <span class="flex-1">{{ $item['name'] }}</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="chevron-{{ $loop->index }}"></i>
                    </button>

                    <div id="dropdown-{{ $loop->index }}" class="hidden ml-4 mt-1 space-y-1">
                        @foreach($item['submenu'] as $subItem)
                            <a href="{{ $subItem['url'] }}"
                               class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $subItem['active'] ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <i class="fas fa-{{ $subItem['icon'] }} mr-3 text-gray-400"></i>
                                <span class="flex-1">{{ $subItem['name'] }}</span>

                                @if(isset($subItem['badge']) && $subItem['badge'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $subItem['badge']['color'] }}-100 text-{{ $subItem['badge']['color'] }}-800">
                                        {{ $subItem['badge']['text'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Regular menu item -->
                    <a href="{{ $item['url'] }}"
                       class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $item['active'] ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i class="fas fa-{{ $item['icon'] }} mr-3 {{ $item['active'] ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                        <span class="flex-1">{{ $item['name'] }}</span>

                        @if(isset($item['badge']) && $item['badge'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $item['badge']['color'] }}-100 text-{{ $item['badge']['color'] }}-800">
                                {{ $item['badge']['text'] }}
                            </span>
                        @endif
                    </a>
                @endif
            </div>
        @endforeach
    </nav>

    <!-- Settings section - moved to bottom -->
    <div class="border-t border-gray-200 px-4 py-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            @i18('settings')
        </h3>
        <div class="space-y-1">
            <a href="/settings/profile"
               class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_starts_with($currentUri, '/settings/profile') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-user-cog mr-3 {{ str_starts_with($currentUri, '/settings/profile') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                <span class="flex-1">@i18('profile')</span>
            </a>
            <a href="/settings/system"
               class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_starts_with($currentUri, '/settings/system') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-cog mr-3 {{ str_starts_with($currentUri, '/settings/system') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                <span class="flex-1">@i18('system')</span>
            </a>
            <a href="/settings/modules"
               class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_starts_with($currentUri, '/settings/modules') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-puzzle-piece mr-3 {{ str_starts_with($currentUri, '/settings/modules') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                <span class="flex-1">@i18('modules')</span>
            </a>
        </div>
    </div>

    <!-- User menu -->
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-600"></i>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-700">
                    @php
                        $userName = 'Uživatel';
                        $userEmail = 'user@example.com';

                        // Kontrola session dat
                        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
                            $userName = $_SESSION['user']['name'] ?? $userName;
                            $userEmail = $_SESSION['user']['email'] ?? $userEmail;
                        }
                    @endphp
                    {{ $userName }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ $userEmail }}
                </p>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <a href="/logout"
               class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md">
                <i class="fas fa-sign-out-alt mr-2"></i>@i18('logout')
            </a>
        </div>
    </div>

    <!-- Language Switcher -->
    <div class="border-t border-gray-200 p-4">
        <div class="flex justify-center">
            @include('components.language-switcher')
        </div>
    </div>
</div>

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const chevron = document.getElementById('chevron-' + dropdownId.split('-')[1]);

    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        dropdown.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Automaticky otevřít dropdown pro aktivní položky
document.addEventListener('DOMContentLoaded', function() {
    const activeDropdowns = document.querySelectorAll('[class*="bg-blue-50"]');
    activeDropdowns.forEach(item => {
        const parent = item.closest('div');
        const dropdown = parent.querySelector('[id^="dropdown-"]');
        const chevron = parent.querySelector('[id^="chevron-"]');

        if (dropdown && chevron) {
            dropdown.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        }
    });
});
</script>
