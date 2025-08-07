<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-07T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-07T12:34:56Z")
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/login",
     * tags={"Auth"},
     * summary="User Login",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful",
     * @OA\JsonContent(
     * @OA\Property(property="access_token", type="string", example="1|e2B1r8iK5j4h3g2f1d0c9b8a7s6d5f4g3h2j1k0l"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Login information invalid",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Login information invalid"),
     * )
     * )
     * )
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Login information invalid',
            ], 401);
        }

        $user = User::where('email', $validated['email'])->first();

        return response()->json([
            'access_token' => $user->createToken('api_token')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
    /**
     * @OA\Post(
     * path="/api/register",
     * tags={"Auth"},
     * summary="User Registration",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="secret"),
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Registration successful",
     * @OA\JsonContent(
     * @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
     * @OA\Property(property="access_token", type="string", example="1|e2B1r8iK5j4h3g2f1d0c9b8a7s6d5f4g3h2j1k0l"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * )
     * )
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'data' => $user,
            'access_token' => $user->createToken('api_token')->plainTextToken,
            'token_type' => 'Bearer',
        ], 201);
    }
    // In AuthController.php

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user info",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user data",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}
