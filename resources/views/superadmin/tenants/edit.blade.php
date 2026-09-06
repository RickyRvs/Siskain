<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-[#1F2A24] truncate">Edit Usaha — {{ $tenant->name }}</h2>
            <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24] shrink-0">Lihat Detail &rarr;</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 sm:p-6">
                <form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}" class="space-y-5">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama Sistem / Usaha" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $tenant->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="title" value="Judul Besar" />
                        <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $tenant->title)" />
                    </div>

                    <div>
                        <x-input-label for="primary_color" value="Warna Utama" />
                        <div class="mt-1 flex items-center gap-3">
                            <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color) }}"
                                   class="h-10 w-16 rounded-lg border-[#DDD5C2] cursor-pointer shrink-0">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold text-white shrink-0"
                                  style="background-color: {{ $tenant->primary_color ?? '#0F2E2B' }}">
                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>

                    <div x-data="{
                        plan: '{{ old('subscription_plan', $tenant->subscription_plan) }}',
                        expiresAt: '{{ old('subscription_expires_at', optional($tenant->subscription_expires_at)->toDateString()) }}'
                    }">
                        <x-input-label for="subscription_plan" value="Paket Langganan" />
                        <select id="subscription_plan" name="subscription_plan" x-model="plan"
                                class="block mt-1 w-full rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                        <x-input-error :messages="$errors->get('subscription_plan')" class="mt-1" />

                        <div x-show="plan !== 'lifetime'" x-cloak class="mt-4">
                            <x-input-label for="subscription_expires_at" value="Aktif Sampai" />
                            <input type="date" id="subscription_expires_at" name="subscription_expires_at" x-model="expiresAt" :required="plan !== 'lifetime'"
                                   class="block mt-1 w-full rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <x-input-error :messages="$errors->get('subscription_expires_at')" class="mt-1" />
                            <p class="text-xs text-[#8A8272] mt-1">Bisa juga diperpanjang cepat lewat tombol "Perpanjang" di halaman detail.</p>
                        </div>
                        <p x-show="plan === 'lifetime'" x-cloak class="text-xs text-[#8A8272] mt-1">Akses selamanya, tidak ada tanggal kadaluarsa.</p>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                               class="rounded border-[#DDD5C2] text-[#16231D] shadow-sm focus:ring-[#D4A73C]">
                        <span class="text-sm text-[#5B5647]">Usaha aktif</span>
                    </label>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-2">
                        <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24] text-center">Batal</a>
                        <x-primary-button class="justify-center">Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>