<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     * path="/api/auth/login",
     * summary="Login a user as HR / Director",
     * description="Login by email, password",
     * operationId="login",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="token")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized")
     *        )
     *     )
     * )
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $token_validity = 24 * 60;

        $this->guard()->factory()->setTTL($token_validity);

        if(!$token = $this->guard()->attempt($validator->validated())){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

     /**
     * @OA\Post(
     * path="/api/auth/register",
     * summary="Register a user as HR / Director",
     * description="Register with name, email, password",
     * operationId="register",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "email","password"},
     *       @OA\Property(property="name", type="string", format="text", example="ajiferuke tomiwa"),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="User{}")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The email has already been taken.")
     *        )
     *     )
     * )
     */
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                $validator->errors()
            ], 422);
        }

        $user = User::create(
            array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
            )
        );

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
            ]);
    }

    /**
     * @OA\Post(
     * path="/api/auth/logout",
     * summary="Logs out current logged in user session.",
     * description="Logs out user with the logged in token either through the body, params, or Auth_Bearer",
     * operationId="logout",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string", format="text", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzMzgyNTY4MSwiZXhwIjoxNjMzOTEyMDgxLCJuYmYiOjE2MzM4MjU2ODEsImp0aSI6IlpoZGpHcjJMaDR3NzAwWVYiLCJzdWIiOjEyLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.291_2F7vTE-eIwSiS1DLA692BMLQuFX27VFt84ahons"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="")
     *        )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *     )
     * )
     */
    public function logout(){
        $this->guard()->logout();

        return response()->json(['message' => 'User logged out successfully']);
    }

    public function profile(){
        return response()->json($this->guard()->user());
    }

    /**
     * @OA\Post(
     * path="/api/auth/refresh?token={token}",
     * summary="Changes current logged in user session token.",
     * description="Logs out and changes the token of the current user.",
     * operationId="refresh",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string", format="text", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzMzgyNTY4MSwiZXhwIjoxNjMzOTEyMDgxLCJuYmYiOjE2MzM4MjU2ODEsImp0aSI6IlpoZGpHcjJMaDR3NzAwWVYiLCJzdWIiOjEyLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.291_2F7vTE-eIwSiS1DLA692BMLQuFX27VFt84ahons"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="token.")
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *     )
     * )
     */
    public function refresh(){
        return $this->respondWithToken($this->guard()->refresh());
    }

    protected function respondWithToken($token){
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validity' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    protected function guard(){
        return Auth::guard();
    }

}
