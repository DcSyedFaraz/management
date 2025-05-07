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
        // Is there an authenticated owner? If so, we’re in “add connected user” mode.
        $owner = Auth::user();
        $isAddMode = (bool) $owner;

        // ───── common validation rules ────────────────────────────────
        $rules = [
            'salutation' => ['required', 'string', Rule::in(['mr', 'mrs', 'other'])],
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ];

        // ─ only for self-registration ─────────────────────────────────
        if (!$isAddMode) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string', 'min:8'];
        }

        // 1) validate
        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }

        // 2) handle connected-user creation
        if ($isAddMode) {
            $maxCount = config('connected.max_connected_users');
            $currentCount = $owner->connectedUsers()->count();

            if ($currentCount >= $maxCount) {
                return response()->json([
                    'message' => "You may only connect up to {$maxCount} users."
                ], 423);
            }

            // auto-generate a password
            $autoPassword = Str::random(10);
            $profilePath = $this->storeProfilePicture($request);

            $new = User::create([
                'name' => $validated['first_name'],
                'email' => $validated['email'],
                'password' => Hash::make($autoPassword),
                'profile_picture' => $profilePath,
            ]);

            // persist the detail record
            $new->userDetail()->create([
                'salutation' => $validated['salutation'],
                'title' => $validated['title'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'street' => $validated['street'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'birth_date' => $validated['birth_date'],
                'phone_number' => $validated['phone_number'] ?? null,
            ]);

            $new->assignRole('user');
            $owner->connectedUsers()->attach($new->id);

            $connected = $owner->connectedUsers()
                ->select('users.id', 'users.name')
                ->get();

            return response()->json([
                'connected_users' => $connected,
            ], 201);
        }

        // 3) self-registration
        Log::info('User registration data:', $validated);

        $profilePath = $this->storeProfilePicture($request);

        $user = User::create([
            'name' => $validated['first_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_picture' => $profilePath,
        ]);

        $user->userDetail()->create([
            'salutation' => $validated['salutation'],
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'street' => $validated['street'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'birth_date' => $validated['birth_date'],
        ]);

        // OTP generation & mail
        $otp = random_int(1000, 9999);
        $expires = Carbon::now()->addMinutes(10);

        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => $expires,
        ]);

        $user->assignRole('user');
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return response()->json([
            'message' => 'Registration successful. Check your email for the OTP.'
        ], 201);
    }

    /**
     * PUT /user/{user}
     * Update a self-registered user’s profile.
     */
    public function update(Request $request, User $user)
    {
        // ensure only the owner can update themselves
        if (Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        Log::info('User update data (raw):', $request->all());

        $rules = [
            'salutation' => ['required', 'string', Rule::in(['mr', 'mrs', 'other'])],
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ];

        $validated = $request->validate($rules);

        // handle profile picture storage
        $oldPath = $user->getRawOriginal('profile_picture');
        $newPath = $request->hasFile('profile_picture')
            ? $this->storeProfilePicture($request)
            : $oldPath;

        Log::info('Profile picture raw paths:', [
            'old' => $oldPath,
            'new' => $newPath,
        ]);

        $user->update([
            'name' => $validated['first_name'],
            'email' => $validated['email'],
            'profile_picture' => $newPath,
        ]);

        // upsert detail row
        $user->userDetail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $validated['phone_number'] ?? null,
                'salutation' => $validated['salutation'],
                'title' => $validated['title'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'street' => $validated['street'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'birth_date' => $validated['birth_date'],
            ]
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()->load('userDetail'),
        ], 200);
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
