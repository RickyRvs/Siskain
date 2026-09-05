<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Pengaturan Sistem</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6">
                <form method="POST" action="{{ route('owner.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama Sistem" />
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

                    <div>
                        <x-input-label for="logo" value="Logo" />
                        @if ($tenant->logo_path)
                            <img src="{{ Storage::url($tenant->logo_path) }}" alt="Logo" class="h-12 mt-2 mb-2 rounded-lg ring-1 ring-[#E7E1D3]">
                        @endif
                        <input type="file" id="logo" name="logo" accept="image/*"
                               class="block mt-1 w-full text-sm text-[#5B5647] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#F6F3EC] file:text-[#1F2A24] file:text-sm">
                        <x-input-error :messages="$errors->get('logo')" class="mt-1" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>