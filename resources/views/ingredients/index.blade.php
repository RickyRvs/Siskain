<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Bahan Baku</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-sm p-5 max-w-xs">
                <p class="text-xs text-[#B5482E] uppercase tracking-wide mb-1">Bahan Menipis</p>
                <p class="text-2xl font-semibold text-[#B5482E]">{{ $lowStockCount }}</p>
            </div>

            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 flex flex-wrap gap-3 items-center">
                <form method="GET" class="flex flex-wrap gap-2 items-center flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan..."
                           class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                    <label class="flex items-center gap-1.5 text-sm text-[#5B5647]">
                        <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                        Hanya yang menipis
                    </label>
                    <button type="submit" class="px-3 py-1.5 text-sm rounded-lg bg-[#1F2A24] text-white">Cari</button>
                    @if (request('search') || request('low_stock'))
                        <a href="{{ route('ingredients.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24]">Reset</a>
                    @endif
                </form>
                <button type="button" x-data
                        x-on:click="$dispatch('open-modal', 'create-ingredient')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Bahan
                </button>
            </div>

            @if ($ingredients->isEmpty())
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm px-6 py-16 text-center text-sm text-[#8A8272]">
                    Belum ada bahan baku.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($ingredients as $ingredient)
                        <div class="bg-white rounded-xl ring-1 {{ $ingredient->isLowStock() ? 'ring-[#F0CFC4]' : 'ring-[#E7E1D3]' }} shadow-sm p-5 flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-[#1F2A24] truncate">{{ $ingredient->name }}</h3>
                                    <p class="text-xs text-[#8A8272] mt-0.5">Satuan: {{ $ingredient->unit }}</p>
                                </div>
                                @if ($ingredient->isLowStock())
                                    <span class="shrink-0 px-2 py-0.5 text-[11px] font-medium rounded-full bg-[#FBEAE6] text-[#B5482E]">Menipis</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg bg-[#F6F3EC] px-3 py-2">
                                    <p class="text-[11px] text-[#8A8272] mb-0.5">Stok</p>
                                    <p class="text-sm font-semibold tabular-nums {{ $ingredient->isLowStock() ? 'text-[#B5482E]' : 'text-[#1F2A24]' }}">
                                        {{ rtrim(rtrim(number_format($ingredient->stock, 2, ',', '.'), '0'), ',') }} {{ $ingredient->unit }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-[#F6F3EC] px-3 py-2">
                                    <p class="text-[11px] text-[#8A8272] mb-0.5">Min. Stok</p>
                                    <p class="text-sm font-semibold text-[#1F2A24] tabular-nums">
                                        {{ rtrim(rtrim(number_format($ingredient->min_stock, 2, ',', '.'), '0'), ',') }} {{ $ingredient->unit }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] text-[#8A8272] mb-1.5">Dipakai di resep</p>
                                @if ($ingredient->products->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($ingredient->products as $product)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-[#F6F3EC] text-[#5B5647]">{{ $product->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-[#8A8272]">—</p>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-1 pt-3 border-t border-[#E7E1D3] mt-auto">
                                <button type="button" title="Sesuaikan stok" x-data
                                        x-on:click="$dispatch('open-modal', 'adjust-stock-{{ $ingredient->id }}')"
                                        class="p-2 rounded-lg text-[#1B6E6E] hover:bg-[#EAF3EE] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </button>
                                <button type="button" title="Edit" x-data
                                        x-on:click="$dispatch('open-modal', 'edit-ingredient-{{ $ingredient->id }}')"
                                        class="p-2 rounded-lg text-[#8A6F1F] hover:bg-[#FBF3DE] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST" onsubmit="return confirm('Hapus bahan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus" class="p-2 rounded-lg text-[#B5482E] hover:bg-[#FBEAE6] transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Modal: sesuaikan stok --}}
                        <x-modal name="adjust-stock-{{ $ingredient->id }}" maxWidth="sm">
                            <form action="{{ route('ingredients.adjust-stock', $ingredient) }}" method="POST" class="p-6">
                                @csrf
                                <h3 class="font-semibold text-[#1F2A24] mb-4">Penyesuaian Stok: {{ $ingredient->name }}</h3>
                                <p class="text-xs text-[#8A8272] mb-4">Stok saat ini: {{ $ingredient->stock }} {{ $ingredient->unit }}</p>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Tipe</label>
                                    <select name="type" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm" required>
                                        <option value="in">Stok Masuk</option>
                                        <option value="out">Stok Keluar</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Qty ({{ $ingredient->unit }})</label>
                                    <input type="number" step="0.01" name="qty" min="0.01" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm" required>
                                </div>
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Catatan (opsional)</label>
                                    <input type="text" name="note" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm">
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button type="button" x-on:click="$dispatch('close-modal', 'adjust-stock-{{ $ingredient->id }}')"
                                            class="px-4 py-2 text-sm rounded-lg border border-[#DDD5C2] text-[#5B5647]">Batal</button>
                                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-[#1F2A24] text-white">Simpan</button>
                                </div>
                            </form>
                        </x-modal>

                        {{-- Modal: edit bahan --}}
                        <x-modal name="edit-ingredient-{{ $ingredient->id }}" maxWidth="md">
                            <form action="{{ route('ingredients.update', $ingredient) }}" method="POST" class="p-6">
                                @csrf @method('PUT')
                                <input type="hidden" name="form_type" value="edit">
                                <input type="hidden" name="ingredient_id" value="{{ $ingredient->id }}">

                                <h3 class="font-semibold text-[#1F2A24] mb-4">Edit Bahan Baku: {{ $ingredient->name }}</h3>

                                @php
                                    $isThisEditFailing = old('form_type') === 'edit' && (int) old('ingredient_id') === $ingredient->id;
                                @endphp

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama Bahan</label>
                                    <input type="text" name="name" value="{{ $isThisEditFailing ? old('name') : $ingredient->name }}"
                                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                    @if ($isThisEditFailing) @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Satuan</label>
                                    <input type="text" name="unit" value="{{ $isThisEditFailing ? old('unit') : $ingredient->unit }}"
                                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                    @if ($isThisEditFailing) @error('unit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Min. Stok</label>
                                    <input type="number" step="0.01" name="min_stock" value="{{ $isThisEditFailing ? old('min_stock') : $ingredient->min_stock }}"
                                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                    @if ($isThisEditFailing) @error('min_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
                                </div>

                                <p class="text-xs text-[#8A8272] mb-4">Stok saat ini: {{ $ingredient->stock }} {{ $ingredient->unit }} (ubah lewat menu Stok, bukan di sini)</p>

                                <div class="flex justify-end gap-2">
                                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-ingredient-{{ $ingredient->id }}')"
                                            class="px-4 py-2 text-sm rounded-lg border border-[#DDD5C2] text-[#5B5647]">Batal</button>
                                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-[#1F2A24] text-white">Update</button>
                                </div>
                            </form>
                        </x-modal>

                        @if ($isThisEditFailing ?? false)
                            <div x-data x-init="$nextTick(() => $dispatch('open-modal', 'edit-ingredient-{{ $ingredient->id }}'))"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div>{{ $ingredients->links() }}</div>
        </div>
    </div>

    {{-- Modal: tambah bahan --}}
    <x-modal name="create-ingredient" maxWidth="md">
        <form action="{{ route('ingredients.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="form_type" value="create">

            <h3 class="font-semibold text-[#1F2A24] mb-4">Tambah Bahan Baku</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama Bahan</label>
                <input type="text" name="name" value="{{ old('form_type') === 'create' ? old('name') : '' }}" placeholder="Misal: Susu Kental Manis"
                       class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                @if (old('form_type') === 'create') @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Satuan</label>
                <input type="text" name="unit" value="{{ old('form_type') === 'create' ? old('unit') : '' }}" placeholder="ml, gram, kg, pcs, dll"
                       class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                @if (old('form_type') === 'create') @error('unit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Stok Awal</label>
                    <input type="number" step="0.01" name="stock" value="{{ old('form_type') === 'create' ? old('stock', 0) : 0 }}"
                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                    @if (old('form_type') === 'create') @error('stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Min. Stok</label>
                    <input type="number" step="0.01" name="min_stock" value="{{ old('form_type') === 'create' ? old('min_stock', 0) : 0 }}"
                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                    @if (old('form_type') === 'create') @error('min_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror @endif
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-ingredient')"
                        class="px-4 py-2 text-sm rounded-lg border border-[#DDD5C2] text-[#5B5647]">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Simpan</button>
            </div>
        </form>
    </x-modal>

    @if ($errors->any() && old('form_type') === 'create')
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', 'create-ingredient'))"></div>
    @endif
</x-app-layout>