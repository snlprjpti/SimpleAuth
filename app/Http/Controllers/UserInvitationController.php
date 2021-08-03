<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidInvitationToken;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserInvitationController extends BaseController
{
    private $user, $repository;

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

    public function getInvitationPin(Request $request)
    {
        try
        {
            $pin = $request->invitation_pin;
            if( !$pin ) throw new Exception(__("Pin Not Found"));
            $user = $this->user->whereInvitationPin($request->invitation_pin)->firstOrFail();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return response()->json( ["invitation_pin" => $user->invitation_pin], 201);
    }

    public function acceptInvitation(Request $request)
    {
        try
        {
            $data = $this->repository->validateData($request, [
                'invitation_pin' => 'required',
                'user_name' => 'required|unique:users,user_name|min:4|max:20',
                'password' => 'required|confirmed|min:6',
            ]);

            $user = $this->user->whereInvitationPin($request->invitation_pin)->first();

            if ( !$user ) throw new InvalidInvitationToken(__("Invalid Pin"));
            $data = [
                "user_name" => $data['user_name'],
                "invitation_pin" => null,
                "register_at" => now(),
                "password" => Hash::make($data["password"])
            ];

            $updated = $this->repository->update($data, $user->id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), "Registered Successfully", 201);
    }
}
