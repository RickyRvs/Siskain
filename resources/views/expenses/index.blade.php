<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Pengeluaran</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-[#EAF3EE] text-[#2F6F4E] rounded-lg">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-[#FBEAE6] text-[#B5482E] rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-5">
                    <p class="text-xs font-medium text-[#8A8272] uppercase">Total Bulan Ini</p>
                    <p class="text-2xl font-semibold text-[#B5482E] mt-1">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap gap-2 items-center bg-white ring-1 ring-[#E7E1D3] rounded-xl p-4">
                <select name="expense_type_id" onchange="this.form.submit()" class="w-full lg:w-auto rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm">
                    <option value="">Semua Jenis</option>
                    @foreach ($allExpenseTypes as $type)
                        <option value="{{ $type->id }}" {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full lg:w-auto rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm">
                    <span class="text-xs text-[#8A8272] shrink-0">s/d</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full lg:w-auto rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm">
                </div>

                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <button type="submit" class="flex-1 lg:flex-none px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Cari</button>

                    @if (request()->anyFilled(['expense_type_id', 'date_from', 'date_to']))
                        <a href="{{ route('expenses.index') }}" class="text-sm text-[#8A8272] hover:underline shrink-0">Reset</a>
                    @endif
                </div>

                <div class="w-full lg:w-auto lg:ml-auto flex gap-2 col-span-1 sm:col-span-2 lg:col-span-1">
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-modal', 'manage-expense-types')"
                        class="flex-1 lg:flex-none px-4 py-2 bg-white ring-1 ring-[#DDD5C2] text-[#1F2A24] font-medium rounded-lg hover:bg-[#F6F3EC] transition text-sm"
                    >
                        Kelola Jenis
                    </button>
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-modal', 'create-expense')"
                        class="flex-1 lg:flex-none px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition text-sm"
                    >
                        + Catat Pengeluaran
                    </button>
                </div>
            </form>

            @if ($expenseTypes->isEmpty())
                <div class="bg-white ring-1 ring-[#FBF0DA] shadow-sm rounded-xl p-5 mb-5 text-sm text-[#B5842A]">
                    Belum ada jenis pengeluaran yang aktif. Klik <strong>Kelola Jenis</strong> buat nambahin dulu (misal: Sewa Lapak, Es Batu, Gas) sebelum bisa nyatet pengeluaran.
                </div>
            @endif

            {{-- List --}}
            @if ($expenses->isEmpty())
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-10 text-center text-[#8A8272]">
                    Belum ada pengeluaran yang cocok.
                </div>
            @else
                {{--
                    Breakpoint sengaja dipindah ke `lg` (bukan `sm`).
                    Di layar tablet (~768px), tabel 5 kolom + 2 tombol aksi jadi
                    kepenyet dan susah dipencet, jadi HP & tablet sama-sama pakai
                    tampilan kartu; tabel penuh cuma muncul di layar besar (laptop/desktop).
                --}}

                {{-- Tabel: cuma di layar besar (lg ke atas) --}}
                <div class="hidden lg:block bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl overflow-hidden">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#FAF8F2]">
                            <tr class="text-left text-[#8A8272] text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 font-medium">Tanggal</th>
                                <th class="px-4 py-3 font-medium">Jenis</th>
                                <th class="px-4 py-3 font-medium text-right">Jumlah</th>
                                <th class="px-4 py-3 font-medium">Catatan</th>
                                <th class="px-4 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EFEAE0]">
                            @foreach ($expenses as $expense)
                                <tr>
                                    <td class="px-4 py-3 text-[#1F2A24] whitespace-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-[#1F2A24] font-medium">{{ $expense->expenseType->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-[#B5482E] whitespace-nowrap">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-[#8A8272] max-w-[200px] truncate" title="{{ $expense->note }}">{{ $expense->note ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                x-data
                                                @click="$dispatch('open-modal', 'edit-expense-{{ $expense->id }}')"
                                                title="Edit"
                                                aria-label="Edit pengeluaran"
                                                class="p-1.5 rounded-md text-[#1B6E6E] hover:bg-[#E9F3F3] transition"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                                @csrf @method('DELETE')
                                                <button
                                                    type="submit"
                                                    title="Hapus"
                                                    aria-label="Hapus pengeluaran"
                                                    class="p-1.5 rounded-md text-[#B5482E] hover:bg-[#FBEAE6] transition"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

                {{-- Card list: HP & Tablet (di bawah lg) --}}
                <div class="lg:hidden space-y-2">
                    @foreach ($expenses as $expense)
                        <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $expense->expenseType->name ?? '-' }}</p>
                                    <p class="text-xs text-[#8A8272] mt-0.5">{{ $expense->expense_date->format('d/m/Y') }}</p>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <p class="text-sm font-semibold text-[#B5482E] mr-1">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                    <button
                                        type="button"
                                        x-data
                                        @click="$dispatch('open-modal', 'edit-expense-{{ $expense->id }}')"
                                        title="Edit"
                                        aria-label="Edit pengeluaran"
                                        class="p-1.5 rounded-md text-[#1B6E6E] hover:bg-[#E9F3F3] transition"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                        @csrf @method('DELETE')
                                        <button
                                            type="submit"
                                            title="Hapus"
                                            aria-label="Hapus pengeluaran"
                                            class="p-1.5 rounded-md text-[#B5482E] hover:bg-[#FBEAE6] transition"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if ($expense->note)
                                <p class="text-xs text-[#8A8272] mt-2">{{ $expense->note }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Modal Edit (satu per row) --}}
                @foreach ($expenses as $expense)
                    <x-modal name="edit-expense-{{ $expense->id }}" max-width="md">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
                            <h3 class="font-semibold text-[#1F2A24]">Edit Pengeluaran</h3>
                            <button type="button" x-data @click="$dispatch('close-modal', 'edit-expense-{{ $expense->id }}')" class="text-[#8A8272] hover:text-[#1F2A24]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form
                            action="{{ route('expenses.update', $expense) }}"
                            method="POST"
                            class="p-4 sm:p-6"
                            x-data="{
                                amountDisplay: '',
                                formatRupiah(value) {
                                    let angka = String(value).replace(/\D/g, '');
                                    if (!angka) return '';
                                    return new Intl.NumberFormat('id-ID').format(angka);
                                },
                                unformatRupiah(value) {
                                    return String(value).replace(/\D/g, '') || '0';
                                },
                                init() {
                                    this.amountDisplay = this.formatRupiah('{{ (int) $expense->amount }}');
                                }
                            }"
                        >
                            @csrf @method('PUT')

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Jenis Pengeluaran</label>
                                <select name="expense_type_id" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                    @foreach ($expenseTypes as $type)
                                        <option value="{{ $type->id }}" {{ $expense->expense_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                    @if ($expense->expenseType && !$expenseTypes->contains('id', $expense->expense_type_id))
                                        <option value="{{ $expense->expenseType->id }}" selected>{{ $expense->expenseType->name }} (nonaktif)</option>
                                    @endif
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Jumlah</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            x-model="amountDisplay"
                                            @input="amountDisplay = formatRupiah($event.target.value)"
                                            class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                            required
                                        >
                                    </div>
                                    <input type="hidden" name="amount" :value="unformatRupiah(amountDisplay)">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Tanggal</label>
                                    <input type="date" name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Catatan (opsional)</label>
                                <textarea name="note" rows="2" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">{{ $expense->note }}</textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end gap-2">
                                <button type="button" x-data @click="$dispatch('close-modal', 'edit-expense-{{ $expense->id }}')" class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition">Update</button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach

                <div class="mt-6">{{ $expenses->links() }}</div>
            @endif

        </div>
    </div>

    {{-- Modal Tambah Pengeluaran --}}
    <x-modal name="create-expense" max-width="md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
            <h3 class="font-semibold text-[#1F2A24]">Catat Pengeluaran</h3>
            <button type="button" x-data @click="$dispatch('close-modal', 'create-expense')" class="text-[#8A8272] hover:text-[#1F2A24]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form
            action="{{ route('expenses.store') }}"
            method="POST"
            class="p-4 sm:p-6"
            x-data="{
                amountDisplay: '',
                expenseTypesData: {{ $expenseTypes->map(fn ($t) => ['id' => $t->id, 'default_amount' => $t->default_amount ? (int) $t->default_amount : null])->values()->toJson() }},
                formatRupiah(value) {
                    let angka = String(value).replace(/\D/g, '');
                    if (!angka) return '';
                    return new Intl.NumberFormat('id-ID').format(angka);
                },
                unformatRupiah(value) {
                    return String(value).replace(/\D/g, '') || '0';
                },
                onTypeChange(id) {
                    const type = this.expenseTypesData.find(t => t.id == id);
                    if (type && type.default_amount && !this.amountDisplay) {
                        this.amountDisplay = this.formatRupiah(type.default_amount);
                    }
                },
                init() {
                    const oldAmount = '{{ old('amount') }}';
                    this.amountDisplay = oldAmount !== '' ? this.formatRupiah(oldAmount) : '';
                }
            }"
        >
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Jenis Pengeluaran</label>
                <select name="expense_type_id" @change="onTypeChange($event.target.value)" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($expenseTypes as $type)
                        <option value="{{ $type->id }}" {{ old('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('expense_type_id') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                @if ($expenseTypes->isEmpty())
                    <p class="text-xs text-[#B5842A] mt-1">Belum ada jenis pengeluaran. Klik "Kelola Jenis" dulu buat nambahin.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Jumlah</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            placeholder="0"
                            x-model="amountDisplay"
                            @input="amountDisplay = formatRupiah($event.target.value)"
                            class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                            required
                        >
                    </div>
                    <input type="hidden" name="amount" :value="unformatRupiah(amountDisplay)">
                    @error('amount') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Tanggal</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                    @error('expense_date') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Catatan (opsional)</label>
                <textarea name="note" rows="2" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">{{ old('note') }}</textarea>
                @error('note') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-expense')" class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition">Simpan</button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Kelola Jenis Pengeluaran --}}
    <x-modal name="manage-expense-types" max-width="lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
            <h3 class="font-semibold text-[#1F2A24]">Kelola Jenis Pengeluaran</h3>
            <button type="button" x-data @click="$dispatch('close-modal', 'manage-expense-types')" class="text-[#8A8272] hover:text-[#1F2A24]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="p-4 sm:p-6 max-h-[75vh] overflow-y-auto">
            {{-- Form tambah jenis baru --}}
            <form
                action="{{ route('expense-types.store') }}"
                method="POST"
                class="flex flex-col sm:flex-row gap-2 mb-5 pb-5 border-b border-dashed border-[#E7E1D3]"
                x-data="{
                    amountDisplay: '',
                    formatRupiah(value) {
                        let angka = String(value).replace(/\D/g, '');
                        if (!angka) return '';
                        return new Intl.NumberFormat('id-ID').format(angka);
                    },
                    unformatRupiah(value) {
                        return String(value).replace(/\D/g, '');
                    }
                }"
            >
                @csrf
                <input type="text" name="name" placeholder="Nama jenis (mis. Sewa Lapak)" class="flex-1 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm" required>
                <div class="relative sm:w-40">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                    <input
                        type="text"
                        inputmode="numeric"
                        placeholder="Nominal biasa"
                        x-model="amountDisplay"
                        @input="amountDisplay = formatRupiah($event.target.value)"
                        class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm"
                    >
                </div>
                <input type="hidden" name="default_amount" :value="unformatRupiah(amountDisplay)">
                <button type="submit" class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition text-sm whitespace-nowrap">+ Tambah</button>
            </form>

            {{-- List jenis yang udah ada --}}
            @if ($allExpenseTypes->isEmpty())
                <p class="text-sm text-[#8A8272] text-center py-6">Belum ada jenis pengeluaran, tambahin dulu di atas.</p>
            @else
                <div class="space-y-2">
                    @foreach ($allExpenseTypes as $type)
                        <div class="flex items-center gap-2 p-2.5 rounded-lg {{ $type->is_active ? 'bg-[#FAF8F2]' : 'bg-[#F6F3EC] opacity-60' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $type->name }}</p>
                                @if ($type->default_amount)
                                    <p class="text-xs text-[#8A8272]">Nominal biasa: Rp {{ number_format($type->default_amount, 0, ',', '.') }}</p>
                                @endif
                            </div>

                            <form action="{{ route('expense-types.update', $type) }}" method="POST" onsubmit="return confirm('{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }} jenis ini?')">
                                @csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $type->name }}">
                                <input type="hidden" name="default_amount" value="{{ (int) $type->default_amount }}">
                                <input type="hidden" name="is_active" value="{{ $type->is_active ? '0' : '1' }}">
                                <button type="submit" class="text-xs px-2.5 py-1 rounded-md {{ $type->is_active ? 'text-[#B5842A] hover:bg-[#FBF0DA]' : 'text-[#2F6F4E] hover:bg-[#EAF3EE]' }}">
                                    {{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <form action="{{ route('expense-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Hapus jenis ini? Cuma bisa kalau belum pernah dipakai.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2.5 py-1 rounded-md text-[#B5482E] hover:bg-[#FBEAE6]">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-modal>

    @if ($errors->any() && old('expense_type_id') !== null)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-expense' }));
            });
        </script>
    @endif
</x-app-layout>