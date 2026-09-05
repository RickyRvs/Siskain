<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Edit Usaha — {{ $tenant->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6">
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
                        <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color) }}"
                               class="block mt-1 h-10 w-20 rounded-lg border-[#DDD5C2] cursor-pointer">
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                               class="rounded border-[#DDD5C2] text-[#16231D] shadow-sm focus:ring-[#D4A73C]">
                        <span class="text-sm text-[#5B5647]">Usaha aktif</span>
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24]">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>