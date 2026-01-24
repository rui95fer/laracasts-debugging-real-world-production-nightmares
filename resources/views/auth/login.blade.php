<x-app-layout>
    <x-slot name="title">Login</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <span class="text-5xl">🌙</span>
                <h2 class="mt-4 text-3xl font-bold">Welcome back</h2>
                <p class="mt-2 text-gray-500">Sign in to your NightmareMart account</p>
            </div>

            <div class="bg-dark-800 border border-dark-700 rounded-lg p-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}"
                               required 
                               autofocus
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Password
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="rounded bg-dark-700 border-dark-600 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300">Register</a>
                    </p>
                </div>
            </div>

            <!-- Test Accounts -->
            <div class="mt-6 bg-dark-800 border border-dark-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-400 mb-3">Test Accounts</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Admin:</span>
                        <span class="font-mono text-gray-300">admin@nightmaremart.test / password</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">User:</span>
                        <span class="font-mono text-gray-300">user@nightmaremart.test / password</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
