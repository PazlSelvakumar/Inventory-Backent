<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     if (!Auth::attempt($request->only('email', 'password'))) {
    //         return response()->json([
    //             'message' => 'Invalid login credentials'
    //         ], 401);
    //     }

    //     $user = Auth::user();

    //     $token = $user->createToken('authToken')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login successful',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'role' => $user->role, 
    //             'type_id' => $user->type_id,
    //         ]
    //     ]);
    // }



    public function store(Request $request)
    {
    $request->validate([
        'login' => 'required',  
        'password' => 'required',
    ]);

    $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

    if (!Auth::attempt([$loginType => $request->login, 'password' => $request->password])) {
        return response()->json([
            'message' => 'Invalid login credentials'
        ], 401);
    }

    $user = Auth::user();

    $token = $user->createToken('default-token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user
    ], 200);
   }



    public function logout(Request $request) 
    {
        $user = Auth::user();

        if ($user) {
            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
