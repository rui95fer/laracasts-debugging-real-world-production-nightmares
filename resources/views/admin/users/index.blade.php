<x-admin-layout>
    <x-slot name="title">Users</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">All Users</h2>
            <p class="text-sm text-gray-500">{{ $users->total() }} users</p>
        </div>
    </div>

    <div class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-dark-900">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">User</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Timezone</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Orders</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Role</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Joined</th>
                    <th class="text-right px-4 py-3 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @foreach($users as $user)
                    <tr class="hover:bg-dark-700/50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-sm">
                            {{ $user->timezone }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-300">{{ $user->orders_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->is_admin)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-900/50 text-purple-300">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300">
                                    User
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" 
                               class="text-purple-400 hover:text-purple-300 text-sm">
                                View
                            </a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-400 hover:text-yellow-300 text-sm">
                                        {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

    <!-- Episode 7 Notice -->
    <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mt-6 text-sm">
        <p class="text-yellow-300">
            <strong>🐛 Episode 7:</strong> Users have different timezones set - useful for testing timezone bugs.
        </p>
    </div>
</x-admin-layout>
