<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chyba - Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    @yield('styles')
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <!-- Main error content -->
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <div class="flex justify-center">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-2xl font-bold text-gray-900">Arcadia CRM</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error card -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <!-- Error icon and code -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    @yield('error-icon')
                </div>
                <h2 class="text-4xl font-bold text-gray-900 mb-2">@yield('error-code')</h2>
                <h3 class="text-xl font-semibold text-gray-700 mb-4">@yield('error-title')</h3>
            </div>

            <!-- Error message -->
            <div class="text-center mb-8">
                <p class="text-gray-600 leading-relaxed">@yield('error-message')</p>
            </div>

            <!-- Action buttons -->
            <div class="space-y-3">
                @yield('error-actions')
            </div>

            <!-- Additional help -->
            @hasSection('error-help')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        @yield('error-help')
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-gray-500 text-sm">
                Arcadia CRM • @yield('error-code')
            </p>
        </div>
    </div>

    <!-- Notification System -->
    {!! render_notifications() !!}

    @stack('scripts')
    @yield('scripts')
    
    <!-- Notification JavaScript -->
    {!! render_notification_scripts() !!}
</body>
</html> 