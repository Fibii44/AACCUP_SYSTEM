@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" style="color: #000435 !important;">{{ $title }}</flux:heading>
    <div class="w-16 h-1 bg-highlight mx-auto rounded-full my-2"></div>
    <flux:subheading style="color: #000435 !important;">{{ $description }}</flux:subheading>
</div>
