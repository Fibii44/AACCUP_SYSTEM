<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            :root {
                --primary: #000435;
                --highlight: #FFC100;
                --text-light: #FFFFFF;
            }
            
            /* Custom BukSU styling */
            .sidebar-buksu {
                background-color: var(--primary);
                border-right: 1px solid rgba(255, 193, 0, 0.2);
            }
            
            .text-buksu-gold {
                color: var(--highlight);
            }
            
            .bg-buksu-gold {
                background-color: var(--highlight);
            }
            
            .text-buksu-navy {
                color: var(--primary);
            }
            
            .border-buksu-gold {
                border-color: var(--highlight);
            }
            
            .hover-buksu-gold:hover {
                color: var(--highlight);
            }
            
            .nav-item-active {
                background-color: rgba(255, 193, 0, 0.2);
                border-left: 3px solid var(--highlight);
            }
        </style>
    </head>
    <body class="min-h-screen bg-primary">
        <flux:sidebar sticky stashable class="sidebar-buksu">
            <flux:sidebar.toggle class="lg:hidden text-buksu-white hover-buksu-gold" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse py-4" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid text-buksu-gold">
                    <flux:navlist.item 
                        icon="home" 
                        :href="route('dashboard')" 
                        :current="request()->routeIs('dashboard')" 
                        wire:navigate 
                        class="text-buksu-white hover-buksu-gold {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                        {{ __('Dashboard') }}
                    </flux:navlist.item>
                    
                    <flux:navlist.item 
                        icon="users" 
                        :href="route('tenants')" 
                        :current="request()->routeIs('tenants')" 
                        wire:navigate 
                        class="text-buksu-white hover-buksu-gold {{ request()->routeIs('tenants') ? 'nav-item-active' : '' }}">
                        {{ __('Tenants') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item 
                    icon="folder-git-2" 
                    href="https://github.com/laravel/livewire-starter-kit" 
                    target="_blank"
                    class="text-buksu-white hover-buksu-gold">
                    {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="book-open-text" 
                    href="https://laravel.com/docs/starter-kits" 
                    target="_blank"
                    class="text-buksu-white hover-buksu-gold">
                    {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                    class="text-buksu-white border border-buksu-gold border-opacity-30"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-buksu-gold text-buksu-navy"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold text-buksu-white">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-buksu-white">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate class="text-buksu-white hover:text-buksu-gold">
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-buksu-white hover:text-buksu-gold">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden sidebar-buksu" style="background-color: #000435;">
            <flux:sidebar.toggle class="lg:hidden text-buksu-white hover-buksu-gold" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="text-buksu-white border border-buksu-gold border-opacity-30"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-buksu-gold text-buksu-navy"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold text-buksu-white">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-buksu-white">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate class="text-buksu-white hover:text-buksu-gold">
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-buksu-white hover:text-buksu-gold">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
