<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24]">Pengaturan Sistem</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4"
             x-data="{
                name: @js(old('name', $tenant->name)),
                title: @js(old('title', $tenant->title)),
                color: @js(old('primary_color', $tenant->primary_color ?: '#16231D')),
                logoUrl: @js($tenant->logo_path ? Storage::url($tenant->logo_path) : null),
                fileName: null,
                onLogoChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.fileName = file.name;
                    this.logoUrl = URL.createObjectURL(file);
                }
             }">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- Live preview --}}
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-3">Pratinjau Tampilan</p>
                <div class="rounded-xl px-5 py-4 flex items-center gap-3 transition-colors" :style="'background-color: ' + color">
                    <template x-if="logoUrl">
                        <img :src="logoUrl" alt="Logo" class="h-10 w-10 rounded-lg object-cover bg-white/10 shrink-0">
                    </template>
                    <template x-if="!logoUrl">
                        <div class="h-10 w-10 rounded-lg bg-white/15 flex items-center justify-center text-white/70 text-xs shrink-0">
                            Logo
                        </div>
                    </template>
                    <div class="min-w-0">
                        <p class="font-semibold text-white truncate" x-text="name || 'Nama Sistem'"></p>
                        <p class="text-xs text-white/80 truncate" x-text="title || 'Judul besar akan tampil di sini'"></p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('owner.settings.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')

                {{-- Identitas --}}
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6 space-y-5">
                    <h3 class="font-semibold text-[#1F2A24]">Identitas Sistem</h3>

                    <div>
                        <x-input-label for="name" value="Nama Sistem" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" x-model="name" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="title" value="Judul Besar" />
                        <x-text-input id="title" name="title" class="block mt-1 w-full" x-model="title" />
                        <p class="text-xs text-[#8A8272] mt-1">Tampil sebagai sub-judul di bawah nama sistem.</p>
                    </div>
                </div>

                {{-- Tampilan --}}
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-6 space-y-5">
                    <h3 class="font-semibold text-[#1F2A24]">Tampilan</h3>

                    <div>
                        <x-input-label for="primary_color" value="Warna Utama" />
                        <div class="mt-1 flex items-center gap-3">
                            <input type="color" id="primary_color" name="primary_color" x-model="color"
                                   class="h-10 w-14 rounded-lg border border-[#DDD5C2] cursor-pointer p-0.5">
                            <input type="text" x-model="color" maxlength="7"
                                   class="w-32 rounded-lg border-[#DDD5C2] text-sm text-[#1F2A24] focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                   placeholder="#16231D">
                        </div>
                        <x-input-error :messages="$errors->get('primary_color')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Logo" />
                        <label for="logo"
                               class="mt-1 flex items-center gap-4 border border-dashed border-[#DDD5C2] rounded-xl p-4 cursor-pointer hover:border-[#D4A73C] hover:bg-[#FAF8F3] transition">
                            <template x-if="logoUrl">
                                <img :src="logoUrl" alt="Logo" class="h-14 w-14 rounded-lg object-cover ring-1 ring-[#E7E1D3] shrink-0">
                            </template>
                            <template x-if="!logoUrl">
                                <div class="h-14 w-14 rounded-lg bg-[#F6F3EC] flex items-center justify-center text-[#B5AF9C] shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 8.25V15A2.25 2.25 0 005.25 17.25h13.5A2.25 2.25 0 0021 15V8.25m-18 0A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25m-18 0h18M8.25 6h7.5" />
                                    </svg>
                                </div>
                            </template>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24]">Klik untuk ganti logo</p>
                                <p class="text-xs text-[#8A8272] mt-0.5" x-text="fileName || 'PNG atau JPG, disarankan rasio 1:1'"></p>
                            </div>
                            <input type="file" id="logo" name="logo" accept="image/*" class="hidden" @change="onLogoChange($event)">
                        </label>
                        <x-input-error :messages="$errors->get('logo')" class="mt-1" />
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>