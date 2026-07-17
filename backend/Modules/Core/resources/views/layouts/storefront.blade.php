<!DOCTYPE html>
<html lang="ro" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Magazin'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-full flex-col bg-gray-50 text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ url('/') }}" class="text-xl font-bold tracking-tight text-gray-900">
                {{ config('app.name', 'Magazin') }}
            </a>

            <nav class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ url('/') }}" class="text-gray-600 hover:text-indigo-600">Acasă</a>

                {{-- Placeholder pentru coș. Componenta Livewire reală apare în Partea 6. --}}
                <a href="#" class="inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600" aria-label="Coș de cumpărături">
                    <span>Coș</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700">0</span>
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10">
        {{-- Suportă atât layout clasic (@extends/@yield) cât și componente Livewire full-page ($slot). --}}
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Magazin') }} · Proiect educațional, monedă {{ setting('shop.currency', 'RON') }}.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
