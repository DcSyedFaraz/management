<?php

namespace App\Http\Controllers;

use App\Mail\OTPMail;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mail;

class UserApiController extends Controller
{
    public function register(Request $request)
    {
        // Validate incoming data.
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:50',
                'insurance_type' => 'nullable|string|max:100',
                'insurance_number' => 'nullable|string|max:100',
                'plz' => 'nullable|string|max:20',
                'profile_picture' => 'nullable|image|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Handle profile picture upload if provided.
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            // Stores the file in the "profile_pictures" directory on the "public" disk.
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Prepare user data.
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // Include profile picture path if available.
        if ($profilePicturePath) {
            $userData['profile_picture'] = $profilePicturePath;
        }

        // Create the user.
        $user = User::create($userData);

        // Create associated user details record.
        $user->userDetail()->create([
            'phone_number' => $request->phone_number,
            'insurance_type' => $request->insurance_type,
            'insurance_number' => $request->insurance_number,
            'plz' => $request->plz,
        ]);

        // Generate a random 6-digit OTP code.
        $otp = random_int(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(10);

        // Save OTP and its expiry in the user record.
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => $otpExpiry,
        ]);
        $user->assignRole('user');
        // Send OTP email using a custom mailable.
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return response()->json([
            'message' => 'Registration successful. An OTP has been sent to your email address for verification.'
        ], 201);
    }

    /**
     * Login an existing user and create a Sanctum token.
     */
    public function login(Request $request)
    {
        // Validate credentials.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user's email is verified.
        // In this example, we assume that a non-null email verification timestamp indicates a verified email.
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Your email is not verified. Please verify your email using the OTP sent to you.'
            ], 403);
        }

        // Create Sanctum token.
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Verify user's email using OTP.
     */
    public function verifyOTP(Request $request)
    {
        // Validate OTP and email.
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if OTP is set and not expired.
        if (!$user->email_otp || !$user->otp_expires_at || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 422);
        }

        // Verify OTP.
        if ($user->email_otp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 422);
        }

        // Mark email as verified.
        $user->update([
            'email_verified_at' => Carbon::now(),
            'email_otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Email successfully verified.']);
    }
}
