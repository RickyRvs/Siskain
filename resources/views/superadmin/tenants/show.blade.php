@php
    // Angka rupiah dipendekkan (Jt/M) biar muat di kartu statistik yang sempit;
    // nilai pastinya tetap ada lewat atribut title (muncul saat di-hover).
    $fmtRupiah = fn ($n) => $n >= 1_000_000_000
        ? 'Rp ' . rtrim(rtrim(number_format($n / 1_000_000_000, 1, ',', '.'), '0'), ',') . ' M'
        : ($n >= 1_000_000
            ? 'Rp ' . rtrim(rtrim(number_format($n / 1_000_000, 1, ',', '.'), '0'), ',') . ' Jt'
            : 'Rp ' . number_format($n, 0, ',', '.'));
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0"
                      style="background-color: {{ $tenant->primary_color ?? '#0F2E2B' }}">
                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                </span>
                <h2 class="font-semibold text-xl text-[#1F2A24] truncate">{{ $tenant->name }}</h2>
            </div>
            <a href="{{ route('superadmin.tenants.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24] shrink-0">&larr; Kembali ke daftar</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ showAddAccount: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-[#FBEAE6] text-[#B5482E] px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            {{-- Password baru yang baru saja dibuat/direset — tampil sekali, gak akan bisa dilihat lagi setelah ini --}}
            @if (session('revealed_password'))
                <div x-data="{ copied: false }" class="bg-[#1F2A24] rounded-xl p-5 text-white">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#D4A73C] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium">Password baru untuk <span class="font-semibold">{{ session('revealed_user_name') }}</span></p>
                            <p class="text-xs text-[#B9C2BC] mt-0.5">Catat &amp; kirim sekarang — password ini tidak akan ditampilkan lagi setelah halaman ini ditinggalkan.</p>
                            <div class="mt-3 flex items-center gap-2 flex-wrap">
                                <code id="revealed-password" class="bg-white/10 px-3 py-2 rounded-lg font-mono text-sm tracking-wide break-all">{{ session('revealed_password') }}</code>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ session('revealed_password') }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                        class="px-3 py-2 rounded-lg bg-[#D4A73C] text-[#0F2E2B] text-xs font-semibold hover:bg-[#E0B559] transition shrink-0">
                                    <span x-show="!copied">Salin</span>
                                    <span x-show="copied" x-cloak>Tersalin!</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Profil usaha --}}
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 md:col-span-2">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div class="min-w-0">
                            <h3 class="text-sm font-medium text-[#8A8272] mb-2">Profil Usaha</h3>
                            <p class="font-semibold text-[#1F2A24] truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-[#8A8272] truncate">{{ $tenant->slug }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[10px] font-medium uppercase px-2 py-1 rounded-full {{ $tenant->is_active ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="text-[10px] font-medium uppercase px-2 py-1 rounded-full {{ $tenant->subscription_badge['bg'] }} {{ $tenant->subscription_badge['text'] }}">
                                {{ $tenant->subscription_badge['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-[#EFEAE0] grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="bg-[#F6F3EC] rounded-lg px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Jumlah Akun</p>
                            <p class="text-sm font-semibold text-[#1F2A24]">{{ $tenant->users->count() }}</p>
                        </div>
                        <div class="bg-[#F6F3EC] rounded-lg px-3 py-2.5 min-w-0">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Total Transaksi (Lunas)</p>
                            <p class="text-sm font-semibold text-[#1F2A24]" title="Rp {{ number_format($tenantOmzet, 0, ',', '.') }}">{{ $fmtRupiah($tenantOmzet) }}</p>
                        </div>
                        <div class="bg-[#F6F3EC] rounded-lg px-3 py-2.5 col-span-2 sm:col-span-1">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Terdaftar Sejak</p>
                            <p class="text-sm font-semibold text-[#1F2A24]">{{ $tenant->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#FBF1DD] text-[#B5842A] text-sm font-medium rounded-lg hover:bg-[#F5E6C2] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit Usaha
                        </a>
                        <form method="POST" action="{{ route('superadmin.tenants.impersonate', $tenant) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#E6F3F3] text-[#1B6E6E] text-sm font-medium rounded-lg hover:bg-[#D9EEEE] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Masuk sebagai Tenant
                            </button>
                        </form>
                        <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant) }}"
                              onsubmit="return confirm('Yakin hapus usaha ini? Semua akun & data transaksinya ikut kehapus permanen.');" class="sm:ml-auto">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#FBEAE6] text-[#B5482E] text-sm font-medium rounded-lg hover:bg-[#F5D9D0] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" /></svg>
                                Hapus Usaha
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Status langganan --}}
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Langganan</h3>
                    <p class="text-lg font-semibold text-[#1F2A24]">{{ $tenant->subscription_plan_label }}</p>

                    @if ($tenant->subscription_plan !== \App\Models\Tenant::PLAN_LIFETIME)
                        <div class="mt-3 space-y-1.5 text-sm">
                            <div class="flex justify-between text-[#8A8272]">
                                <span>Mulai</span>
                                <span class="text-[#1F2A24]">{{ optional($tenant->subscription_started_at)->translatedFormat('d M Y') ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between text-[#8A8272]">
                                <span>Berakhir</span>
                                <span class="{{ $tenant->is_subscription_expired ? 'text-[#B5482E] font-medium' : 'text-[#1F2A24]' }}">
                                    {{ optional($tenant->subscription_expires_at)->translatedFormat('d M Y') ?? 'Belum diatur' }}
                                </span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('superadmin.tenants.renew', $tenant) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                Perpanjang {{ $tenant->subscription_plan === \App\Models\Tenant::PLAN_TAHUNAN ? '1 Tahun' : '1 Bulan' }}
                            </button>
                        </form>
                    @else
                        <p class="mt-2 text-sm text-[#8A8272]">Akses selamanya, tidak ada tanggal kadaluarsa.</p>
                    @endif
                </div>
            </div>

            {{-- Manajemen akun --}}
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-[#8A8272]">Akun ({{ $tenant->users->count() }})</h3>
                    <button type="button" @click="showAddAccount = !showAddAccount"
                            class="inline-flex items-center gap-1.5 text-sm text-[#B5842A] font-medium hover:text-[#8A6420]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambah Akun
                    </button>
                </div>

                {{-- Form tambah akun (owner tambahan / kasir) --}}
                <div x-show="showAddAccount" x-cloak x-transition class="mb-4 p-4 bg-[#FAF8F2] rounded-lg">
                    <form method="POST" action="{{ route('superadmin.tenants.users.store', $tenant) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-[#8A8272] mb-1">Nama</label>
                            <input type="text" name="name" required class="w-full text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                        </div>
                        <div>
                            <label class="block text-xs text-[#8A8272] mb-1">Email</label>
                            <input type="email" name="email" required class="w-full text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                        </div>
                        <div>
                            <label class="block text-xs text-[#8A8272] mb-1">Role</label>
                            <select name="role" class="w-full text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                <option value="kasir">Kasir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-[#8A8272] mb-1">Password (opsional)</label>
                            <input type="text" name="password" placeholder="Kosongkan untuk auto-generate" class="w-full text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                        </div>
                        <div class="sm:col-span-2 flex justify-end gap-2 pt-1">
                            <button type="button" @click="showAddAccount = false" class="px-3.5 py-2 text-sm text-[#8A8272] hover:text-[#1F2A24]">Batal</button>
                            <button type="submit" class="px-3.5 py-2 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B]">Simpan Akun</button>
                        </div>
                    </form>
                </div>

                {{-- Daftar akun --}}
                <div class="divide-y divide-[#EFEAE0]">
                    @forelse ($tenant->users as $u)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <span class="relative shrink-0">
                                    <span class="w-9 h-9 rounded-full bg-[#0F2E2B] text-white text-xs font-bold flex items-center justify-center">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                    @if ($u->last_active_at && $u->last_active_at->gte(now()->subMinutes(5)))
                                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-[#2F6F4E] ring-2 ring-white" title="Sedang online"></span>
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $u->name }}</p>
                                        <span class="shrink-0 text-[10px] font-medium uppercase px-1.5 py-0.5 rounded {{ $u->role === 'owner' ? 'bg-[#1F2A24] text-white' : 'bg-[#F6F3EC] text-[#5B5647]' }}">
                                            {{ ucfirst($u->role) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#8A8272] truncate">{{ $u->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0 pl-12 sm:pl-0">
                                <form method="POST" action="{{ route('superadmin.tenants.users.reset-password', [$tenant, $u]) }}"
                                      onsubmit="return confirm('Reset password {{ $u->name }}? Password lama langsung tidak berlaku.');">
                                    @csrf
                                    <button type="submit" title="Reset Password" aria-label="Reset password {{ $u->name }}"
                                            class="p-2 rounded-lg text-[#B5842A] hover:bg-[#FBF1DD] transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.tenants.users.destroy', [$tenant, $u]) }}"
                                      onsubmit="return confirm('Hapus akun {{ $u->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus Akun" aria-label="Hapus akun {{ $u->name }}"
                                            class="p-2 rounded-lg text-[#B5482E] hover:bg-[#FBEAE6] transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada akun.</p>
                    @endforelse
                </div>
            </div>

            {{-- Histori perubahan --}}
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Histori Perubahan Setting</h3>
                @forelse ($histories as $h)
                    <div class="flex gap-3 py-2.5 border-b border-[#EFEAE0] last:border-0">
                        <span class="w-2 h-2 rounded-full bg-[#D4A73C] mt-1.5 shrink-0"></span>
                        <div class="min-w-0">
                            <p class="text-sm text-[#1F2A24]">
                                <span class="font-medium">{{ $h->changedBy->name ?? 'Sistem' }}</span>
                                ganti <span class="font-medium">{{ $h->field }}</span>
                            </p>
                            <p class="text-xs text-[#8A8272] truncate">
                                "{{ $h->old_value ?? '(kosong)' }}" &rarr; "{{ $h->new_value ?? '(kosong)' }}"
                            </p>
                            <p class="text-xs text-[#B5A97A]">{{ $h->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada perubahan setting.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>