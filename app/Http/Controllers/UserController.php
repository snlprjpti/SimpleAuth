<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repository\UserRepository;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor.
     */
    public function __construct(User $user, UserRepository $userRepository)
    {
        $this->user = $user;
        $this->userRepository = $userRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return UserResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new UserResource($data);
    }

    public function inviteUser(Request $request)
    {
        try {
            if(auth()->user()->role != 'admin') return response()->json("unauthorized access", 401);
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                "name" => "required",
                "email" => "required|unique:users,email",
            ]);
            if ($validator->fails()) {
                return $this->validationErrors($validator->errors());
            }
            $created = $this->userRepository->create($data, function ($created) use ($request) {
                $this->userRepository->storeInvitation($created);
            });
        } catch (Exception $exception) {
            return $exception;
        }

        return response()->json($this->resource($created), 201);
    }


    public function updateProfile(Request $request)
    {
        try
        {
            $user_id = Auth::user()->id;
            $data = $request->all();
            $validator = Validator::make($data  , [
                "email" => "sometimes|email|unique:users,email,{$user_id}",
                "password" => is_null($request->password) ? "sometimes|nullable" : "required|confirmed",
                "name" => "sometimes",
                "user_name" => "sometimes|unique:users,user_name,{$user_id}",
                "avatar" => "sometimes|mimes:jpg,png,jpeg",
            ]);
            if ($validator->fails()) {
                return $this->validationErrors($validator->errors());
            }
            if ( is_null($request->password) ) {
                unset($data["password"]);
            } else {
                $data["password"] = Hash::make($data["password"]);
            }

            if($request->avatar)
            {
                $image = $this->userRepository->createImage($request->avatar);
                $data = array_merge($data,$image);
            }

            $updated = $this->userRepository->update($data, $user_id);
        }
        catch (Exception $exception)
        {
            return $exception->getMessage();
        }

        return response()->json($this->resource($updated), 201);
    }
}
