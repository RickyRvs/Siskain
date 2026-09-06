@php
    // Modal mana yang harus otomatis kebuka lagi kalau validasi gagal
    $reopenForm = old('_form');
    $reopenCreate = $reopenForm === 'create' && $errors->any();
    $reopenEditId = ($reopenForm && str_starts_with($reopenForm, 'edit-') && $errors->any())
        ? (int) str_replace('edit-', '', $reopenForm)
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2" x-data>
            <h2 class="font-semibold text-xl text-[#1F2A24]">Kelola Kasir</h2>
            <button type="button" @click="$dispatch('open-create-staff')"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                + Tambah Kasir
            </button>
        </div>
    </x-slot>

    <div class="py-6"
         x-data="{
            showCreate: {{ $reopenCreate ? 'true' : 'false' }},
            editingId: {{ $reopenEditId ?? 'null' }}
         }"
         @open-create-staff.window="showCreate = true">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide">Total Kasir</p>
                    <p class="text-2xl font-semibold text-[#1F2A24] mt-1">{{ $staff->total() }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide">Sedang Online</p>
                    <p class="text-2xl font-semibold text-[#2F6F4E] mt-1">{{ $staff->filter(fn($s) => $s->isOnline())->count() }}</p>
                </div>
                <div class="hidden sm:block bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide">Offline</p>
                    <p class="text-2xl font-semibold text-[#8A8272] mt-1">{{ $staff->filter(fn($s) => !$s->isOnline())->count() }}</p>
                </div>
            </div>

            {{-- Search cepat (client-side) --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8272]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z" />
                </svg>
                <input type="text" id="staffSearch" placeholder="Cari nama atau email kasir..."
                       class="w-full pl-9 pr-3 py-2.5 text-sm rounded-lg border border-[#E7E1D3] focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C] text-[#1F2A24] placeholder:text-[#B5AF9C]">
            </div>

            @if ($staff->count() === 0)
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-8 text-center text-[#8A8272]">
                    Belum ada akun kasir.
                </div>
            @else
                {{-- Tabel: tampil di layar md ke atas --}}
                <div class="hidden md:block bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#F6F3EC] text-[#8A8272] text-xs uppercase">
                                <tr>
                                    <th class="text-left px-5 py-3 whitespace-nowrap">Nama</th>
                                    <th class="text-left px-5 py-3 whitespace-nowrap">Email</th>
                                    <th class="text-left px-5 py-3">Akses Menu</th>
                                    <th class="text-left px-5 py-3 whitespace-nowrap">Status</th>
                                    <th class="text-right px-5 py-3 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EFEAE0]">
                                @foreach ($staff as $s)
                                    <tr class="staff-row hover:bg-[#FAF8F3] transition" data-name="{{ strtolower($s->name) }}" data-email="{{ strtolower($s->email) }}">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-[#F0E4BE] text-[#8A6420] flex items-center justify-center text-xs font-semibold shrink-0">
                                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-[#1F2A24]">{{ $s->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-[#5B5647] whitespace-nowrap">{{ $s->email }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex flex-wrap gap-1 max-w-[220px]">
                                                @forelse ($s->permissions ?? [] as $p)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#EAF3EE] text-[#2F6F4E] whitespace-nowrap">
                                                        {{ config('menus')[$p] ?? $p }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-[#8A8272]">Belum ada akses</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-medium uppercase px-1.5 py-0.5 rounded whitespace-nowrap {{ $s->isOnline() ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $s->isOnline() ? 'bg-[#2F6F4E]' : 'bg-[#B5AF9C]' }}"></span>
                                                {{ $s->isOnline() ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="editingId = {{ $s->id }}" title="Edit"
                                                        class="p-1.5 rounded-lg text-[#B5842A] hover:bg-[#F6EED9] transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75l-2.25-2.25" />
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('owner.staff.destroy', $s) }}"
                                                      onsubmit="return confirm('Yakin hapus akun kasir ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Hapus"
                                                            class="p-1.5 rounded-lg text-[#B5482E] hover:bg-[#FBE9E5] transition">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.166m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card list: tampil di layar kecil --}}
                <div class="md:hidden space-y-3">
                    @foreach ($staff as $s)
                        <div class="staff-row bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4" data-name="{{ strtolower($s->name) }}" data-email="{{ strtolower($s->email) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-[#F0E4BE] text-[#8A6420] flex items-center justify-center text-sm font-semibold shrink-0">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-[#1F2A24] truncate">{{ $s->name }}</p>
                                        <p class="text-xs text-[#8A8272] truncate">{{ $s->email }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium uppercase px-1.5 py-0.5 rounded whitespace-nowrap shrink-0 {{ $s->isOnline() ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $s->isOnline() ? 'bg-[#2F6F4E]' : 'bg-[#B5AF9C]' }}"></span>
                                    {{ $s->isOnline() ? 'Online' : 'Offline' }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-1 mt-3">
                                @forelse ($s->permissions ?? [] as $p)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#EAF3EE] text-[#2F6F4E] whitespace-nowrap">
                                        {{ config('menus')[$p] ?? $p }}
                                    </span>
                                @empty
                                    <span class="text-xs text-[#8A8272]">Belum ada akses</span>
                                @endforelse
                            </div>

                            <div class="flex items-center justify-end gap-1.5 mt-4 pt-3 border-t border-[#EFEAE0]">
                                <button type="button" @click="editingId = {{ $s->id }}" title="Edit"
                                        class="p-2 rounded-lg text-[#B5842A] hover:bg-[#F6EED9] transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75l-2.25-2.25" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('owner.staff.destroy', $s) }}"
                                      onsubmit="return confirm('Yakin hapus akun kasir ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus"
                                            class="p-2 rounded-lg text-[#B5482E] hover:bg-[#FBE9E5] transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.166m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p id="staffEmptySearch" class="hidden text-center text-sm text-[#8A8272] py-6">Tidak ada kasir yang cocok dengan pencarian.</p>
            @endif

            {{ $staff->links() }}
        </div>

        {{-- ===================== MODAL: TAMBAH KASIR ===================== --}}
        <div x-show="showCreate" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="showCreate = false">
            <div x-show="showCreate" x-transition.opacity class="absolute inset-0 bg-black/40" @click="showCreate = false"></div>

            <div x-show="showCreate"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-xl shadow-xl ring-1 ring-[#E7E1D3] w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#EFEAE0]">
                    <h3 class="font-semibold text-lg text-[#1F2A24]">Tambah Akun Kasir</h3>
                    <button type="button" @click="showCreate = false" class="text-[#8A8272] hover:text-[#1F2A24]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.staff.store') }}" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_form" value="create">

                    <div>
                        <x-input-label for="create_name" value="Nama" />
                        <x-text-input id="create_name" name="name" class="block mt-1 w-full" :value="old('_form') === 'create' ? old('name') : ''" required />
                        @if (old('_form') === 'create')
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        @endif
                    </div>

                    <div>
                        <x-input-label for="create_email" value="Email" />
                        <x-text-input id="create_email" type="email" name="email" class="block mt-1 w-full" :value="old('_form') === 'create' ? old('email') : ''" required />
                        @if (old('_form') === 'create')
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        @endif
                    </div>

                    <div>
                        <x-input-label for="create_password" value="Password" />
                        <x-text-input id="create_password" type="password" name="password" class="block mt-1 w-full" required />
                        @if (old('_form') === 'create')
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        @endif
                    </div>

                    <div>
                        <x-input-label value="Hak Akses Menu" />
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($menus as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-[#5B5647] cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                           {{ in_array($key, old('_form') === 'create' ? old('permissions', []) : []) ? 'checked' : '' }}
                                           class="rounded border-[#DDD5C2] text-[#16231D] focus:ring-[#D4A73C]">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                        <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24]">Batal</button>
                        <x-primary-button class="w-full sm:w-auto justify-center">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: EDIT KASIR (satu per baris) ===================== --}}
        @foreach ($staff as $s)
            <div x-show="editingId === {{ $s->id }}" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="editingId = null">
                <div x-show="editingId === {{ $s->id }}" x-transition.opacity class="absolute inset-0 bg-black/40" @click="editingId = null"></div>

                <div x-show="editingId === {{ $s->id }}"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-xl shadow-xl ring-1 ring-[#E7E1D3] w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-[#EFEAE0]">
                        <h3 class="font-semibold text-lg text-[#1F2A24]">Edit Akun Kasir — {{ $s->name }}</h3>
                        <button type="button" @click="editingId = null" class="text-[#8A8272] hover:text-[#1F2A24]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('owner.staff.update', $s) }}" class="p-6 space-y-5">
                        @csrf @method('PUT')
                        <input type="hidden" name="_form" value="edit-{{ $s->id }}">

                        @php $isReopened = old('_form') === 'edit-' . $s->id; @endphp

                        <div>
                            <x-input-label for="edit_name_{{ $s->id }}" value="Nama" />
                            <x-text-input id="edit_name_{{ $s->id }}" name="name" class="block mt-1 w-full" :value="$isReopened ? old('name') : $s->name" required />
                            @if ($isReopened)
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            @endif
                        </div>

                        <div>
                            <x-input-label for="edit_email_{{ $s->id }}" value="Email" />
                            <x-text-input id="edit_email_{{ $s->id }}" type="email" name="email" class="block mt-1 w-full" :value="$isReopened ? old('email') : $s->email" required />
                            @if ($isReopened)
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            @endif
                        </div>

                        <div>
                            <x-input-label for="edit_password_{{ $s->id }}" value="Password Baru (opsional)" />
                            <x-text-input id="edit_password_{{ $s->id }}" type="password" name="password" class="block mt-1 w-full" placeholder="Kosongkan kalau tidak diubah" />
                            @if ($isReopened)
                                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                            @endif
                        </div>

                        <div>
                            <x-input-label value="Hak Akses Menu" />
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($menus as $key => $label)
                                    <label class="flex items-center gap-2 text-sm text-[#5B5647] cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                               {{ in_array($key, $isReopened ? old('permissions', []) : ($s->permissions ?? [])) ? 'checked' : '' }}
                                               class="rounded border-[#DDD5C2] text-[#16231D] focus:ring-[#D4A73C]">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                            <button type="button" @click="editingId = null" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24]">Batal</button>
                            <x-primary-button class="w-full sm:w-auto justify-center">Simpan Perubahan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            const input = document.getElementById('staffSearch');
            if (!input) return;

            const rows = document.querySelectorAll('.staff-row');
            const emptyMsg = document.getElementById('staffEmptySearch');

            input.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                if (emptyMsg) {
                    emptyMsg.classList.toggle('hidden', visibleCount !== 0 || q === '');
                }
            });
        })();
    </script>
</x-app-layout>