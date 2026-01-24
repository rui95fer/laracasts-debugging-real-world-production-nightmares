<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - NightmareMart Admin</title>

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
<body class="bg-dark-950 text-gray-100 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-dark-900 border-r border-dark-700 fixed h-full">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-4 border-b border-dark-700">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                        <span class="text-2xl">🌙</span>
                        <span class="text-lg font-bold text-purple-400">NightmareMart</span>
                    </a>
                    <p class="text-xs text-gray-500 mt-1">Admin Panel</p>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-md transition
                              {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-800' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-md transition
                              {{ request()->routeIs('admin.orders.*') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-800' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Orders</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-md transition
                              {{ request()->routeIs('admin.products.*') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-800' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>Products</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center space-x-3 px-3 py-2 rounded-md transition
                              {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-800' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Users</span>
                    </a>
                </nav>

                <!-- User Info -->
                <div class="p-4 border-t border-dark-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex space-x-2">
                        <a href="{{ route('home') }}" class="flex-1 text-center text-xs text-gray-400 hover:text-white py-1 px-2 bg-dark-800 rounded">
                            View Site
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-xs text-gray-400 hover:text-white py-1 px-2 bg-dark-800 rounded">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-dark-900 border-b border-dark-700 sticky top-0 z-40">
                <div class="px-6 py-4">
                    <h1 class="text-xl font-semibold">{{ $title ?? 'Admin' }}</h1>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded-lg mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-lg mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-900/50 border border-blue-700 text-blue-300 px-4 py-3 rounded-lg mb-4">
                        {{ session('info') }}
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
