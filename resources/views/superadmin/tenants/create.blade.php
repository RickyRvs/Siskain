<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Tambah Usaha Baru</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 sm:p-6">
                <form method="POST" action="{{ route('superadmin.tenants.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <h3 class="text-sm font-semibold text-[#1F2A24] mb-3">Data Usaha</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" value="Nama Sistem / Usaha" />
                                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="title" value="Judul Besar (opsional, tampil di landing/login)" />
                                <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title')" />
                            </div>
                            <div>
                                <x-input-label for="primary_color" value="Warna Utama" />
                                <div class="mt-1 flex items-center gap-3">
                                    <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', '#0F2E2B') }}"
                                           class="h-10 w-16 rounded-lg border-[#DDD5C2] cursor-pointer shrink-0">
                                    <p class="text-xs text-[#8A8272]">Dipakai untuk aksen kartu &amp; avatar usaha ini di panel superadmin.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-[#EFEAE0]">

                    <div x-data="{
                        plan: '{{ old('subscription_plan', 'bulanan') }}',
                        expiresAt: '{{ old('subscription_expires_at', now()->addMonth()->toDateString()) }}',
                        setDefaultExpiry() {
                            if (this.plan === 'bulanan') this.expiresAt = '{{ now()->addMonth()->toDateString() }}';
                            if (this.plan === 'tahunan') this.expiresAt = '{{ now()->addYear()->toDateString() }}';
                        }
                    }">
                        <h3 class="text-sm font-semibold text-[#1F2A24] mb-3">Langganan</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="subscription_plan" value="Paket" />
                                <select id="subscription_plan" name="subscription_plan" x-model="plan" @change="setDefaultExpiry()"
                                        class="block mt-1 w-full rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    <option value="bulanan">Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                    <option value="lifetime">Lifetime</option>
                                </select>
                                <x-input-error :messages="$errors->get('subscription_plan')" class="mt-1" />
                            </div>
                            <div x-show="plan !== 'lifetime'" x-cloak>
                                <x-input-label for="subscription_expires_at" value="Aktif Sampai" />
                                <input type="date" id="subscription_expires_at" name="subscription_expires_at" x-model="expiresAt" :required="plan !== 'lifetime'"
                                       class="block mt-1 w-full rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                <x-input-error :messages="$errors->get('subscription_expires_at')" class="mt-1" />
                            </div>
                            <p x-show="plan === 'lifetime'" x-cloak class="text-xs text-[#8A8272]">Akses selamanya, tidak ada tanggal kadaluarsa.</p>
                        </div>
                    </div>

                    <hr class="border-[#EFEAE0]">

                    <div>
                        <h3 class="text-sm font-semibold text-[#1F2A24] mb-3">Akun Owner</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="owner_name" value="Nama Owner" />
                                <x-text-input id="owner_name" name="owner_name" class="block mt-1 w-full" :value="old('owner_name')" required />
                                <x-input-error :messages="$errors->get('owner_name')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="owner_email" value="Email Owner" />
                                <x-text-input id="owner_email" type="email" name="owner_email" class="block mt-1 w-full" :value="old('owner_email')" required />
                                <x-input-error :messages="$errors->get('owner_email')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="owner_password" value="Password Owner" />
                                <x-text-input id="owner_password" type="password" name="owner_password" class="block mt-1 w-full" required />
                                <p class="text-xs text-[#8A8272] mt-1">Minimal 8 karakter.</p>
                                <x-input-error :messages="$errors->get('owner_password')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-2">
                        <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24] text-center">Batal</a>
                        <x-primary-button class="justify-center">Simpan Usaha</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>