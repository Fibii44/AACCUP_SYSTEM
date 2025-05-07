<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <style>
            /* Custom dark mode styling */
            :root.dark, .dark {
                --bg-background: #000435 !important;
            }
            
            .dark body, 
            .dark .bg-background, 
            .dark .bg-default,
            .dark [data-theme="dark"] {
                background-color: #000435 !important;
            }
        </style>
        
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
