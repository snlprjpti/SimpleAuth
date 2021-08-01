<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * LoginController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login']);
    }

    public function login(Request $request)
    {
        try
        {
            $data = $request->all();

            $validator = Validator::make($data, [
                "email" => "required|email|exists:users,email",
                "password" => "required"
            ]);
            if ($validator->fails()) {
                return $this->validationErrors($validator->errors());
            }

            if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
                $user = Auth::user();
                $token = $user->createToken('MyApp')->accessToken;
            } else {
                return response()->json(['error' => 'Unauthorised'], 401);
            }

            $payload = [
                "token" => $token,
                "user" => new UserResource(auth()->user())
            ];
        }
        catch( Exception $exception )
        {
            return $exception->getMessage();
        }

        return response()->json($payload, "200");
    }

    public function logout()
    {
        try
        {
            if(Auth::check()) {
                Auth::user()->token()->revoke();
                return response()->json(["status" => "success", "message" => "Success! You are logged out."], 201);
            }
            return response()->json(["status" => "failed", "message" => "Failed! You are already logged out."], 403);
        }
        catch( Exception $exception )
        {
            return $exception->getMessage();
        }
    }
}
