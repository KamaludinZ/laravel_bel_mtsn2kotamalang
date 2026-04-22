<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Login Admin</h2>
        <p class="text-white/70 text-sm">Masuk untuk mengelola sistem bel sekolah</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white mb-2">Email</label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   class="block w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all"
                   placeholder="admin@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-300" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-white mb-2">Password</label>
            <div class="relative">
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       class="block w-full px-4 py-3 pr-12 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all"
                       placeholder="••••••••">
                <button type="button"
                        onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-white/70 hover:text-white transition-colors">
                    <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
        </div>

        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyeOpen = document.getElementById('eye-open');
                const eyeClosed = document.getElementById('eye-closed');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClosed.classList.add('hidden');
                }
            }
        </script>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me"
                   type="checkbox"
                   name="remember"
                   class="w-4 h-4 rounded bg-white/20 border-white/30 text-blue-600 focus:ring-2 focus:ring-white/50">
            <label for="remember_me" class="ml-2 text-sm text-white/80">Ingat saya</label>
        </div>

        <div class="space-y-3">
            <button type="submit"
                    class="w-full px-4 py-3 bg-white text-blue-900 font-semibold rounded-lg hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all">
                Masuk
            </button>

            @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-white/80 hover:text-white transition-colors">
                        Lupa password?
                    </a>
                </div>
            @endif
        </div>
    </form>
</x-guest-layout>
