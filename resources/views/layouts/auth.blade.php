@php use Core\Notification\Notification; @endphp
        <!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.j"></script>
    @stack('styles')
    @yield('styles')
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
<!-- Notification System -->
    {!! Notification::renderFlash() !!}
    {!! Notification::renderToasts() !!}

        <!-- Main Content -->
@yield('content')

@stack('scripts')
    {!! Notification::renderFlashScript() !!}
    {!! Notification::renderToastScript() !!}
@yield('scripts')

</body>
</html>