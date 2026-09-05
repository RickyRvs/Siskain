<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Varian - {{ $product->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('products.variants.store', $product) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Varian</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Misal: Merah - L" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU (opsional)</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                        @error('sku') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Modal</label>
                            <input type="number" step="0.01" name="price_modal" value="{{ old('price_modal', 0) }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            @error('price_modal') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                            <input type="number" step="0.01" name="price_jual" value="{{ old('price_jual', 0) }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            @error('price_jual') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('products.variants.index', $product) }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
