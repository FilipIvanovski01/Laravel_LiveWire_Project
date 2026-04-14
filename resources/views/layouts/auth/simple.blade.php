<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-50 antialiased">
        <div class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
            <div class="mx-auto w-full max-w-md space-y-5">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-3 font-medium" wire:navigate>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#007BFF] text-white shadow-sm">
                        <flux:icon name="shopping-bag" class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-semibold text-[#212529]">MiniCommerce</span>
                </a>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
