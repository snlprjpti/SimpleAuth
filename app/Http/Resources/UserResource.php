<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "user_name" => $this->user_name,
            "email" => $this->email,
            "role" => $this->role,
            "avatar" => $this->avatar_url,
            "register_at" => ($this->register_at) ? $this->register_at->format("M d, Y H:i A") : null,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
