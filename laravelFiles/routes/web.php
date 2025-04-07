<?php

use App\Http\Controllers\Authentication;
use App\Http\Controllers\GroupMembers;
use App\Http\Controllers\PageLoader;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageLoader::class,'auth']);

Route::get("/dashboard",[PageLoader::class,'home']);

Route::post("auth-exe",[Authentication::class,'user_auth']);

Route::get("get-members-for-group/{id}",[GroupMembers::class,'members_for_group']);

Route::get("get-channels-for-member/{id}",[GroupMembers::class,'get_channels_for_member']);