<header class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                @if($company->logo)
                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                         class="h-20 w-20 sm:h-24 sm:w-24 object-contain rounded-lg bg-white dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-600">
                @else
                    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-store text-3xl text-white"></i>
                    </div>
                @endif
            </div>

            {{-- Company Info --}}
            <div class="text-center sm:text-left flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $company->name }}
                </h1>

                <div class="mt-2 flex flex-wrap justify-center sm:justify-start gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                    @if($company->phone)
                        <span class="inline-flex items-center gap-1">
                            <i class="fas fa-phone text-gray-400 dark:text-gray-500"></i>
                            {{ $company->phone }}
                        </span>
                    @endif

                    @if($company->email)
                        <span class="inline-flex items-center gap-1">
                            <i class="fas fa-envelope text-gray-400 dark:text-gray-500"></i>
                            {{ $company->email }}
                        </span>
                    @endif

                    @if($company->address)
                        <span class="inline-flex items-center gap-1">
                            <i class="fas fa-map-marker-alt text-gray-400 dark:text-gray-500"></i>
                            {{ $company->address }}
                        </span>
                    @endif

                    @if($company->ig)
                        <a href="https://instagram.com/{{ $company->ig }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 text-pink-600 dark:text-pink-400 hover:underline">
                            <i class="fab fa-instagram"></i>
                            @ {{ $company->ig }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
