<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#16231D]">Masuk</h2>
        <p class="text-sm text-[#8A8272] mt-1.5">Login dulu buat lanjut ke kasir dan stok kamu.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
                           placeholder="username kamu" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-2">
                <x-text-input id="password" class="block w-full pr-10" :type="'password'" x-bind:type="showPassword ? 'text' : 'password'" name="password"
                               placeholder="Masukkan password" required autocomplete="current-password" />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-[#8A8272] hover:text-[#1F2A24]"
                    :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                >
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-[#DDD5C2] text-[#16231D] shadow-sm focus:ring-[#D4A73C]">
                <span class="text-sm text-[#5B5647]">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#1B6E6E] hover:text-[#144F4F] font-medium" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center py-3">
            Masuk
        </x-primary-button>
    </form>
</x-guest-layout>