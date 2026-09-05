<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $layoutUser = auth()->user();
            $layoutBrand = $layoutUser?->role === 'superadmin'
                ? __('Super Admin')
                : ($layoutUser?->tenant->name ?? config('app.name', 'Laravel'));
        @endphp

        <title>{{ $layoutBrand }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F6F3EC] text-[#1F2A24]">
        <div
            x-data="{ sidebarOpen: false }"
            x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)"
            class="min-h-screen flex"
        >
            {{-- Backdrop mobile/tablet: sibling dari <aside>, bukan child --}}
            <div
                x-show="sidebarOpen"
                x-cloak
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            ></div>

            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0 lg:pl-64">

                {{-- Mobile topbar --}}
                <div class="lg:hidden sticky top-0 z-20 flex items-center justify-between bg-[#0F2E2B] px-4 py-3">
                    <button @click="sidebarOpen = true" class="text-[#F6F3EC]" aria-label="{{ __('Buka menu') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="text-[#F6F3EC] font-semibold tracking-tight">
                        {{ $layoutBrand }}
                    </span>
                    <span class="w-6"></span>
                </div>

                @isset($header)
                    <header class="bg-white border-b border-[#E7E1D3]">
                        <div class="px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>