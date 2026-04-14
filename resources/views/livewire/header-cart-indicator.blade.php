<flux:button
    :href="route('cart.index')"
    wire:navigate
    class="relative h-10 border border-[#E5E7EB] bg-white px-3 text-[#212529] hover:bg-[#F8F9FA] focus:ring-2 focus:ring-[#007BFF]"
    icon="shopping-cart"
>
    {{ __('Cart') }}
    @if ($count > 0)
        <span class="absolute -right-2 -top-2 inline-flex min-w-5 items-center justify-center rounded-full bg-[#007BFF] px-1.5 py-0.5 text-xs font-semibold text-white shadow-sm">
            {{ $count }}
        </span>
    @endif
</flux:button>
