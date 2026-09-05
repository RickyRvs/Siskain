<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Customer</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="px-4 py-3 bg-[#EAF3EC] border border-[#BFDCC7] text-[#1F5C33] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 bg-[#FBEAE7] border border-[#EFC3BA] text-[#8F372D] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                <form method="GET" class="flex gap-2 flex-1">
                    <div class="relative flex-1 sm:max-w-xs">
                        <svg class="w-4 h-4 text-[#9C9280] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari customer..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]">
                    </div>
                    <button type="submit" class="shrink-0 px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg text-[#5B5647] hover:bg-[#F7F4EC]">Cari</button>
                    @if (request('search'))
                        <a href="{{ route('customers.index') }}" class="shrink-0 px-2 py-2 text-sm text-[#8A8371] hover:text-[#1F2A24]">Reset</a>
                    @endif
                </form>
                <div class="flex gap-2 sm:ml-auto">
                    <a href="{{ route('customers.piutang') }}"
                        class="flex-1 sm:flex-none text-center px-4 py-2 text-sm font-medium rounded-lg border border-[#1F2A24] text-[#1F2A24] hover:bg-[#1F2A24] hover:text-white transition-colors">
                        Piutang
                    </a>
                    <button type="button" x-data
                        x-on:click="$dispatch('open-modal', 'create-customer')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Customer
                    </button>
                </div>
            </div>

            @if ($customers->isEmpty())
                <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm p-14 text-center">
                    <p class="text-[#5B5647] text-sm mb-2">Belum ada customer.</p>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'create-customer')"
                        class="text-sm text-[#D4A73C] font-medium hover:underline">+ Tambah customer pertama</button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($customers as $customer)
                        @php $piutang = $customer->totalPiutang(); @endphp
                        <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm p-5 flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-[#F3E7C4] text-[#8A6D1D] flex items-center justify-center text-sm font-semibold shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-[#1F2A24] truncate">{{ $customer->name }}</h3>
                                        @if ($piutang > 0)
                                            <span class="text-xs font-medium text-[#B94A3D]">Piutang Rp {{ number_format($piutang, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-xs font-medium text-[#1F5C33]">Lunas</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" title="Edit" x-data
                                        x-on:click="$dispatch('open-modal', 'edit-customer-{{ $customer->id }}')"
                                        class="p-2 rounded-lg text-[#8A6D1D] hover:bg-[#F3E7C4]/60 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus customer ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-2 rounded-lg text-[#B94A3D] hover:bg-[#FBEAE7] transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-sm text-[#5B5647] pt-3 border-t border-[#F0ECE0]">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-[#9C9280]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                    <span>{{ $customer->phone ?? '—' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-[#9C9280] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span>{{ $customer->address ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Modal: edit customer --}}
                        @php
                            $isThisEditFailing = old('form_type') === 'edit' && (int) old('customer_id') === $customer->id;
                        @endphp
                        <x-modal name="edit-customer-{{ $customer->id }}" maxWidth="md">
                            <div class="px-6 pt-6 pb-2 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#F3E7C4] text-[#8A6D1D] flex items-center justify-center text-sm font-semibold shrink-0">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <p class="text-sm text-[#5B5647] min-w-0">Mengubah data <span class="font-medium text-[#1F2A24]">{{ $customer->name }}</span></p>
                            </div>
                            <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6 pt-4">
                                @csrf @method('PUT')
                                <input type="hidden" name="form_type" value="edit">
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama <span class="text-[#B94A3D]">*</span></label>
                                    <input type="text" name="name" value="{{ $isThisEditFailing ? old('name') : $customer->name }}"
                                        class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                                        required>
                                    @if ($isThisEditFailing) @error('name') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
                                </div>

                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Telepon <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                                    <input type="text" name="phone" value="{{ $isThisEditFailing ? old('phone') : $customer->phone }}"
                                        class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]">
                                    @if ($isThisEditFailing) @error('phone') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Alamat <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                                    <textarea name="address" rows="3"
                                        class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]">{{ $isThisEditFailing ? old('address') : $customer->address }}</textarea>
                                    @if ($isThisEditFailing) @error('address') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-customer-{{ $customer->id }}')"
                                        class="px-4 py-2 text-sm font-medium rounded-lg border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F7F4EC]">Batal</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Update</button>
                                </div>
                            </form>
                        </x-modal>

                        @if ($isThisEditFailing)
                            <div x-data x-init="$nextTick(() => $dispatch('open-modal', 'edit-customer-{{ $customer->id }}'))"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div>{{ $customers->links() }}</div>
        </div>
    </div>

    {{-- Modal: tambah customer --}}
    <x-modal name="create-customer" maxWidth="lg">
        <div class="px-6 pt-6 pb-2">
            <p class="text-sm text-[#5B5647]">Isi data customer baru. Field bertanda <span class="text-[#B94A3D]">*</span> wajib diisi.</p>
        </div>
        <form action="{{ route('customers.store') }}" method="POST" class="p-6 pt-4">
            @csrf
            <input type="hidden" name="form_type" value="create">

            <div class="mb-5">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama <span class="text-[#B94A3D]">*</span></label>
                <input type="text" name="name" value="{{ old('form_type') === 'create' ? old('name') : '' }}"
                    class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                    placeholder="Nama lengkap customer" required>
                @if (old('form_type') === 'create') @error('name') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Telepon <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                <input type="text" name="phone" value="{{ old('form_type') === 'create' ? old('phone') : '' }}"
                    class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                    placeholder="08xxxxxxxxxx">
                @if (old('form_type') === 'create') @error('phone') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Alamat <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                <textarea name="address" rows="3"
                    class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                    placeholder="Alamat customer">{{ old('form_type') === 'create' ? old('address') : '' }}</textarea>
                @if (old('form_type') === 'create') @error('address') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-customer')"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F7F4EC]">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Simpan</button>
            </div>
        </form>
    </x-modal>

    @if ($errors->any() && old('form_type') === 'create')
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', 'create-customer'))"></div>
    @endif
</x-app-layout>