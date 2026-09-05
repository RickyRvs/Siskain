<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1F2A24]">Daftar Usaha</h2>
            <a href="{{ route('superadmin.tenants.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Usaha
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-lg bg-[#EAF0EE] text-[#1F2A24] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V7a2 2 0 012-2h4a2 2 0 012 2v14M13 21V11a2 2 0 012-2h4a2 2 0 012 2v10M3 21h18" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs text-[#8A8272]">Total Usaha</p>
                        <p class="text-xl font-semibold text-[#1F2A24]">{{ $summary['total'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-lg bg-[#EAF3EE] text-[#2F6F4E] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs text-[#8A8272]">Aktif</p>
                        <p class="text-xl font-semibold text-[#2F6F4E]">{{ $summary['aktif'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-lg bg-[#F2F2F2] text-[#8A8272] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 6.64a9 9 0 11-12.73 0M12 3v9" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs text-[#8A8272]">Nonaktif</p>
                        <p class="text-xl font-semibold text-[#8A8272]">{{ $summary['nonaktif'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-lg bg-[#FBF1DD] text-[#B5842A] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8V6m0 2c1.66 0 3 .9 3 2m-3 6v2m0-2c-1.66 0-3-.9-3-2" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs text-[#8A8272]">Total Omzet (Lunas)</p>
                        <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="w-4 h-4 text-[#B5A97A] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama usaha..."
                           class="w-64 rounded-lg border-[#DDD5C2] text-sm pl-9 focus:ring-[#D4A73C] focus:border-[#D4A73C]">
                </div>
                <select name="status" onchange="this.form.submit()"
                        class="rounded-lg border-[#DDD5C2] text-sm focus:ring-[#D4A73C] focus:border-[#D4A73C]">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-[#1F2A24] bg-[#F6F3EC] rounded-lg hover:bg-[#EFEAE0] transition">Terapkan</button>
                @if (request('search') || request('status'))
                    <a href="{{ route('superadmin.tenants.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24]">Reset</a>
                @endif
            </form>

            {{-- Grid kartu usaha --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($tenants as $t)
                    @php $owner = $t->users->first(); @endphp
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden hover:shadow-md transition flex flex-col">
                        <div class="h-1.5" style="background-color: {{ $t->primary_color ?? '#0F2E2B' }}"></div>

                        <div class="p-5 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-11 h-11 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0"
                                          style="background-color: {{ $t->primary_color ?? '#0F2E2B' }}">
                                        {{ strtoupper(substr($t->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[#1F2A24] truncate">{{ $t->name }}</p>
                                        <p class="text-xs text-[#8A8272] truncate">{{ $t->slug }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 text-[10px] font-medium uppercase px-2 py-1 rounded-full {{ $t->is_active ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                    {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="mt-4 pt-4 border-t border-[#EFEAE0] flex items-center gap-2 text-sm min-w-0">
                                <svg class="w-4 h-4 text-[#B5A97A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                @if ($owner)
                                    <span class="truncate text-[#1F2A24]">{{ $owner->name }}</span>
                                    <span class="text-[#8A8272] truncate">&middot; {{ $owner->email }}</span>
                                @else
                                    <span class="text-[#B5482E]">Belum ada akun owner</span>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="bg-[#F6F3EC] rounded-lg px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Akun</p>
                                    <p class="text-sm font-semibold text-[#1F2A24]">{{ $t->users_count }}</p>
                                </div>
                                <div class="bg-[#F6F3EC] rounded-lg px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Total Transaksi</p>
                                    <p class="text-sm font-semibold text-[#1F2A24] truncate">Rp {{ number_format($t->transactions_sum_total ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <p class="mt-3 flex items-center gap-1.5 text-xs text-[#8A8272]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Dibuat {{ $t->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-1 px-4 py-3 bg-[#FAF8F2] border-t border-[#EFEAE0]">
                            <form method="POST" action="{{ route('superadmin.tenants.impersonate', $t) }}">
                                @csrf
                                <button type="submit" title="Lihat Data" aria-label="Lihat data {{ $t->name }}"
                                        class="p-2 rounded-lg text-[#1B6E6E] hover:bg-[#E6F3F3] transition">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </form>
                            <a href="{{ route('superadmin.tenants.edit', $t) }}" title="Edit" aria-label="Edit {{ $t->name }}"
                               class="p-2 rounded-lg text-[#B5842A] hover:bg-[#FBF1DD] transition">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('superadmin.tenants.destroy', $t) }}"
                                  onsubmit="return confirm('Yakin hapus usaha ini? Semua data ikut kehapus.');">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus" aria-label="Hapus {{ $t->name }}"
                                        class="p-2 rounded-lg text-[#B5482E] hover:bg-[#FBEAE6] transition">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-10 text-center text-[#8A8272]">
                        Belum ada usaha yang cocok dengan pencarian ini.
                    </div>
                @endforelse
            </div>

            {{ $tenants->links() }}
        </div>
    </div>
</x-app-layout>