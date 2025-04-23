<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class ConnectedUserController extends Controller
{
    public function index(User $owner)
    {
        Log::info('Connected users for owner: ', ['owner_id' => auth()->user()->id ?? null]);
        $max = config('connected.max_connected_users');

        // fetch only the related users
        $connected = $owner->connectedUsers()
            ->select('users.id as id', 'users.name')
            ->get();

        return response()->json([
            'max_connected_users' => $max,
            'connected_users' => $connected,
        ]);
    }

    // Create a new connected user under this owner
    public function store(Request $request, User $owner)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create the new user
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Attach pivot
        $owner->connectedUsers()->attach($newUser->id);

        return response()->json([
            'id' => $newUser->id,
            'name' => $newUser->name,
        ], 201);
    }
}
