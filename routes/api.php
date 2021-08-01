<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserInvitationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;

Route::group(["middleware" => ["api"], "prefix" => "admin"], function() {

    Route::post("/login", [LoginController::class, "login"])->name("login");

    Route::group(['middleware' => 'auth:api'], function(){
        Route::get("/logout", [LoginController::class, "logout"])->name("logout");

        Route::post("/invite-new-user", [UserController::class, "inviteUser"]);

    });

});


Route::group(["middleware" => ["api"], "prefix" => "user"], function() {

    Route::get("/invitation-info/{invitation_pin}", [UserInvitationController::class, "getInvitationPin"])->name("invitation-info");
    Route::post("/accept-invitation", [UserInvitationController::class, "acceptInvitation"])->name("accept-invitation");

    Route::group(['middleware' => 'auth:api'], function(){

        Route::post("/update-profile", [UserController::class, "updateProfile"]);

    });

});
