<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#16231D]">Masuk</h2>
        <p class="text-sm text-[#8A8272] mt-1.5">Login dulu buat lanjut ke kasir dan stok kamu.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')"
                           placeholder="nama@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-2 w-full" type="password" name="password"
                           placeholder="Masukkan password" required autocomplete="current-password" />
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

        @if (Route::has('register'))
            <p class="text-center text-sm text-[#8A8272] pt-2">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-[#1B6E6E] hover:text-[#144F4F] font-medium">Daftar sekarang</a>
            </p>
        @endif
    </form>
</x-guest-layout>