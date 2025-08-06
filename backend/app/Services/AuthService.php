<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Auth\SendOtpNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthService
{
    /**
     * Registers a new user and sends OTP/verification link.
     */
    public function register(array $data): User
    {
        $otp = random_int(100000, 999999);
        $token = Str::random(64);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'otp' => $otp,
            'verification_token' => $token,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $user->assignRole('user');
        $user->notify(new SendOtpNotification($otp, $token, 'verify your account', '/verify-email'));

        return $user;
    }

    /**
     * Logs in the user and returns token.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => 'Please verify your email first.']);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('roles', 'permissions'),
        ];
    }

    /**
     * Verifies the user's email and returns login token.
     */
    public function verify(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if ($user->hasVerifiedEmail()) {
            throw new \Exception('Email already verified.');
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            throw new \Exception('Verification code/link has expired.');
        }

        if (($data['otp'] && $user->otp != $data['otp']) || ($data['token'] && $user->verification_token != $data['token'])) {
            throw new \Exception('Invalid verification code or token.');
        }

        $user->markEmailAsVerified();
        $user->forceFill(['otp' => null, 'verification_token' => null, 'otp_expires_at' => null])->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function verifyPasswordResetOtp(array $data): string
    {
        $passwordReset = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$passwordReset || $passwordReset->otp != $data['otp']) {
            throw new \Exception('Invalid OTP.');
        }
        if (Carbon::parse($passwordReset->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw new \Exception('OTP has expired.');
        }

        $resetSessionToken = Str::random(64);

        DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->update(['token' => Hash::make($resetSessionToken)]); 

        return $resetSessionToken;
    }

    public function resetPasswordWithToken(array $data): void
    {
        $passwordReset = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$passwordReset || !Hash::check($data['reset_token'], $passwordReset->token)) {
            throw new \Exception('Invalid or expired reset token.');
        }

        User::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
    }

    /**
     * Sends a new verification OTP/link.
     */
    public function resendVerification(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user->hasVerifiedEmail()) {
            throw new \Exception('Email already verified.');
        }

        $otp = random_int(100000, 999999);
        $token = Str::random(64);
        $user->update(['otp' => $otp, 'verification_token' => $token, 'otp_expires_at' => Carbon::now()->addMinutes(10)]);
        $user->notify(new SendOtpNotification($otp, $token, 'verify your account', '/verify-email'));
    }

    /**
     * Sends OTP/link for password reset.
     */
    public function forgotPassword(string $email): string
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception('User not found.');
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $otp = random_int(100000, 999999);
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'otp' => $otp,
            'created_at' => Carbon::now()
        ]);

        $user->notify(new SendOtpNotification($otp, $token, 'reset your password', '/reset-password'));

        return $token;
    }

    /**
     * Verifies OTP/link and resets the password.
     */
    public function resetPassword(array $data): void
    {
        $passwordReset = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$passwordReset || !Hash::check($data['token'], $passwordReset->token) || $passwordReset->otp != $data['otp']) {
            throw new \Exception('Invalid OTP or token.');
        }
        if (Carbon::parse($passwordReset->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw new \Exception('OTP/link has expired.');
        }

        User::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
    }

    /**
     * Updates the password for the logged-in user.
     */
    public function updatePassword(User $user, array $data): void
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password does not match.']);
        }

        $user->forceFill(['password' => Hash::make($data['new_password'])])->save();
    }

    /**
     * Updates the user's profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        // Validation rule to check for unique email
        validator($data, [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validate();

        $user->update($data);

        return $user;
    }
}
