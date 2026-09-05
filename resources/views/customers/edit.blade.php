<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Edit Customer</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-[#E7E1D2] flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#F3E7C4] text-[#8A6D1D] flex items-center justify-center text-sm font-semibold shrink-0">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <p class="text-sm text-[#5B5647] min-w-0">Mengubah data <span class="font-medium text-[#1F2A24]">{{ $customer->name }}</span></p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6">
                    @csrf @method('PUT')

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Nama <span class="text-[#B94A3D]">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                            class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C] @error('name') border-[#B94A3D] @enderror"
                            required>
                        @error('name') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Telepon <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                            class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C] @error('phone') border-[#B94A3D] @enderror">
                        @error('phone') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Alamat <span class="text-[#8A8371] font-normal">(opsional)</span></label>
                        <textarea name="address" rows="3"
                            class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C] @error('address') border-[#B94A3D] @enderror">{{ old('address', $customer->address) }}</textarea>
                        @error('address') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <a href="{{ route('customers.index') }}" class="text-center px-4 py-2 text-sm font-medium rounded-lg border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F7F4EC]">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>