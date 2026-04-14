@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="text-xl font-semibold text-[#212529]">{{ $title }}</flux:heading>
    <flux:subheading class="text-sm text-gray-500">{{ $description }}</flux:subheading>
</div>
