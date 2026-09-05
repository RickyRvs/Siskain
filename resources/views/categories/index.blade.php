<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Kategori Produk</h2>
        </div>
    </x-slot>

    <div class="py-6"
         x-data="{
             search: '',
             deleting: { name: '', url: '' },
             openDelete(e) {
                 this.deleting = { name: e.currentTarget.dataset.name, url: e.currentTarget.dataset.url };
                 $dispatch('open-modal', 'delete-category');
             },
         }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if (session('success'))
                <div class="p-4 bg-[#EAF3EE] text-[#2F6F4E] rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-[#FBEAE4] text-[#B5482E] rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Toolbar -->
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8272]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari kategori..."
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                </div>
                <button
                    type="button"
                    @click="$dispatch('open-modal', 'create-category')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kategori
                </button>
            </div>

            <!-- Grid kategori -->
            @if ($categories->isEmpty())
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-10 text-center text-sm text-[#8A8272]">
                    Belum ada kategori.
                    <button type="button" @click="$dispatch('open-modal', 'create-category')" class="text-[#B5842A] font-medium hover:underline">Tambah yang pertama</button>.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @php
                        $palette = [
                            ['bg-[#FBF0D9]', 'text-[#B5842A]', 'bg-[#D4A73C]'],
                            ['bg-[#EAF3EE]', 'text-[#2F6F4E]', 'bg-[#2F6F4E]'],
                            ['bg-[#E4EEF2]', 'text-[#1B6E6E]', 'bg-[#1B6E6E]'],
                            ['bg-[#FBEAE4]', 'text-[#B5482E]', 'bg-[#B5482E]'],
                            ['bg-[#F0EDF7]', 'text-[#5B4E8A]', 'bg-[#5B4E8A]'],
                            ['bg-[#F6F3EC]', 'text-[#1F2A24]', 'bg-[#1F2A24]'],
                        ];
                    @endphp
                    @foreach ($categories as $i => $category)
                        @php [$dotBg, $dotText, $accent] = $palette[$i % count($palette)]; @endphp
                        <div
                            x-show="!search || '{{ Str::lower($category->name) }}'.includes(search.toLowerCase())"
                            @click="$dispatch('open-modal', 'view-products-{{ $category->id }}')"
                            class="group relative bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl overflow-hidden flex flex-col hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                        >
                            <!-- accent bar -->
                            <div class="h-1 w-full {{ $accent }}"></div>

                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center text-base font-semibold {{ $dotBg }} {{ $dotText }}">
                                            {{ Str::upper(Str::substr($category->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-[#1F2A24] leading-snug">{{ $category->name }}</p>
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-medium {{ $category->products_count > 0 ? 'bg-[#F6F3EC] text-[#1F2A24]' : 'bg-[#F6F3EC] text-[#8A8272]' }}">
                                                {{ $category->products_count }} produk
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Icon actions -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            type="button"
                                            @click.stop="$dispatch('open-modal', 'edit-category-{{ $category->id }}')"
                                            title="Edit kategori"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-[#8A8272] hover:text-[#1B6E6E] hover:bg-[#E4EEF2] transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click.stop="openDelete($event)"
                                            data-name="{{ $category->name }}"
                                            data-url="{{ route('categories.destroy', $category) }}"
                                            title="Hapus kategori"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-[#8A8272] hover:text-[#B5482E] hover:bg-[#FBEAE4] transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Daftar Produk (muncul saat card diklik) --}}
                        <x-modal name="view-products-{{ $category->id }}" max-width="md">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center text-sm font-semibold {{ $dotBg }} {{ $dotText }}">
                                        {{ Str::upper(Str::substr($category->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <h3 class="font-semibold text-[#1F2A24] leading-tight">{{ $category->name }}</h3>
                                        <p class="text-xs text-[#8A8272]">{{ $category->products_count }} produk</p>
                                    </div>
                                </div>
                                <button type="button" @click="$dispatch('close-modal', 'view-products-{{ $category->id }}')" class="text-[#8A8272] hover:text-[#1F2A24]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="p-6 max-h-96 overflow-y-auto">
                                @if ($category->products->isEmpty())
                                    <p class="text-sm text-[#8A8272] text-center py-6">Belum ada produk di kategori ini.</p>
                                @else
                                    <ul class="divide-y divide-[#E7E1D3]">
                                        @foreach ($category->products as $product)
                                            <li class="py-2.5 flex items-center gap-3">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $accent }} shrink-0"></span>
                                                <span class="text-sm text-[#1F2A24]">{{ $product->name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="px-6 py-4 border-t border-[#E7E1D3] flex justify-end">
                                <button type="button" @click="$dispatch('close-modal', 'view-products-{{ $category->id }}')"
                                        class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Tutup</button>
                            </div>
                        </x-modal>

                        {{-- Modal Edit Kategori (satu per kartu) --}}
                        <x-modal name="edit-category-{{ $category->id }}" max-width="md">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
                                <h3 class="font-semibold text-[#1F2A24]">Edit Kategori</h3>
                                <button type="button" @click="$dispatch('close-modal', 'edit-category-{{ $category->id }}')" class="text-[#8A8272] hover:text-[#1F2A24]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <form action="{{ route('categories.update', $category) }}" method="POST" class="p-6">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $category->id }}">
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama Kategori</label>
                                    <input type="text" name="name" value="{{ old('id') == $category->id ? old('name') : $category->name }}"
                                           class="w-full text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                           required>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="$dispatch('close-modal', 'edit-category-{{ $category->id }}')"
                                            class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Batal</button>
                                    <button type="submit"
                                            class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition text-sm">Update</button>
                                </div>
                            </form>
                        </x-modal>

                        @if ($errors->any() && old('id') == $category->id)
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-category-{{ $category->id }}' }));
                                });
                            </script>
                        @endif
                    @endforeach
                </div>

                <div>{{ $categories->links() }}</div>
            @endif
        </div>

        {{-- Modal Tambah Kategori --}}
        <x-modal name="create-category" max-width="md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
                <h3 class="font-semibold text-[#1F2A24]">Tambah Kategori</h3>
                <button type="button" @click="$dispatch('close-modal', 'create-category')" class="text-[#8A8272] hover:text-[#1F2A24]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama Kategori</label>
                    <input type="text" name="name" value="{{ !old('id') ? old('name') : '' }}" autofocus
                           class="w-full text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                           placeholder="Contoh: Makanan, Minuman, Cemilan" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close-modal', 'create-category')"
                            class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition text-sm">Simpan</button>
                </div>
            </form>
        </x-modal>

        @if ($errors->any() && !old('id'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-category' }));
                });
            </script>
        @endif

        {{-- Modal Hapus Kategori (satu, dipakai bareng lewat Alpine "deleting") --}}
        <x-modal name="delete-category" max-width="sm">
            <div class="p-6">
                <div class="w-11 h-11 rounded-full bg-[#FBEAE4] text-[#B5482E] flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="font-semibold text-[#1F2A24] mb-1">Hapus kategori?</h3>
                <p class="text-sm text-[#8A8272] mb-5">
                    Kategori <span class="font-medium text-[#1F2A24]" x-text="deleting.name"></span> akan dihapus permanen dan tidak bisa dikembalikan.
                </p>
                <form :action="deleting.url" method="POST" class="flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="$dispatch('close-modal', 'delete-category')"
                            class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 bg-[#B5482E] text-white rounded-lg hover:bg-[#9C3B24] transition text-sm">Ya, Hapus</button>
                </form>
            </div>
        </x-modal>
    </div>
</x-app-layout>