<div class="mt-6">
    {{-- Divider --}}
    <div class="relative flex items-center justify-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
        </div>
        <div class="relative px-4 bg-white dark:bg-gray-900">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">atau</span>
        </div>
    </div>

    {{-- SSO Button --}}
    <div class="mt-5">
        <a href="{{ route('sipetra.login') }}"
           class="group relative flex items-center justify-center w-full px-5 py-2.5 
                  bg-white dark:bg-gray-800 
                  border border-gray-300 dark:border-gray-600 
                  rounded-lg shadow-sm 
                  hover:bg-gray-50 dark:hover:bg-gray-700 
                  hover:border-blue-400 dark:hover:border-blue-500
                  hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                  transition-all duration-200 ease-in-out
                  no-underline"
           style="text-decoration: none;">

            {{-- Logo BPS --}}
            <div class="flex items-center justify-center w-6 h-6 mr-3 flex-shrink-0">
                <img src="{{ asset('images/logo_bps.png') }}" 
                     alt="Logo BPS" 
                     class="w-5 h-5 object-contain"
                     loading="lazy">
            </div>

            {{-- Label --}}
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">
                Masuk dengan SIPETRA SSO
            </span>

            {{-- Arrow icon --}}
            <svg class="w-4 h-4 ml-2 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all duration-200 flex-shrink-0" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Info text --}}
    <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
        Login terpusat menggunakan akun BPS Kabupaten Demak
    </p>
</div>
