<?php

namespace App\Livewire;

use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderCartIndicator extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        if (! Auth::check()) {
            $this->count = 0;

            return;
        }

        $this->count = (int) Cart::query()
            ->where('user_id', Auth::id())
            ->withCount('items')
            ->value('items_count');
    }

    public function render()
    {
        return view('livewire.header-cart-indicator');
    }
}
