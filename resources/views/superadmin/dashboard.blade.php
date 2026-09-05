<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Dashboard Superadmin</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <!-- Kartu statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <span class="w-9 h-9 rounded-lg bg-[#EAF0EE] text-[#1F2A24] flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V7a2 2 0 012-2h4a2 2 0 012 2v14M13 21V11a2 2 0 012-2h4a2 2 0 012 2v10M3 21h18" />
                        </svg>
                    </span>
                    <p class="text-xs text-[#8A8272] mb-1">Total Usaha</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">{{ $totalTenants }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">{{ $activeTenants }} aktif</p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <span class="w-9 h-9 rounded-lg bg-[#EAF3EE] text-[#2F6F4E] flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <p class="text-xs text-[#8A8272] mb-1">Sedang Online</p>
                    <p class="text-xl font-semibold text-[#2F6F4E]">{{ $onlineUsers->count() }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">5 menit terakhir</p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <span class="w-9 h-9 rounded-lg bg-[#FBF1DD] text-[#B5842A] flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8V6m0 2c1.66 0 3 .9 3 2m-3 6v2m0-2c-1.66 0-3-.9-3-2" />
                        </svg>
                    </span>
                    <p class="text-xs text-[#8A8272] mb-1">Total Omzet (Lunas)</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Gabungan semua usaha</p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <span class="w-9 h-9 rounded-lg bg-[#EAF0F3] text-[#1B6E6E] flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a1 1 0 011-1h1a1 1 0 011 1v6m4 0v-9a1 1 0 011-1h1a1 1 0 011 1v9M5 19v-3a1 1 0 011-1h1a1 1 0 011 1v3M3 19h18" />
                        </svg>
                    </span>
                    <p class="text-xs text-[#8A8272] mb-1">Rata-rata Omzet</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($avgOmzetPerTenant, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Per usaha aktif</p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 flex flex-col">
                    <span class="w-9 h-9 rounded-lg bg-[#F2F2F2] text-[#8A8272] flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <p class="text-xs text-[#8A8272] mb-1">Usaha Baru Bulan Ini</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">{{ $newTenantsThisMonth }}</p>
                    <a href="{{ route('superadmin.tenants.index') }}" class="text-xs font-semibold text-[#B5842A] hover:text-[#8A6420] mt-auto pt-1">
                        Lihat semua usaha &rarr;
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Yang lagi online -->
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Sedang Online</h3>
                    @forelse ($onlineUsers as $u)
                        <div class="flex items-center gap-3 py-2.5 border-b border-[#EFEAE0] last:border-0">
                            <span class="relative shrink-0">
                                <span class="w-9 h-9 rounded-full bg-[#0F2E2B] text-white text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </span>
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-[#2F6F4E] ring-2 ring-white"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $u->name }}</p>
                                <p class="text-xs text-[#8A8272] truncate">{{ $u->tenant->name ?? '—' }} &middot; {{ $u->role }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Tidak ada yang online</p>
                    @endforelse
                </div>

                <!-- Usaha terbaru -->
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Usaha Terbaru</h3>
                    @forelse ($tenants as $t)
                        <a href="{{ route('superadmin.tenants.edit', $t) }}" class="flex items-center gap-3 py-2.5 border-b border-[#EFEAE0] last:border-0 hover:bg-[#F6F3EC] -mx-2 px-2 rounded-lg transition">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold text-white shrink-0"
                                  style="background-color: {{ $t->primary_color ?? '#0F2E2B' }}">
                                {{ strtoupper(substr($t->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $t->name }}</p>
                                <p class="text-xs text-[#8A8272]">{{ $t->users_count }} akun</p>
                            </div>
                            <span class="shrink-0 text-[10px] font-medium uppercase px-1.5 py-0.5 rounded {{ $t->is_active ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada usaha terdaftar</p>
                    @endforelse
                    <a href="{{ route('superadmin.tenants.create') }}" class="flex items-center gap-1.5 text-sm text-[#B5842A] font-medium hover:text-[#8A6420] mt-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah usaha baru
                    </a>
                </div>

                <!-- Histori perubahan setting -->
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Histori Perubahan Setting</h3>
                    @forelse ($recentHistories as $h)
                        <div class="flex gap-3 py-2.5 border-b border-[#EFEAE0] last:border-0">
                            <span class="w-2 h-2 rounded-full bg-[#D4A73C] mt-1.5 shrink-0"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-[#1F2A24]">
                                    <span class="font-medium">{{ $h->tenant->name ?? '—' }}</span>
                                    ganti <span class="font-medium">{{ $h->field }}</span>
                                </p>
                                <p class="text-xs text-[#8A8272] truncate">
                                    "{{ $h->old_value ?? '(kosong)' }}" &rarr; "{{ $h->new_value ?? '(kosong)' }}"
                                </p>
                                <p class="text-xs text-[#B5A97A]">{{ $h->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada perubahan setting</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>