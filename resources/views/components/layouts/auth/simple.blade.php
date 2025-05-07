<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            :root {
                --primary: #000435;
                --highlight: #FFC100;
                --text: #000435;
            }
            body {
                background: linear-gradient(135deg, var(--primary) 0%, #0a1045 100%) !important;
            }
            flux-button[variant="primary"] button {
                background-color: var(--highlight) !important;
                color: var(--primary) !important;
                font-weight: bold !important;
                transition: all 0.3s ease !important;
            }
            flux-button[variant="primary"] button:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            }
            flux-input label {
                color: var(--text) !important;
            }
            flux-input input {
                color: var(--text) !important;
            }
            flux-input input:focus, 
            flux-checkbox div:focus-within {
                border-color: var(--highlight) !important;
                box-shadow: 0 0 0 3px rgba(255, 193, 0, 0.2) !important;
            }
            flux-checkbox label {
                color: var(--text) !important;
            }
            flux-link a {
                color: var(--highlight) !important;
            }
            flux-link a:hover {
                color: var(--primary) !important;
            }
            flux-heading, flux-subheading {
                color: var(--text) !important;
            }
            .auth-card {
                border-top: 4px solid var(--highlight);
                background-color: white;
                border-radius: 0.75rem;
                padding: 2rem;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            }
            
            /* Dark mode styles */
            .dark flux-input label,
            .dark flux-input input,
            .dark flux-checkbox label,
            .dark flux-heading,
            .dark flux-subheading {
                color: white !important;
            }
            
            .dark .auth-card {
                background-color: #000435 !important;
                border: 1px solid var(--highlight);
            }
            
            .dark flux-input input,
            .dark flux-input div,
            .dark [data-flux-control] {
                background-color: rgba(255, 255, 255, 0.1) !important;
                border-color: rgba(255, 255, 255, 0.2) !important;
            }
            
            .dark flux-input input::placeholder {
                color: rgba(255, 255, 255, 0.6) !important;
            }
        </style>
    </head>
    <body class="min-h-screen antialiased">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('landing.index') }}" class="flex flex-col items-center gap-2 font-medium mb-4" wire:navigate>
                    <span class="flex h-16 w-16 items-center justify-center">
                        <x-app-logo-icon class="h-16 w-auto" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6 auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
