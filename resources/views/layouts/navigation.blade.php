@php
    $currentUser = Auth::user();
    $isSuperAdmin = $currentUser?->role === 'superadmin';
    $activeTenant = $currentUser?->tenant;

    if ($isSuperAdmin) {
        $brandName = __('Super Admin');
        $brandTitle = __('Panel Pusat');
        $brandHome = route('superadmin.dashboard');
    } else {
        $brandName = $activeTenant->name ?? config('app.name', 'Laravel');
        $brandTitle = $activeTenant->title ?? __('Sistem Kasir');
        $brandHome = route('dashboard');
    }

    $brandColor = $activeTenant->primary_color ?? '#0F2E2B';
@endphp

<aside
    x-cloak
    style="--tenant-primary: {{ $brandColor }}"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[var(--tenant-primary)] flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 print:hidden"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    {{-- Brand --}}
    <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <a href="{{ $brandHome }}" class="flex items-center gap-3 min-w-0 flex-1">
            @if (!$isSuperAdmin && $activeTenant?->logo_path)
                <img src="{{ Storage::url($activeTenant->logo_path) }}" alt="{{ $brandName }}"
                     class="w-9 h-9 rounded-lg object-cover shrink-0 bg-white">
            @else
                <span class="w-9 h-9 rounded-lg bg-[#D4A73C] text-[#0F2E2B] font-bold flex items-center justify-center text-sm shrink-0">
                    {{ strtoupper(substr($brandName, 0, 1)) }}
                </span>
            @endif
            <span class="min-w-0">
                <span class="block font-semibold leading-tight text-[#F6F3EC] truncate">{{ $brandName }}</span>
                <span class="block text-xs leading-tight text-white/50 truncate">{{ $brandTitle }}</span>
            </span>
        </a>

        {{-- Tombol tutup, cuma muncul di HP/tablet --}}
        <button
            @click="sidebarOpen = false"
            class="lg:hidden shrink-0 text-white/60 hover:text-white p-1"
            aria-label="{{ __('Tutup menu') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Nav groups --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
        @php
            $navLink = fn ($active) => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition '
                . ($active
                    ? 'bg-[#D4A73C] text-[#0F2E2B]'
                    : 'text-white/70 hover:text-white hover:bg-white/5');
        @endphp

        @if ($isSuperAdmin)
            {{-- ============ NAV KHUSUS SUPERADMIN ============ --}}
            <div class="space-y-1">
                <a href="{{ route('superadmin.dashboard') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('superadmin.dashboard')) }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                    </svg>
                    {{ __('Dashboard') }}
                </a>

                <a href="{{ route('superadmin.tenants.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('superadmin.tenants.*')) }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V7a2 2 0 012-2h4a2 2 0 012 2v14M13 21V11a2 2 0 012-2h4a2 2 0 012 2v10M3 21h18M9 9h.01M9 13h.01M9 17h.01" />
                    </svg>
                    {{ __('Daftar Usaha') }}
                </a>
            </div>
        @else
            {{-- ============ NAV TENANT (OWNER & KASIR) ============ --}}

            {{-- Menu Utama: fitur yang paling sering dipakai sehari-hari --}}
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wide text-white/30">{{ __('Menu Utama') }}</p>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('dashboard')) }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg>
                        {{ __('Dashboard') }}
                    </a>

                    @if (Route::has('transactions.create') && $currentUser->canAccessMenu('transactions'))
                        <a href="{{ route('transactions.create') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('transactions.create')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2H19a1 1 0 01.98 1.2l-1.6 8A1 1 0 0117.4 15H8.4M7 13L5.4 5M7 13l-1.7 2c-.5.6-.1 1.4.7 1.4H17M9 20a1 1 0 11-2 0 1 1 0 012 0zm9 0a1 1 0 11-2 0 1 1 0 012 0z" />
                            </svg>
                            {{ __('Kasir') }}
                        </a>
                    @endif

                    @if ($currentUser->canAccessMenu('transactions'))
                        <a href="{{ route('transactions.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('transactions.index') || request()->routeIs('transactions.show')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                            </svg>
                            {{ __('Transaksi') }}
                        </a>
                    @endif

                    @if (Route::has('reports.index') && $currentUser->canAccessMenu('reports'))
                        <a href="{{ route('reports.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('reports.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a1 1 0 011-1h1a1 1 0 011 1v6m4 0v-9a1 1 0 011-1h1a1 1 0 011 1v9M5 19v-3a1 1 0 011-1h1a1 1 0 011 1v3M3 19h18" />
                            </svg>
                            {{ __('Laporan') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Data Toko: master data & riwayat pendukung --}}
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wide text-white/30">{{ __('Data Toko') }}</p>
                <div class="space-y-1">
                    @if ($currentUser->canAccessMenu('products'))
                        <a href="{{ route('products.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('products.*') || request()->routeIs('variants.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            {{ __('Produk & Stok') }}
                        </a>
                    @endif

                    @if (Route::has('ingredients.index') && $currentUser->canAccessMenu('ingredients'))
                        <a href="{{ route('ingredients.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('ingredients.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M5 8h14M6 8l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M10 12v6m4-6v6" />
                            </svg>
                            {{ __('Bahan Baku') }}
                        </a>
                    @endif

                    @if ($currentUser->canAccessMenu('categories'))
                        <a href="{{ route('categories.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('categories.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M3 11l8.586-8.586a2 2 0 012.828 0l6.172 6.172a2 2 0 010 2.828L12 20l-9-9z" />
                            </svg>
                            {{ __('Kategori') }}
                        </a>
                    @endif

                    @if ($currentUser->canAccessMenu('customers'))
                        <a href="{{ route('customers.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('customers.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 8v-2a4 4 0 00-4-4h-4a4 4 0 00-4 4v2" />
                            </svg>
                            {{ __('Customer') }}
                        </a>
                    @endif

                    @if (Route::has('stock-movements.index') && $currentUser->canAccessMenu('stock-movements'))
                        <a href="{{ route('stock-movements.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('stock-movements.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                            </svg>
                            {{ __('Riwayat Stok') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($currentUser->role === 'owner')
                <div>
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wide text-white/30">{{ __('Pengaturan') }}</p>
                    <div class="space-y-1">
                        <a href="{{ route('owner.settings.edit') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('owner.settings.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                            {{ __('Pengaturan Sistem') }}
                        </a>
                        <a href="{{ route('owner.staff.index') }}" @click="sidebarOpen = false" class="{{ $navLink(request()->routeIs('owner.staff.*')) }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ __('Kelola Kasir') }}
                        </a>
                    </div>
                </div>
            @endif
        @endif
    </nav>

    {{-- User --}}
    <div class="border-t border-white/10 p-3" x-data="{ userMenuOpen: false }">
        @php
            $roleLabel = match ($currentUser->role ?? null) {
                'superadmin' => __('Super Admin'),
                'owner' => __('Owner'),
                default => __('Kasir'),
            };
            $roleDot = match ($currentUser->role ?? null) {
                'superadmin' => 'bg-[#8B5CF6]',
                'owner' => 'bg-[#D4A73C]',
                default => 'bg-[#6FCF97]',
            };
        @endphp
        <div class="relative">
            <button
                @click="userMenuOpen = !userMenuOpen"
                @click.outside="userMenuOpen = false"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/5 transition"
            >
                <span class="w-10 h-10 rounded-full bg-[#D4A73C] text-[#0F2E2B] text-sm font-bold flex items-center justify-center shrink-0 ring-2 ring-white/10">
                    {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1 text-left">
                    <span class="block text-sm font-semibold text-[#F6F3EC] truncate">{{ $currentUser->name }}</span>
                    <span class="inline-flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $roleDot }}"></span>
                        <span class="text-xs text-white/50">{{ $roleLabel }}</span>
                    </span>
                </span>
                <svg class="w-4 h-4 text-white/40 shrink-0 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            {{-- Dropdown --}}
            <div
                x-show="userMenuOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute bottom-full left-0 right-0 mb-2 bg-[#153834] ring-1 ring-white/10 rounded-lg shadow-lg overflow-hidden"
            >
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-medium text-white/80 hover:text-white hover:bg-white/5 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-medium text-[#F0A18C] hover:text-white hover:bg-[#B5482E] transition cursor-pointer border-t border-white/5"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</aside>