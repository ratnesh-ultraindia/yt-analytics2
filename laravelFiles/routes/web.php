<?php

use App\Http\Controllers\GroupMembers;
use App\Http\Controllers\PageLoader;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageLoader::class,'home']);

// Route::get("/group-details/{id}",[PageLoader::class,'group_details']);

// Route::get("/member-details/{id}",[PageLoader::class,'member_details']);

// Route::get("channel-data/{id}",[PageLoader::class,'channel_data']);

Route::get("get-members-for-group/{id}",[GroupMembers::class,'members_for_group']);

Route::get("get-channels-for-member/{id}",[GroupMembers::class,'get_channels_for_member']);