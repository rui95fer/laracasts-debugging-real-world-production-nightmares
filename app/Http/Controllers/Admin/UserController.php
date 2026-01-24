<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display all users
     */
    public function index()
    {
        $users = User::withCount('orders')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load('orders.items.product');

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Toggle admin status
     */
    public function toggleAdmin(User $user)
    {
        // Prevent self-demotion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $status = $user->is_admin ? 'promoted to admin' : 'demoted from admin';

        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
