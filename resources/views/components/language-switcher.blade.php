@php
use Core\Helpers\TranslationHelper;
$currentLocale = TranslationHelper::getLocale();
$supportedLocales = TranslationHelper::getSupportedLocales();
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }" x-init="
    // Force reset dropdown state when page loads
    open = false;

    // Reset on page visibility change (navigation)
    document.addEventListener('visibilitychange', () => {
        open = false;
    });

    // Reset on page load
    window.addEventListener('load', () => {
        open = false;
    });

    // Reset on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        open = false;
    });

    // Reset on beforeunload (when leaving page)
    window.addEventListener('beforeunload', () => {
        open = false;
    });

    // Reset on popstate (back/forward navigation)
    window.addEventListener('popstate', () => {
        open = false;
    });

    // Close dropdown when clicking outside
    $watch('open', value => {
        if (value) {
            setTimeout(() => {
                const handleClickOutside = (event) => {
                    if (!event.target.closest('[x-data]')) {
                        open = false;
                        document.removeEventListener('click', handleClickOutside);
                    }
                };
                document.addEventListener('click', handleClickOutside);
            }, 0);
        }
    });

    // Force close on any navigation
    setTimeout(() => {
        open = false;
    }, 100);
">
    <div>
        <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="language-menu-button" aria-expanded="true" aria-haspopup="true">
            <span class="mr-2">{{ $supportedLocales[$currentLocale]['flag'] ?? '🌐' }}</span>
            <span class="hidden sm:inline">{{ $supportedLocales[$currentLocale]['native'] ?? $currentLocale }}</span>
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-bottom-right absolute right-0 bottom-full mb-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="language-menu-button" tabindex="-1">
        <div class="py-1" role="none">
            @foreach($supportedLocales as $locale => $info)
                <button @click="open = false; changeLanguage('{{ $locale }}', '{{ $info['flag'] }}')" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $locale === $currentLocale ? 'bg-gray-100 text-gray-900' : '' }}" role="menuitem" tabindex="-1">
                    <span class="mr-3 text-lg">{{ $info['flag'] }}</span>
                    <span class="flex-1 text-left">{{ $info['native'] }}</span>
                    @if($locale === $currentLocale)
                        <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>


