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
    public function details($id)
    {
        $user = User::findOrFail($id);
        // send with userdetails
        $user->load('userDetail');
        Log::info('Connected user details:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
        return response()->json($user->only(['id', 'name', 'email']) + [
            'details' => $user->userDetail,
        ]);

    }
    // Create a new connected user under this owner
    public function store(Request $request, User $owner)
    {
        $request->validate([
            'salutation' => ['required', 'string', Rule::in(['mr', 'mrs', 'other'])],
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated = $request->all();

        // store picture if provided
        $path = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('profiles', 'public')
            : null;

        $new = User::create([
            'name' => $validated['first_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'profile_picture' => $path,
        ]);

        $new->assignRole('user');

        // build details
        $new->userDetail()->create([
            'salutation' => $validated['salutation'],
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'street' => $validated['street'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'birth_date' => $validated['birth_date'],
        ]);

        $owner->connectedUsers()->attach($new->id);

        return response()->json([
            'id' => $new->id,
            'name' => $new->name,
        ], 201);
    }

    /**
     * PUT /owners/{owner}/connected-users/{connectedUser}
     * Update an existing connected user.
     */
    public function update(Request $request, User $owner, User $connectedUser)
    {
        if (!$owner->connectedUsers()->where('users.id', $connectedUser->id)->exists()) {
            abort(404, 'Not connected');
        }

        Log::info('Connected user update data:', $request->all());

        $rules = [
            'salutation' => ['required', 'string', Rule::in(['mr', 'mrs', 'other'])],
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($connectedUser->id)],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ];

        $validated = $request->validate($rules);

        // handle picture
        $path = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('profiles', 'public')
            : $connectedUser->profile_picture;

        $connectedUser->update([
            'name' => $validated['first_name'],
            'email' => $validated['email'],
            'profile_picture' => $path,
        ]);

        $connectedUser->userDetail()->updateOrCreate(
            ['user_id' => $connectedUser->id],
            [
                'salutation' => $validated['salutation'],
                'title' => $validated['title'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'street' => $validated['street'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'birth_date' => $validated['birth_date'],
                'phone_number' => $validated['phone_number'],

            ]
        );

        return response()->json([
            'message' => 'Connected user updated successfully.',
            'connected_user' => $connectedUser->fresh()->load('userDetail'),
        ], 200);
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
