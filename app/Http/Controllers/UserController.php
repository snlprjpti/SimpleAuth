<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * UserController constructor.
     */
    public function __construct(User $user, UserRepository $repository)
    {
        parent::__construct();
        $this->user = $user;
        $this->repository = $repository;
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
            if(auth()->user()->role != 'admin') return $this->errorResponse("Unauthorized Access", 401);

            $data = $this->repository->validateData($request, [
                "name" => "required",
                "email" => "required|unique:users,email",
            ]);

            $created = $this->repository->create($data, function ($created) use ($request) {
                $this->repository->storeInvitation($created);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), "Created Successfully", 201);
    }


    public function updateProfile(Request $request)
    {
        try
        {
            $user_id = Auth::user()->id;

            $data = $this->repository->validateData($request, [
                "email" => "sometimes|email|unique:users,email,{$user_id}",
                "password" => is_null($request->password) ? "sometimes|nullable" : "required|confirmed",
                "name" => "sometimes",
                "user_name" => "sometimes|unique:users,user_name,{$user_id}",
                "avatar" => "sometimes|mimes:jpg,png,jpeg",
            ]);

            if ( is_null($request->password) ) {
                unset($data["password"]);
            } else {
                $data["password"] = Hash::make($data["password"]);
            }

            if($request->avatar)
            {
                $image = $this->repository->createImage($request->avatar);
                $data = array_merge($data,$image);
            }

            $updated = $this->repository->update($data, $user_id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), "Profile Update Successfully", 201);
    }
}
