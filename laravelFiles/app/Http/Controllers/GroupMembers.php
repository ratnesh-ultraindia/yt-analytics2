<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Analytics;
use Illuminate\Container\Attributes\Cache;
use PDO;

class GroupMembers extends Controller
{


    function members_for_group($id){
        
        $groupMembers = GroupMember::where("group_id",$id)->get();


        return json_encode([
            "success"=>TRUE,
            "members"=>$groupMembers,
        ]);

    }

    function get_channels_for_member($id) {
        
        $analyticsController = new Analytics();

        $memberData = GroupMember::find($id);

        $channels = explode(",",$memberData["channels"]);

        $allChannelData = [];

        foreach($channels as $channelId){

            $channelData = $analyticsController->channel($channelId);

            $allChannelData[$channelId] = json_decode($channelData,TRUE);

        }

        return json_encode($allChannelData);

        
    }
    
}
