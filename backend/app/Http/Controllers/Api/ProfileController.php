<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // GET /api/profile
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile_image' => $user->profile_image
                ? (str_starts_with($user->profile_image, 'http')
                    ? $user->profile_image
                    : asset('storage/' . $user->profile_image))
                : null,
        ]);
    }

    // PUT /api/profile
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    // POST /api/profile/image
    public function uploadImage(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Upload new image
        $path = $request->file('profile_image')->store('profile_images', 'public');

        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile image updated successfully!',
            'profile_image' => asset('storage/' . $path),
        ]);
    }
}
