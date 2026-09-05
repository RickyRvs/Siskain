<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Penyesuaian Stok</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#E7E1D3] rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-[#E7E1D3]">
                    <p class="text-sm text-[#5B5647]">Catat stok masuk (pembelian, retur) atau stok keluar (kerusakan, hilang) di luar transaksi penjualan — baik untuk produk maupun bahan baku.</p>
                </div>

                @if ($errors->any())
                    <div class="mx-6 mt-4 p-3 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('stock-movements.store') }}" method="POST" class="p-6"
                      x-data="{
                        products: {{ $products->map(fn($p) => [
                            'id' => $p->id,
                            'stock' => (int) $p->stock,
                            'variants' => $p->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'stock' => (int) $v->stock])->values(),
                        ])->values() }},
                        ingredients: {{ $ingredients->map(fn($i) => [
                            'id' => $i->id,
                            'stock' => (float) $i->stock,
                            'unit' => $i->unit,
                        ])->values() }},
                        sourceType: 'product',
                        variants: [],
                        selectedProduct: null,
                        selectedVariant: null,
                        selectedIngredient: null,
                        type: 'in',
                        qty: null,
                        get currentStock() {
                            if (this.sourceType === 'ingredient') {
                                return this.selectedIngredient ? this.selectedIngredient.stock : null;
                            }
                            return this.selectedVariant ? this.selectedVariant.stock : (this.selectedProduct ? this.selectedProduct.stock : null);
                        },
                        get currentUnit() {
                            return this.sourceType === 'ingredient' && this.selectedIngredient ? this.selectedIngredient.unit : '';
                        },
                        get insufficientStock() {
                            return this.type === 'out' && this.currentStock !== null && this.qty > this.currentStock;
                        },
                        resetSelection() {
                            this.selectedProduct = null;
                            this.selectedVariant = null;
                            this.selectedIngredient = null;
                            this.variants = [];
                            this.qty = null;
                        }
                      }">
                    @csrf

                    <!-- Toggle jenis item -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Jenis Item <span class="text-[#B94A3D]">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium cursor-pointer transition"
                                   :class="sourceType === 'product' ? 'bg-[#F5EEDD] border-[#E7D9B4] text-[#8A6A2C]' : 'border-[#DDD5C2] text-[#8A8272] hover:bg-[#F6F3EC]'">
                                <input type="radio" name="source_type" value="product" x-model="sourceType" @change="resetSelection()" class="hidden">
                                Produk
                            </label>
                            <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium cursor-pointer transition"
                                   :class="sourceType === 'ingredient' ? 'bg-[#F5EEDD] border-[#E7D9B4] text-[#8A6A2C]' : 'border-[#DDD5C2] text-[#8A8272] hover:bg-[#F6F3EC]'">
                                <input type="radio" name="source_type" value="ingredient" x-model="sourceType" @change="resetSelection()" class="hidden">
                                Bahan Baku
                            </label>
                        </div>
                    </div>

                    <!-- Pilih Produk -->
                    <div class="mb-5" x-show="sourceType === 'product'" x-cloak>
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Produk <span class="text-[#B94A3D]">*</span></label>
                        <select name="product_id" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                :required="sourceType === 'product'"
                                @change="
                                    selectedProduct = products.find(p => p.id == $event.target.value) || null;
                                    variants = selectedProduct?.variants || [];
                                    selectedVariant = null;
                                ">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-5" x-show="sourceType === 'product' && variants.length > 0" x-cloak>
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Varian <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                        <select name="product_variant_id" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                @change="selectedVariant = variants.find(v => v.id == $event.target.value) || null">
                            <option value="">-- Tanpa Varian --</option>
                            <template x-for="v in variants" :key="v.id">
                                <option :value="v.id" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Pilih Bahan Baku -->
                    <div class="mb-5" x-show="sourceType === 'ingredient'" x-cloak>
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Bahan Baku <span class="text-[#B94A3D]">*</span></label>
                        <select name="ingredient_id" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                :required="sourceType === 'ingredient'"
                                @change="selectedIngredient = ingredients.find(i => i.id == $event.target.value) || null">
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                            @endforeach
                        </select>
                    </div>

                    <p class="mb-5 -mt-3 text-xs text-[#8A8272]" x-show="currentStock !== null" x-cloak>
                        Stok saat ini: <span class="font-semibold text-[#1F2A24]" x-text="currentStock"></span> <span x-text="currentUnit"></span>
                    </p>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Tipe <span class="text-[#B94A3D]">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium cursor-pointer transition"
                                   :class="type === 'in' ? 'bg-[#EAF3EE] border-[#CFE6DA] text-[#2F6F4E]' : 'border-[#DDD5C2] text-[#8A8272] hover:bg-[#F6F3EC]'">
                                <input type="radio" name="type" value="in" x-model="type" class="hidden">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
                                Stok Masuk
                            </label>
                            <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium cursor-pointer transition"
                                   :class="type === 'out' ? 'bg-[#FBEAE6] border-[#F0CFC4] text-[#B5482E]' : 'border-[#DDD5C2] text-[#8A8272] hover:bg-[#F6F3EC]'">
                                <input type="radio" name="type" value="out" x-model="type" class="hidden">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
                                Stok Keluar
                            </label>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">
                            Qty <span x-show="currentUnit" x-text="'(' + currentUnit + ')'" class="text-[#8A8371] font-normal"></span>
                            <span class="text-[#B94A3D]">*</span>
                        </label>
                        <input type="number" name="qty" x-model.number="qty"
                               :step="sourceType === 'ingredient' ? 0.01 : 1"
                               :min="sourceType === 'ingredient' ? 0.01 : 1"
                               class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                               :class="insufficientStock ? 'border-[#B94A3D]' : ''" required>
                    </div>
                    <p class="mb-5 text-xs text-[#B94A3D]" x-show="insufficientStock" x-cloak>
                        Qty melebihi stok saat ini (<span x-text="currentStock"></span> <span x-text="currentUnit"></span>).
                    </p>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Catatan <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                        <input type="text" name="note" placeholder="Contoh: retur dari supplier, barang rusak, dll."
                               class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('stock-movements.index') }}" class="px-4 py-2 text-sm font-medium rounded-lg border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F7F4EC]">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>