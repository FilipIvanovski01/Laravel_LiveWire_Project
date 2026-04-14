<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-[#F8F9FA] text-[#212529]">
        <header class="border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-3 lg:px-12 xl:px-16">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#007BFF] text-white shadow-sm">
                        <flux:icon name="shopping-bag" class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-semibold text-[#212529]">MiniCommerce</span>
                </a>

                @auth
                    <div class="flex items-center gap-3">
                        <livewire:header-cart-indicator />

                        <div x-data="{ open: false }" class="relative">
                            <button
                                type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#E5E7EB] bg-white px-3 text-sm text-[#212529] hover:bg-[#F8F9FA] focus:outline-none focus:ring-2 focus:ring-[#007BFF]"
                                x-on:click="open = !open"
                                x-on:keydown.escape.window="open = false"
                                aria-haspopup="true"
                            >
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#E9ECEF] text-xs font-semibold">
                                    {{ auth()->user()->initials() }}
                                </span>
                                <flux:icon name="chevron-down" class="h-4 w-4 text-[#6C757D]" />
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition
                                x-on:click.outside="open = false"
                                class="absolute right-0 z-30 mt-2 w-56 rounded-xl border border-[#E5E7EB] bg-white p-2 shadow-lg"
                            >
                                <a href="{{ route('profile.edit') }}" wire:navigate class="block rounded-md px-3 py-2 text-sm text-[#212529] hover:bg-[#F8F9FA]">
                                    {{ __('Profile') }}
                                </a>
                                <a href="{{ route('buyer.orders.index') }}" wire:navigate class="block rounded-md px-3 py-2 text-sm text-[#212529] hover:bg-[#F8F9FA]">
                                    {{ __('Orders') }}
                                </a>
                                @if (auth()->user()->vendor !== null)
                                    <a href="{{ route('vendor.products.index') }}" wire:navigate class="block rounded-md px-3 py-2 text-sm text-[#212529] hover:bg-[#F8F9FA]">
                                        {{ __('Vendor dashboard') }}
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[#E5E7EB] pt-1">
                                    @csrf
                                    <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-sm text-[#DC3545] hover:bg-[#FFF5F5]">
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <flux:button variant="primary" class="bg-[#007BFF] hover:bg-[#0069d9]" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                @endauth
            </div>
        </header>

        <div class="mx-auto flex w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-10">
            {{ $slot }}
        </div>

        <footer class="border-t border-gray-200 bg-gray-50">
            <div class="mx-auto w-full max-w-7xl px-6 py-12 text-sm text-gray-600 lg:px-12 xl:px-16">
                <div class="grid grid-cols-1 gap-10 text-center md:grid-cols-3 md:text-left">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2">
                            <flux:icon name="shopping-bag" class="h-4 w-4 text-[#007BFF]" />
                            <span class="font-semibold text-[#212529]">MiniCommerce</span>
                        </div>
                        <p>{{ __('A modern multi-vendor marketplace for everyday shopping.') }}</p>
                    </div>

                    <div class="space-y-2">
                        <p class="font-medium text-[#212529]">{{ __('Explore') }}</p>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('home') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Marketplace') }}</a>
                            @auth
                                <a href="{{ route('cart.index') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Cart') }}</a>
                                <a href="{{ route('buyer.orders.index') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Orders') }}</a>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="font-medium text-[#212529]">{{ __('Account') }}</p>
                        <div class="flex flex-col gap-2">
                            @auth
                                <a href="{{ route('profile.edit') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Profile') }}</a>
                                @if (auth()->user()->vendor !== null)
                                    <a href="{{ route('vendor.products.index') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Vendor dashboard') }}</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" wire:navigate class="hover:text-[#007BFF]">{{ __('Login') }}</a>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-gray-200 pt-4 text-center text-xs text-gray-500 md:text-left">
                    &copy; 2026 {{ __('MiniCommerce. All rights reserved.') }}
                </div>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
