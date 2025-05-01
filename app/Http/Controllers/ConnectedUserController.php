<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;
use Storage;

class ConnectedUserController extends Controller
{
    public function index(User $owner)
    {
        // Log::info('Connected users for owner: ', ['owner_id' => auth()->user()->id ?? null]);
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
    public function show(User $owner, User $connectedUser)
    {
        // ensure the connectedUser is actually related
        if (!$owner->connectedUsers()->where('users.id', $connectedUser->id)->exists()) {
            abort(404, 'Not connected');
        }

        $connectedUser->load('userDetail');

        $profilePic = null;
        if (
            $connectedUser->profile_picture
            && Storage::disk('public')->exists($connectedUser->profile_picture)
        ) {
            // Use Storage::url() so it respects your filesystem config
            $profilePic = Storage::url($connectedUser->profile_picture);
        }

        return response()->json([
            'connected_user' => [
                'id' => $connectedUser->id,
                'name' => $connectedUser->name,
                'email' => $connectedUser->email,
                'profile_picture' => $profilePic,
                'details' => $connectedUser->userDetail,
            ],
        ]);
    }

    /**
     * PUT /owners/{owner}/connected-users/{connectedUser}
     * Update an existing connected user.
     */
    public function update(Request $request, User $owner, User $connectedUser)
    {
        // ensure the connectedUser is actually related
        if (!$owner->connectedUsers()->where('users.id', $connectedUser->id)->exists()) {
            abort(404, 'Not connected');
        }
        Log::info('Connected user update request', [
            'user_id' => auth()->user()->id ?? null,
            'connected_user_id' => $request->all(),
        ]);
        // same validation as in register, except unique email ignores this user
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($connectedUser->id)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'insurance_type' => ['nullable', 'string', 'max:100'],
            'insurance_number' => ['nullable', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:20'],
            'birthDate' => ['nullable', 'string', 'max:20'],
            'plz' => ['nullable', 'string', 'max:20'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $validated['profile_picture'] = $path;
        }

        // update main user record
        $connectedUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_picture' => $validated['profile_picture'] ?? $connectedUser->profile_picture,
        ]);

        // update details
        $connectedUser->userDetail()->updateOrCreate(
            ['user_id' => $connectedUser->id],
            [
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'insurance_type' => $validated['insurance_type'] ?? null,
                'insurance_number' => $validated['insurance_number'] ?? null,
                'lastName' => $validated['lastName'] ?? null,
                'birthDate' => $validated['birthDate'] ?? null,
                'plz' => $validated['plz'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Connected user updated successfully.',
            'connected_user' => $connectedUser->fresh()->load('userDetail'),
        ]);
    }
    public function destroy(User $owner, User $connectedUser)
    {
        // 1. Authorize: only the owner may remove their connected users
        if (Auth::id() !== $owner->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        Log::info('Connected user delete request', [
            'user_id' => $owner->id ?? null,
            'connected_user_id' => $connectedUser->id,
        ]);
        // 2. Ensure this user is actually connected
        $relation = $owner->connectedUsers()->where('users.id', $connectedUser->id);
        if (!$relation->exists()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // 3. Detach the pivot
        $owner->connectedUsers()->detach($connectedUser->id);

        // 4. (Optional) delete the user record & details if you don't want to keep it
        $connectedUser->userDetail()->delete();
        $connectedUser->delete();

        $max = config('connected.max_connected_users');

        return response()->json([
            'message' => 'Connected user removed.',
            'max_connected_users' => $max,
            'connected_users' => $owner->connectedUsers()
                ->select('users.id', 'users.name')
                ->get(),
        ], 200);
    }
}
