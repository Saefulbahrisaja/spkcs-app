<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SPKCS Banten-SPABILITY' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
    @yield('styles')
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Navbar (fixed) -->
    <div class="fixed top-0 left-0 right-0 z-50">
        @include('layouts.navbar')
    </div>

    <!-- Sidebar (fixed under navbar) -->
    <div class="fixed top-16 bottom-0 left-0 w-64 z-40 bg-white border-r overflow-auto">
        @include('layouts.sidebar')
    </div>

    <!-- Main content: leave space for navbar (4rem) and sidebar (w-64). Only this scrolls. -->
    <main class="ml-64 pt-16 p-6" style="height: calc(100vh - 4rem); overflow:auto;">
        {{-- Session messages moved inside main so they scroll with content --}}
        @if($message = Session::get('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ $message }}
            </div>
        @endif

        @if($message = Session::get('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ $message }}
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
