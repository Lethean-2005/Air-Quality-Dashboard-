<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Resolve a stored profile_image value into a usable URL. Google-auth users
     * store an already-absolute avatar URL there instead of a local storage path,
     * so wrapping it in Storage::url() again would mangle it into a broken
     * "/storage/https://..." path.
     */
    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'email_verified_at', 'last_login_at', 'is_active', 'created_at', 'updated_at', 'role', 'profile_image', 'phone', 'bio')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($user) {
                        $user->profile_image = $this->resolveImageUrl($user->profile_image);
                        return $user;
                    });

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'string|nullable|in:admin,user',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);
            $data['role'] = $data['role'] ?? 'user';

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
                $data['profile_image'] = $imagePath;
            }

            $user = User::create($data);

            // Return user with image URL
            $user->profile_image = $this->resolveImageUrl($user->profile_image);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific user by ID.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Add full image URL
        $user->profile_image = $this->resolveImageUrl($user->profile_image);

        return response()->json($user);
    }

    /**
     * Update user info by ID.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'role' => 'sometimes|string|nullable|in:admin,user',
            'password' => 'sometimes|nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                
                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
                $data['profile_image'] = $imagePath;
            }

            // Handle password
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Return user with image URL
            $user->profile_image = $this->resolveImageUrl($user->profile_image);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user by ID.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        try {
            // Delete profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return authenticated user's info.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'role' => $user->role ?? 'user',
            'profile_image' => $this->resolveImageUrl($user->profile_image),
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    /**
     * Get current user's role only
     */
    public function role(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'role' => $user->role ?? 'user'
        ]);
    }

    /**
     * Update current user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'current_password' => 'nullable|string|min:6',
            'new_password' => 'nullable|string|min:6|confirmed',
            'new_password_confirmation' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                
                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
                $user->profile_image = $imagePath;
            }

            // Update user data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->bio = $request->bio;

            // Update password if provided
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'role' => $user->role,
                    'profile_image' => $this->resolveImageUrl($user->profile_image),
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove profile image
     */
    public function removeProfileImage(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            // Delete image file if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
                $user->profile_image = null;
                $user->save();
            }

            return response()->json([
                'message' => 'Profile image removed successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'role' => $user->role,
                    'profile_image' => null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
