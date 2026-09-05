<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#16231D]">Buat akun</h2>
        <p class="text-sm text-[#8A8272] mt-1.5">Isi datamu untuk mulai pakai Siskain.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')"
                           placeholder="Nama lengkap" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')"
                           placeholder="nama@email.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-2 w-full" type="password" name="password"
                           placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi password')" />
            <x-text-input id="password_confirmation" class="block mt-2 w-full" type="password" name="password_confirmation"
                           placeholder="Ulangi password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-3">
            Daftar
        </x-primary-button>

        <p class="text-center text-sm text-[#8A8272] pt-2">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#1B6E6E] hover:text-[#144F4F] font-medium">Masuk di sini</a>
        </p>
    </form>
</x-guest-layout>