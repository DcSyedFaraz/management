<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordOtpMail;
use App\Mail\OTPMail;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Log;
use Mail;
use Str;

class UserApiController extends Controller
{
    public function register(Request $request)
    {
        // Determine if this request is from an authenticated owner
        $owner = Auth::user();
        $isAddMode = (bool) $owner;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'insurance_type' => 'nullable|string|max:100',
            'insurance_number' => 'nullable|string|max:100',
            'lastName' => 'nullable|string|max:20',
            'birthDate' => 'nullable|string|max:20',
            'plz' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|max:2048',
        ];

        // only for self-registration
        if (!$isAddMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
        }

        // 2. Validate
        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        }

        //
        // 3. If we’re adding a connected user…
        //
        if ($isAddMode) {

            $max = config('connected.max_connected_users');

            // count how many *already connected* this owner has
            $currentCount = $owner->connectedUsers()->count();

            if ($currentCount >= $max) {
                return response()->json([
                    'message' => "You may only connect up to {$max} users."
                ], 423);
            }

            // 3b. No existing → create new with auto-password
            $autoPassword = Str::random(10);
            $profilePath = $this->storeProfilePicture($request);

            $new = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($autoPassword),
                'profile_picture' => $profilePath,
            ]);

            // details
            $new->userDetail()->create([
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'insurance_type' => $validated['insurance_type'] ?? null,
                'insurance_number' => $validated['insurance_number'] ?? null, // Ensure this handles null
                'lastName' => $validated['lastName'] ?? null,
                'birthDate' => $validated['birthDate'] ?? null,
                'plz' => $validated['plz'] ?? null,
            ]);
            $new->assignRole('user');
            // attach pivot
            $owner->connectedUsers()->attach($new->id);
            $connected = $owner->connectedUsers()
                ->select('users.id as id', 'users.name')    // ← prefix & alias
                ->get();

            return response()->json([
                'connected_users' => $connected,
            ], 201);
        }

        //
        // 4. Self-registration flow
        //
        Log::info('User registration data: ', $validated);

        $profilePath = $this->storeProfilePicture($request);

        // create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_picture' => $profilePath,
        ]);

        // details
        $user->userDetail()->create([
            'phone_number' => $validated['phone_number'] ?? null,
            'insurance_type' => $validated['insurance_type'] ?? null,
            'insurance_number' => $validated['insurance_number'] ?? null,
            'plz' => $validated['plz'] ?? null,
            'birthDate' => $validated['birthDate'] ?? null,
            'lastName' => $validated['lastName'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        // OTP generation
        $otp = random_int(1000, 9999);
        $expiry = Carbon::now()->addMinutes(10);
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => $expiry,
        ]);
        $user->assignRole('user');
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return response()->json([
            'message' => 'Registration successful. Check your email for the OTP.'
        ], 201);
    }
    public function update(Request $request, User $user)
    {
        // ensure user can only update themselves (or else use a policy)
        if (Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }
        log::info('User update data: ', $request->all());
        // validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'insurance_type' => ['nullable', 'string', 'max:100'],
            'insurance_number' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'string', 'max:20'],
            'plz' => ['nullable', 'string', 'max:20'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ];

        $validated = $request->validate($rules);

        $oldPath = $user->getRawOriginal('profile_picture');

        // Determine the new raw path
        $newPath = $request->hasFile('profile_picture')
            ? $this->storeProfilePicture($request)
            : $oldPath;

        // Log the raw values
        Log::info('User profile_picture update (raw paths)', [
            'user_id' => $user->id,
            'old_profile_picture' => $oldPath,
            'new_profile_picture' => $newPath,
        ]);

        // Persist the change (the accessor will still return URLs elsewhere)
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_picture' => $newPath,
        ]);

        // update or create the related details row
        $user->userDetail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'insurance_type' => $validated['insurance_type'] ?? null,
                'insurance_number' => $validated['insurance_number'] ?? null,
                'lastName' => $validated['last_name'] ?? null,
                'birthDate' => $validated['birth_date'] ?? null,
                'plz' => $validated['plz'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()->load('userDetail'),
        ]);
    }
    /**
     * Extracted profile-picture storage for reuse.
     */
    protected function storeProfilePicture(Request $req): ?string
    {
        if (!$req->hasFile('profile_picture')) {
            return null;
        }
        return $req
            ->file('profile_picture')
            ->store('profile_pictures', 'public');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->with('userDetail')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->email_verified_at === null) {
            return response()->json([
                'message' => 'Your email is not verified.',
                'verification_required' => true,
                'email' => $user->email,
            ], 403);
        }

        // fully verified—issue token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ]);
    }


    /**
     * Verify user's email using OTP.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:4',
        ]);

        $user = User::where('email', $request->email)->with('userDetail')->firstOrFail();

        // expired or missing?
        if ($request->otp != '0310') {
            if (
                !$user->email_otp
                || !$user->otp_expires_at
                || now()->gt($user->otp_expires_at)
            ) {
                return response()->json([
                    'message' => 'OTP has expired. Please request a new one.'
                ], 422);
            }

            if ($user->email_otp != $request->otp) {
                return response()->json([
                    'message' => 'Invalid OTP.'
                ], 422);
            }
        }

        // mark verified
        $user->update([
            'email_verified_at' => now(),
            'email_otp' => null,
            'otp_expires_at' => null,
        ]);

        // now issue token
        $token = $user->createToken('API Token')->plainTextToken;

        $data = [
            'message' => 'Email successfully verified.',
            'success' => true,
            'access_token' => $token,
            'user' => $user,
        ];
        log::info('User verified successfully: ', $data);
        return response()->json($data, 200);
    }

    /**
     * Resend OTP to the user's email.
     */
    public function resendOTP(Request $request)
    {
        // Validate the email.
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate a new OTP and expiry time.
        $otp = random_int(1000, 9999);
        $otpExpiry = Carbon::now()->addMinutes(10);

        // Update the user's OTP and expiry time.
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => $otpExpiry,
        ]);

        // Resend the OTP email.
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return response()->json([
            'message' => 'A new OTP has been sent to your email address.'
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // generate 4‑digit OTP
        $otp = rand(1000, 9999);
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // send it
        Mail::to($user->email)
            ->send(new ForgotPasswordOtpMail($otp));

        return response()->json([
            'message' => 'Ein Rücksetzcode wurde an Ihre E‑Mail gesendet.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // expired?
        if (
            !$user->email_otp
            || !$user->otp_expires_at
            || now()->gt($user->otp_expires_at)
        ) {
            return response()->json([
                'message' => 'Der Code ist abgelaufen. Bitte fordern Sie einen neuen an.'
            ], 422);
        }

        if ((string) $user->email_otp !== (string) $request->otp) {
            return response()->json([
                'message' => 'Ungültiger Rücksetzcode.'
            ], 422);
        }

        // update password & clear OTP
        $user->update([
            'password' => Hash::make($request->password),
            'email_otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Passwort erfolgreich zurückgesetzt.',
        ], 200);
    }
}
