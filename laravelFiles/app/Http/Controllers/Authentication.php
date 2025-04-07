<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Authentication extends Controller
{
    
    function user_auth(Request $request) {
        if ($request->secretcode == env("PASSCODE")) {
            session(["authenticated" => TRUE]);
    
            return response()->json([
                "success" => true
            ]);
        } else {
            return response()->json([
                "success" => false
            ]);
        }
    }

}
