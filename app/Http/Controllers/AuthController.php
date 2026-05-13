<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    //
    // Step 1: Send user to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Step 2: Google sends user back here
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find existing user or create a new one
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name'   => $googleUser->getName(),
                    'email'  => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            // Create a login token for this user
            $token = $user->createToken('auth_token')->plainTextToken;

            // Send user back to React with the token
            return redirect(env('FRONTEND_URL') . '/auth/callback?token=' . $token);

            } catch (\Exception $e) {
            return redirect(env('FRONTEND_URL') . '/login?error=google_auth_failed');
        }
    }

    // Get currently logged in user
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Log out
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
