<x-layouts::auth :title="__('Register')">
    <div class="space-y-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <!-- Name -->
            <div>
                <flux:input
                    name="name"
                    :label="__('Name')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full"
                    :placeholder="__('Full name')"
                />
                @error('name') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
            </div>

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full"
                    placeholder="email@example.com"
                />
                @error('email') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
            </div>

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full"
                    :placeholder="__('Password')"
                    viewable
                />
                @error('password') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full"
                    :placeholder="__('Confirm password')"
                    viewable
                />
            </div>

            <div class="pt-1">
                <flux:button type="submit" variant="primary" class="w-full bg-[#007BFF] hover:bg-[#0069d9] disabled:opacity-60" data-test="register-user-button" x-bind:disabled="submitting">
                    <span x-show="!submitting">{{ __('Create account') }}</span>
                    <span x-show="submitting">{{ __('Creating account...') }}</span>
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-600">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link class="text-[#007BFF] hover:underline" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
