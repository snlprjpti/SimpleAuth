<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $default_users = [
            [
                "name" => "Admin",
                "email" => "admin@gmail.com",
                "password" => bcrypt("admin"),
                "role" => "admin",
            ],
            [
                "name" => "User",
                "email" => "user@gmail.com",
                "password" => bcrypt("user"),
                "role" => "user",
            ]
        ];

        $data = array_map(function ($user) {
            return [
                "name" => $user["name"] ?? "Admin",
                "email" => $user["email"] ?? "admin@gmail.com",
                "password" => $user["password"] ?? bcrypt("admin123"),
                "api_token" => Str::random(80),
                "role" => $user["role"] ?? "user",
                "created_at" => now(),
                "updated_at" => now(),
                "register_at" => now()
            ];
        }, $default_users);

        DB::table("users")->insert($data);
    }
}
