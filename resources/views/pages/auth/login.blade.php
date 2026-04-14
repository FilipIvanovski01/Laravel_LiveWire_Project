<x-layouts::auth :title="__('Log in')">
    <div class="space-y-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    class="w-full"
                    placeholder="email@example.com"
                />
                @error('email') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
            </div>

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    class="w-full"
                    viewable
                />
                @error('password') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror

                @if (Route::has('password.request'))
                    <flux:link class="absolute inset-e-0 top-0 text-sm text-[#007BFF] hover:underline" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="pt-1">
                <flux:button variant="primary" type="submit" class="w-full bg-[#007BFF] hover:bg-[#0069d9] disabled:opacity-60" data-test="login-button" x-bind:disabled="submitting">
                    <span x-show="!submitting">{{ __('Log in') }}</span>
                    <span x-show="submitting">{{ __('Signing in...') }}</span>
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-center text-sm text-gray-600 rtl:space-x-reverse">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link class="text-[#007BFF] hover:underline" :href="route('register')" wire:navigate>{{ __('Register') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
