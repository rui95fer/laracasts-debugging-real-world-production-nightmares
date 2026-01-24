<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'NightmareMart' }} - NightmareMart</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-dark-950 text-gray-100 font-sans antialiased min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-dark-900 border-b border-dark-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Main Nav -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2">
                            <span class="text-2xl">🌙</span>
                            <span class="text-xl font-bold text-purple-400">NightmareMart</span>
                        </a>
                        
                        <div class="hidden md:flex ml-10 space-x-4">
                            <a href="{{ route('products.index') }}" 
                               class="px-3 py-2 text-sm font-medium rounded-md transition
                                      {{ request()->routeIs('products.*') ? 'bg-dark-700 text-white' : 'text-gray-300 hover:bg-dark-800 hover:text-white' }}">
                                Products
                            </a>
                            @auth
                                <a href="{{ route('orders.index') }}" 
                                   class="px-3 py-2 text-sm font-medium rounded-md transition
                                          {{ request()->routeIs('orders.*') ? 'bg-dark-700 text-white' : 'text-gray-300 hover:bg-dark-800 hover:text-white' }}">
                                    My Orders
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="px-3 py-2 text-sm font-medium rounded-md transition bg-purple-600 hover:bg-purple-700 text-white">
                                        Admin
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="flex-1 max-w-lg mx-8 hidden md:flex items-center">
                        <form action="{{ route('search') }}" method="GET" class="w-full">
                            <div class="relative">
                                <input type="text" 
                                       name="q" 
                                       value="{{ request('q') }}"
                                       placeholder="Search products..."
                                       class="w-full bg-dark-800 border border-dark-600 rounded-lg py-2 pl-10 pr-4 text-sm text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-300 hover:text-white transition">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </a>

                        <!-- User Menu -->
                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-300 hover:text-white transition">
                                    <span>{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-dark-800 border border-dark-600 rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-dark-700">My Orders</a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-dark-700">Admin Dashboard</a>
                                    @endif
                                    <hr class="my-1 border-dark-600">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-dark-700">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white transition">Login</a>
                            <a href="{{ route('register') }}" class="text-sm bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 mx-4 mt-4 rounded-lg max-w-7xl lg:mx-auto">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 mx-4 mt-4 rounded-lg max-w-7xl lg:mx-auto">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-900/50 border border-blue-700 text-blue-300 px-4 py-3 mx-4 mt-4 rounded-lg max-w-7xl lg:mx-auto">
                {{ session('info') }}
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-dark-900 border-t border-dark-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <span class="text-2xl">🌙</span>
                        <span class="text-lg font-semibold text-purple-400">NightmareMart</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        A debugging education project. Intentionally buggy! 🐛
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
