<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Varian - {{ $product->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <div class="mb-4">
                <a href="{{ route('products.variants.create', $product) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">+ Tambah Varian</a>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Varian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($variants as $variant)
                            <tr>
                                <td class="px-6 py-4">{{ $variant->name }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($variant->price_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">{{ $variant->stock }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('variants.edit', $variant) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form action="{{ route('variants.destroy', $variant) }}" method="POST" class="inline" onsubmit="return confirm('Hapus varian ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada varian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:underline">&larr; Kembali ke Produk</a>
            </div>
        </div>
    </div>
</x-app-layout>
