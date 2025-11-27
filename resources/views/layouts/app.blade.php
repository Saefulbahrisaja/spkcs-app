<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SPKCS Banten-SPABILITY' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>

<body class="bg-gray-100">

    @include('layouts.navbar')

    <div class="flex">
        
        @include('layouts.sidebar')

        <main class="p-6 w-full">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
