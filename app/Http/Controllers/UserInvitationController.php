<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidInvitationToken;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserInvitationController extends Controller
{
    private $user, $userRepository;

    public function __construct(User $user, UserRepository $userRepository)
    {
        $this->user = $user;
        $this->userRepository = $userRepository;
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
            return $exception->getMessage();
        }

        return response()->json( ["invitation_pin" => $user->invitation_pin], 201);
    }

    public function acceptInvitation(Request $request)
    {
        try
        {
            $data = $request->all();
            $validator = Validator::make($data, [
                'invitation_pin' => 'required',
                'user_name' => 'required|unique:users,user_name|min:4|max:20',
                'password' => 'required|confirmed|min:6',
            ]);
            if ($validator->fails()) {
                return $this->validationErrors($validator->errors());
            }
            $user = $this->user->whereInvitationPin($request->invitation_pin)->first();

            if ( !$user ) throw new InvalidInvitationToken(__("Invalid Pin"));
            $data = [
                "user_name" => $data['user_name'],
                "invitation_pin" => null,
                "register_at" => now(),
                "password" => Hash::make($data["password"])
            ];

            $updated = $this->userRepository->update($data, $user->id);
        }
        catch (Exception $exception)
        {
            return $exception->getMessage();
        }

        return response()->json($updated, 201);
    }
}
