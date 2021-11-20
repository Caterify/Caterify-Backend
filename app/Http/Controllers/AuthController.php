<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:\App\Models\User,email',
            'phone' => 'required|numeric|unique:\App\Models\User,phone',
            'role' => 'required|numeric',
            'password' => 'required|string|confirmed',
            'address' => 'required_if:role,1',
            'radius' => 'numeric|required_if:role,1',
            'latitude' => 'numeric|required_if:role,1',
            'longitude' => 'numeric|required_if:role,1'
        ]);

        // return $request;
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'radius' => $request->radius,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $user->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return ResponseHelper::response(
            "Successfully created new user",
            201,
            [
                'token' => $token,
                'user' => $user
            ]
        );
    }

    /**
     * Yes, this is a login function
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::response("Invalid email or password", 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ResponseHelper::response(
            "Successfully logged in",
            200,
            ['token' => $token]
        );
    }

    /**
     * Yes, this is a logout function
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return ResponseHelper::response("Successfully logged out", 200);
    }
}
