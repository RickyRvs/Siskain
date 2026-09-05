<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Edit Akun Kasir — {{ $staff->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6">
                <form method="POST" action="{{ route('owner.staff.update', $staff) }}" class="space-y-5">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $staff->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $staff->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password Baru (opsional)" />
                        <x-text-input id="password" type="password" name="password" class="block mt-1 w-full" placeholder="Kosongkan kalau tidak diubah" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Hak Akses Menu" />
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($menus as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-[#5B5647] cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                           {{ in_array($key, old('permissions', $staff->permissions ?? [])) ? 'checked' : '' }}
                                           class="rounded border-[#DDD5C2] text-[#16231D] focus:ring-[#D4A73C]">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                        <a href="{{ route('owner.staff.index') }}" class="text-center px-4 py-2 text-sm font-medium text-[#8A8272] hover:text-[#1F2A24]">Batal</a>
                        <x-primary-button class="w-full sm:w-auto justify-center">Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>