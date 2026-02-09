<div style="background-color: #F0F4F8;"
    class="min-h-screen flex flex-col items-center justify-center p-4 pt-12 sm:pt-20">
    <div class="card-rounded w-full max-w-md bg-white shadow-xl overflow-hidden p-8 sm:p-10 border border-blue-100">

        <!-- Header Section: Logo & Titles -->
        <div class="flex flex-col items-center justify-center text-center mb-8">
            <!-- Header Group: Logo & Title Side-by-Side -->
            <!-- Header Group: Logo & Title Side-by-Side (Inline Styles for Reliability) -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                <!-- Logo -->
                <div style="display: inline-flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('images/logo_bps.png') }}" alt="Logo BPS" style="height: 48px; width: auto;"
                        class="object-contain drop-shadow-sm">
                </div>

                <!-- Title -->
                <h2 class="font-bold tracking-tight"
                    style="font-size: 24px; color: #0F4C5C; margin: 0; line-height: 1.2; white-space: nowrap;">
                    Manajemen Surat
                </h2>
            </div>

            <!-- Description -->
            <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">
                Silakan masuk untuk mengakses Sistem Informasi Manajemen Surat
            </p>
        </div>

        <!-- Custom Style for Login Button -->
        <style>
            .btn-teal {
                background-color: #0F4C5C !important;
                color: white !important;
            }

            .btn-teal:hover {
                background-color: #135d70 !important;
            }

            .card-rounded {
                border-radius: 1.5rem !important;
                /* Forces rounded-3xl equivalent */
            }
        </style>

        <!-- Login Form -->
        <div class="w-full">
            <form wire:submit="authenticate" class="space-y-8">
                {{ $this->form }}

                <!-- Manual Button to ensure styles update immediately -->
                <button type="submit"
                    class="btn-teal w-full font-bold py-2.5 rounded-lg transition duration-200 shadow-md flex justify-center items-center gap-2 group relative"
                    wire:loading.attr="disabled">

                    <span wire:loading.remove class="flex items-center gap-2">
                        Login
                    </span>

                    <span wire:loading.flex class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Authenticating...
                    </span>
                </button>
            </form>
        </div>

    </div>

    <!-- Footer/Copyright (Optional but good for single column layouts) -->
    <div class="text-center text-xs text-gray-400" style="margin-top: 2.5rem;">
        &copy; 2026 BPS Kabupaten Demak. All rights reserved.
    </div>
</div>