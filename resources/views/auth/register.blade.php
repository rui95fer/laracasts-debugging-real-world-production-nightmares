<x-app-layout>
    <x-slot name="title">Register</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <span class="text-5xl">🌙</span>
                <h2 class="mt-4 text-3xl font-bold">Create an account</h2>
                <p class="mt-2 text-gray-500">Join NightmareMart today</p>
            </div>

            <div class="bg-dark-800 border border-dark-700 rounded-lg p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Name
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}"
                               required 
                               autofocus
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}"
                               required
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-300 mb-2">
                            Timezone
                        </label>
                        <select name="timezone" 
                                id="timezone"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @foreach(getTimezoneList() as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Used for displaying dates correctly (Episode 7)</p>
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

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                            Confirm Password
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               required
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition">
                        Create Account
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
