<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\YtExecs;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Models\YtChannelsForGroup;
use App\Http\Controllers\Analytics;
use App\Models\GroupMembersChannels;
use Illuminate\Support\Facades\Http;

class PageLoader extends Controller
{

    private function page_loader($viewName,$data){
        echo view("header",$data);
        echo view("pages.".$viewName,$data);
        echo view("footer",$data);
    }

    function home()  {
        
        $groups = Group::all();

        $this->page_loader("dashboard",[
            "title" => "Youtube Groups",
            "groups" => $groups
        ]);

    }

}
