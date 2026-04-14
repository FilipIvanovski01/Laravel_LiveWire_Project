<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header class="border-b border-zinc-200/80 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95 sm:px-6">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm">
                    <flux:icon name="shopping-bag" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <flux:heading size="sm" class="tracking-tight">MiniCommerce</flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Modern marketplace') }}</flux:text>
                </div>
            </a>

            <flux:spacer />

            @auth
                @php($cartCount = auth()->user()->cart?->items()->count() ?? 0)

                <flux:button :href="route('cart.index')" wire:navigate class="relative mr-2" icon="shopping-cart">
                    {{ __('Cart') }}
                    @if ($cartCount > 0)
                        <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                            {{ $cartCount }}
                        </span>
                    @endif
                </flux:button>

                <flux:dropdown position="bottom" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                    <flux:menu>
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                        <flux:menu.separator />
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Profile') }}
                        </flux:menu.item>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <flux:button variant="primary" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
            @endauth
        </flux:header>

        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6">
            {{ $slot }}
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
