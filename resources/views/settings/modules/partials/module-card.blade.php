<div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                @if($module->getIcon())
                    <div class="flex-shrink-0">
                        <i class="{{ $module->getIcon() }} text-2xl" style="color: {{ $module->getColor() ?? '#6b7280' }}"></i>
                    </div>
                @else
                    <div class="flex-shrink-0">
                        <i class="fas fa-cube text-2xl text-gray-400"></i>
                    </div>
                @endif
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">{{ $module->getDisplayName() }}</h3>
                </div>
            </div>
            <div class="relative">
                <button type="button" class="inline-flex items-center p-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="toggleDropdown('{{ $module->getName() }}')">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="dropdown-{{ $module->getName() }}" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                        <a href="/settings/modules/{{ $module->getName() }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-eye mr-3"></i>Detail
                        </a>
                        @if(!$module->isInstalled())
                            <a href="/settings/modules/{{ $module->getName() }}/install" class="flex items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                <i class="fas fa-download mr-3"></i>Nainstalovat
                            </a>
                        @else
                            <a href="/settings/modules/{{ $module->getName() }}/uninstall" class="flex items-center px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                <i class="fas fa-trash mr-3"></i>Odinstalovat
                            </a>
                        @endif
                        @if($module->isInstalled())
                            @if($module->isEnabled())
                                <a href="/settings/modules/{{ $module->getName() }}/disable" class="flex items-center px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                    <i class="fas fa-toggle-off mr-3"></i>Vypnout
                                </a>
                            @else
                                <a href="/settings/modules/{{ $module->getName() }}/enable" class="flex items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                    <i class="fas fa-toggle-on mr-3"></i>Zapnout
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            {{ $module->getDescription() ?? 'Žádný popis' }}
        </p>

        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Verze</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $module->getVersion() }}</div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Autor</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $module->getAuthor() ?? 'Neznámý' }}</div>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Stav</div>
                <div class="mt-1">
                    @if($module->isInstalled())
                        @if($module->isEnabled())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Aktivní
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-pause-circle mr-1"></i>Vypnutý
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-times-circle mr-1"></i>Neinstalovaný
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-500">
                @if($module->getInstallDate())
                    <i class="fas fa-clock mr-1"></i>Nainstalováno: {{ $module->getInstallDate()->format('d.m.Y H:i') }}
                @else
                    <i class="fas fa-info-circle mr-1"></i>Není nainstalováno
                @endif
            </div>
        </div>
    </div>
</div>
