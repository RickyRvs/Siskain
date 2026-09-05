<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Tambah Usaha Baru</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6">
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
                                <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', '#0F2E2B') }}"
                                       class="block mt-1 h-10 w-20 rounded-lg border-[#DDD5C2] cursor-pointer">
                            </div>
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
                                <x-input-error :messages="$errors->get('owner_password')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24]">Batal</a>
                        <x-primary-button>Simpan Usaha</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>