<x-guest-layout title="Sign in">
    <div class="auth-head">
        <img class="auth-head__emblem" src="{{ asset('assets/img/logo/favicon.svg') }}" alt="" width="44" height="44" />
        <h1 class="auth-head__title">Welcome back</h1>
        <p class="auth-head__text">Sign in to the SOV SUMMIT admin.</p>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="auth-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="auth-field">
            <div class="auth-label-row">
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                @endif
            </div>
            <div class="auth-password">
                <input id="password" class="auth-input @error('password') is-invalid @enderror" type="password" name="password"
                       required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" class="auth-password__toggle" aria-label="{{ __('Show password') }}" aria-pressed="false" data-password-toggle>
                    <svg class="auth-icon-show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                    <svg class="auth-icon-hide" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.6 10.6 0 0112 5c6.4 0 10 7 10 7a17.6 17.6 0 01-3.2 4.1M6.6 6.6A17.4 17.4 0 002 12s3.6 7 10 7a10 10 0 005.4-1.6M9.9 9.9a3 3 0 004.2 4.2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <label for="remember_me" class="auth-check">
            <input id="remember_me" type="checkbox" name="remember" @checked(old('remember'))>
            <span class="auth-check__box" aria-hidden="true"></span>
            <span>{{ __('Keep me signed in') }}</span>
        </label>

        <button type="submit" class="auth-button">
            <span>{{ __('Sign in') }}</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>

        <p class="auth-note">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 11V8a4 4 0 018 0v3" fill="none" stroke="currentColor" stroke-width="1.4"/></svg>
            Secure area for SOV SUMMIT staff only.
        </p>
    </form>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = button.parentElement.querySelector('input');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-pressed', show ? 'true' : 'false');
                button.setAttribute('aria-label', show ? @js(__('Hide password')) : @js(__('Show password')));
            });
        });
    </script>
</x-guest-layout>
