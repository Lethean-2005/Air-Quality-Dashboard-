<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return Redirect::away($frontendUrl . '/auth/callback?error=' . urlencode('Google authentication failed. Please try again.'));
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name'             => $googleUser->getName() ?? 'Google User',
                'email'            => $googleUser->getEmail(),
                'password'         => Str::random(32),
                'role'             => 'user',
                'google_id'        => $googleUser->getId(),
                'profile_image'    => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        } else {
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
            }
            if (!$user->profile_image && $googleUser->getAvatar()) {
                $user->profile_image = $googleUser->getAvatar();
            }
            $user->save();
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('api_token')->plainTextToken;

        $params = http_build_query([
            'token' => $token,
            'role'  => $user->role,
            'name'  => $user->name,
            'image' => $user->profile_image ?? '',
        ]);

        return Redirect::away($frontendUrl . '/auth/callback?' . $params);
    }

    public function register(Request $request)
    {

        // dd(request()->all());
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user', 
        ]);

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('api_token')->plainTextToken,
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'User registered successfully']);

    }

    public function login(Request $request)
    {
        $request->validate([

            'email'    => 'required|email',
            'password' => 'required|string',

            'email' => 'required|email',
            'password' => 'required'

        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'This account has been deactivated'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'user'  => $user,
            'role'    => $user->role,
            'token' => $user->createToken('api_token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); // Revoke only the current token

        return response()->json([
            'message' => 'Logged out successfully'
        ]);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }


}
