<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-[#1F2A24]">Kelola Kasir</h2>
            <a href="{{ route('owner.staff.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                + Tambah Kasir
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#F6F3EC] text-[#8A8272] text-xs uppercase">
                            <tr>
                                <th class="text-left px-5 py-3 whitespace-nowrap">Nama</th>
                                <th class="text-left px-5 py-3 whitespace-nowrap">Email</th>
                                <th class="text-left px-5 py-3">Akses Menu</th>
                                <th class="text-left px-5 py-3 whitespace-nowrap">Status</th>
                                <th class="text-right px-5 py-3 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EFEAE0]">
                            @forelse ($staff as $s)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-[#1F2A24] whitespace-nowrap">{{ $s->name }}</td>
                                    <td class="px-5 py-3 text-[#5B5647] whitespace-nowrap">{{ $s->email }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-[220px]">
                                            @forelse ($s->permissions ?? [] as $p)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#EAF3EE] text-[#2F6F4E] whitespace-nowrap">
                                                    {{ config('menus')[$p] ?? $p }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-[#8A8272]">Belum ada akses</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[10px] font-medium uppercase px-1.5 py-0.5 rounded whitespace-nowrap {{ $s->isOnline() ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#F2F2F2] text-[#8A8272]' }}">
                                            {{ $s->isOnline() ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                                        <a href="{{ route('owner.staff.edit', $s) }}" class="text-[#B5842A] hover:text-[#8A6420] font-medium">Edit</a>
                                        <form method="POST" action="{{ route('owner.staff.destroy', $s) }}" class="inline"
                                              onsubmit="return confirm('Yakin hapus akun kasir ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-[#B5482E] hover:text-[#8A3520] font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-[#8A8272]">Belum ada akun kasir.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $staff->links() }}
        </div>
    </div>
</x-app-layout>