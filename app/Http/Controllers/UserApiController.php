<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordOtpMail;
use App\Mail\OTPMail;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;
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
                'lastName' => 'nullable|string|max:20',
                'birthDate' => 'nullable|string|max:20',
                'profile_picture' => 'nullable|image|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        log::info('User registration data: ', $validated);
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
            'birthDate' => $request->birthDate,
            'lastName' => $request->lastName,
        ]);

        // Generate a random 6-digit OTP code.
        $otp = random_int(1000, 9999);
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
